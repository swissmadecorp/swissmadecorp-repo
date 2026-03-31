<?php

namespace App\Services;

use App\Models\VisitorProfile;
use App\Models\VisitorSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VisitorTrackingService
{
    public function trackHeartbeat(Request $request, array $payload): VisitorSession
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        $now = now();
        $visitorKey = $payload['visitor_key'];
        $sessionToken = $payload['session_token'];
        $visibilityState = $payload['visibility_state'] ?? 'visible';
        $ipAddress = $this->resolveIpAddress($request);
        $geo = $this->resolveGeoData($ipAddress);

        $profile = VisitorProfile::query()->firstOrCreate(
            ['visitor_key' => $visitorKey],
            [
                'visit_count' => 0,
                'first_seen_at' => $now,
            ],
        );

        $session = VisitorSession::query()->firstWhere('session_token', $sessionToken);
        $this->resumeEndedSessionForInternalNavigation($session, $profile, $payload);
        $isNewVisit = $this->sessionRequiresNewVisit($session, $profile);
        $activeSessionToken = $isNewVisit && $session ? (string) Str::uuid() : $sessionToken;

        if ($isNewVisit) {
            $profile->forceFill([
                'visit_count' => $this->nextVisitCount($profile),
                'first_seen_at' => $profile->first_seen_at ?: $now,
                'last_seen_at' => $now,
                'last_known_ip' => $ipAddress,
                'country' => $geo['country'] ?? $profile->country,
                'city' => $geo['city'] ?? $profile->city,
            ])->save();

            $session = VisitorSession::query()->create([
                'visitor_profile_id' => $profile->id,
                'session_token' => $activeSessionToken,
                'ip_address' => $ipAddress,
                'user_agent' => Str::limit((string) $request->userAgent(), 65535, ''),
                'country' => $geo['country'] ?? $profile->country,
                'city' => $geo['city'] ?? $profile->city,
                'landing_url' => $payload['page_url'] ?? null,
                'landing_path' => $payload['page_path'] ?? null,
                'landing_title' => $payload['page_title'] ?? null,
                'current_url' => $payload['page_url'] ?? null,
                'current_path' => $payload['page_path'] ?? null,
                'current_title' => $payload['page_title'] ?? null,
                'referrer_url' => $payload['referrer_url'] ?? null,
                'referrer_host' => $this->extractHost($payload['referrer_url'] ?? null),
                'page_views' => 1,
                'started_at' => $now,
                'last_seen_at' => $now,
                'ended_at' => null,
                'metadata' => $this->sessionMetadata($visibilityState, $now),
            ]);

            return $session->fresh(['profile']);
        }

        $pageChanged = $this->pageChanged(
            $session->current_path,
            $payload['page_path'] ?? null,
            $session->current_url,
            $payload['page_url'] ?? null,
        );

        $session->forceFill([
            'ip_address' => $ipAddress ?: $session->ip_address,
            'user_agent' => Str::limit((string) $request->userAgent(), 65535, ''),
            'country' => $geo['country'] ?? $session->country ?? $profile->country,
            'city' => $geo['city'] ?? $session->city ?? $profile->city,
            'current_url' => $payload['page_url'] ?? $session->current_url,
            'current_path' => $payload['page_path'] ?? $session->current_path,
            'current_title' => $payload['page_title'] ?? $session->current_title,
            'referrer_url' => $session->referrer_url ?: ($payload['referrer_url'] ?? null),
            'referrer_host' => $session->referrer_host ?: $this->extractHost($payload['referrer_url'] ?? null),
            'last_seen_at' => $now,
            'ended_at' => null,
            'page_views' => $pageChanged ? $session->page_views + 1 : max(1, $session->page_views),
            'metadata' => $this->sessionMetadata($visibilityState, $now, $session),
        ])->save();

        $profile->forceFill([
            'last_seen_at' => $now,
            'last_known_ip' => $ipAddress ?: $profile->last_known_ip,
            'country' => $geo['country'] ?? $profile->country,
            'city' => $geo['city'] ?? $profile->city,
        ])->save();

        return $session->fresh(['profile']);
    }

    public function markVisitorLeft(array $payload): ?VisitorSession
    {
        $this->purgeExpiredDataIfDue();

        $session = VisitorSession::query()
            ->with('profile')
            ->firstWhere('session_token', $payload['session_token'] ?? null);

        if (! $session) {
            return null;
        }

        $now = now();

        $session->forceFill([
            'current_url' => $payload['page_url'] ?? $session->current_url,
            'current_path' => $payload['page_path'] ?? $session->current_path,
            'current_title' => $payload['page_title'] ?? $session->current_title,
            'last_seen_at' => $now,
            'ended_at' => $now,
        ])->save();

        if ($session->profile) {
            $session->profile->forceFill([
                'last_seen_at' => $now,
            ])->save();
        }

        return $session->fresh(['profile']);
    }

    public function rememberChatIdentity(?string $visitorKey, ?string $name = null, ?string $email = null): ?VisitorProfile
    {
        $this->purgeExpiredDataIfDue();

        if (! $visitorKey) {
            return null;
        }

        $profile = VisitorProfile::query()->firstOrCreate(
            ['visitor_key' => $visitorKey],
            ['first_seen_at' => now()],
        );

        $displayName = $this->cleanValue($name, 120);
        $visitorEmail = $this->cleanValue($email, 255);

        $profile->forceFill([
            'display_name' => $displayName ?: $profile->display_name,
            'email' => $visitorEmail ?: $profile->email,
            'last_identified_at' => ($displayName || $visitorEmail) ? now() : $profile->last_identified_at,
        ])->save();

        return $profile;
    }

    public function activeVisitors(): Collection
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return VisitorSession::query()
            ->with('profile')
            ->whereNull('ended_at')
            ->orderByDesc('last_seen_at')
            ->get()
            ->filter(fn (VisitorSession $session) => $this->sessionIsOnline($session))
            ->map(fn (VisitorSession $session) => $this->sessionSummary($session, true))
            ->values();
    }

    public function history(int $perPage = 12): LengthAwarePaginator
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return VisitorSession::query()
            ->with('profile')
            ->orderByDesc('started_at')
            ->paginate($perPage);
    }

    public function leftHistory(int $perPage = 12): LengthAwarePaginator
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return VisitorSession::query()
            ->with('profile')
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->paginate($perPage, ['*'], 'leftPage');
    }

    public function stats(): array
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        $activeVisitors = $this->activeVisitors();

        return [
            'active_visitors' => $activeVisitors->count(),
            'known_visitors' => VisitorProfile::query()->whereNotNull('display_name')->count(),
            'returning_visitors' => VisitorProfile::query()->where('visit_count', '>', 1)->count(),
            'total_visits' => VisitorSession::query()->count(),
        ];
    }

    public function purgeExpiredData(): array
    {
        $cutoff = $this->retentionCutoff();

        $sessionsDeleted = VisitorSession::query()
            ->where(function ($query) use ($cutoff) {
                $query->where(function ($endedSessions) use ($cutoff) {
                    $endedSessions
                        ->whereNotNull('ended_at')
                        ->where('ended_at', '<', $cutoff);
                })->orWhere(function ($abandonedSessions) use ($cutoff) {
                    $abandonedSessions
                        ->whereNull('ended_at')
                        ->where(function ($recentActivity) use ($cutoff) {
                            $recentActivity
                                ->where('last_seen_at', '<', $cutoff)
                                ->orWhere(function ($missingLastSeen) use ($cutoff) {
                                    $missingLastSeen
                                        ->whereNull('last_seen_at')
                                        ->where(function ($startedFallback) use ($cutoff) {
                                            $startedFallback
                                                ->where('started_at', '<', $cutoff)
                                                ->orWhere(function ($createdFallback) use ($cutoff) {
                                                    $createdFallback
                                                        ->whereNull('started_at')
                                                        ->where('created_at', '<', $cutoff);
                                                });
                                        });
                                });
                        });
                });
            })
            ->delete();

        $profilesDeleted = VisitorProfile::query()
            ->doesntHave('sessions')
            ->where(function ($query) use ($cutoff) {
                $query->where(function ($identifiedProfiles) use ($cutoff) {
                    $identifiedProfiles
                        ->whereNotNull('last_identified_at')
                        ->where('last_identified_at', '<', $cutoff);
                })->orWhere(function ($anonymousProfiles) use ($cutoff) {
                    $anonymousProfiles
                        ->whereNull('last_identified_at')
                        ->where(function ($recentlySeenProfiles) use ($cutoff) {
                            $recentlySeenProfiles
                                ->where('last_seen_at', '<', $cutoff)
                                ->orWhere(function ($neverSeenProfiles) use ($cutoff) {
                                    $neverSeenProfiles
                                        ->whereNull('last_seen_at')
                                        ->where(function ($firstSeenFallback) use ($cutoff) {
                                            $firstSeenFallback
                                                ->where('first_seen_at', '<', $cutoff)
                                                ->orWhere(function ($createdFallback) use ($cutoff) {
                                                    $createdFallback
                                                        ->whereNull('first_seen_at')
                                                        ->where('created_at', '<', $cutoff);
                                                });
                                        });
                                });
                        });
                });
            })
            ->delete();

        return [
            'sessions_deleted' => $sessionsDeleted,
            'profiles_deleted' => $profilesDeleted,
            'retention_days' => $this->retentionDays(),
        ];
    }

    public function sessionSummary(VisitorSession $session, bool $active = false): array
    {
        $session->loadMissing('profile');
        $profile = $session->profile;
        $endedAt = $session->ended_at ?: ($active ? now() : $session->last_seen_at);
        $secondsOnSite = max(0, optional($session->started_at)->diffInSeconds($endedAt ?? now()) ?? 0);
        $displayName = $profile?->display_name ?: null;

        return [
            'id' => $session->id,
            'session_token' => $session->session_token,
            'visitor_key' => $profile?->visitor_key,
            'display_name' => $displayName,
            'label' => $displayName ?: 'Visitor',
            'initials' => $this->initials($displayName),
            'is_returning' => (int) ($profile?->visit_count ?? 0) > 1,
            'is_known_customer' => filled($displayName),
            'visit_count' => (int) ($profile?->visit_count ?? 1),
            'country' => $session->country ?: $profile?->country,
            'city' => $session->city ?: $profile?->city,
            'ip_address' => $session->ip_address,
            'current_path' => $session->current_path,
            'current_url' => $session->current_url,
            'referrer_url' => $session->referrer_url,
            'referrer_host' => $session->referrer_host,
            'landing_path' => $session->landing_path,
            'page_views' => $session->page_views,
            'started_at' => optional($session->started_at)?->toIso8601String(),
            'visit_date_label' => optional($session->started_at)?->format('M j, Y g:i A'),
            'last_seen_at' => optional($session->last_seen_at)?->toIso8601String(),
            'ended_at' => optional($session->ended_at)?->toIso8601String(),
            'ended_date_label' => optional($session->ended_at)?->format('M j, Y g:i A'),
            'seconds_on_site' => $secondsOnSite,
            'time_on_site' => $this->formatDuration($secondsOnSite),
            'status_label' => $active ? 'Browsing now' : ($session->ended_at ? 'Ended' : 'Timed out'),
            'location_label' => collect([$session->city ?: $profile?->city, $session->country ?: $profile?->country])
                ->filter()
                ->implode(', ') ?: 'Unknown location',
        ];
    }

    public function expireInactiveSessions(): int
    {
        $expiredSessionIds = VisitorSession::query()
            ->whereNull('ended_at')
            ->get()
            ->filter(fn (VisitorSession $session) => ! $this->sessionIsOnline($session))
            ->pluck('id');

        if ($expiredSessionIds->isEmpty()) {
            return 0;
        }

        return VisitorSession::query()
            ->whereIn('id', $expiredSessionIds)
            ->update(['ended_at' => now()]);
    }

    private function purgeExpiredDataIfDue(): void
    {
        Cache::add(
            'visitor-monitor:last-purge-at',
            now()->toIso8601String(),
            now()->addHour(),
        ) && $this->purgeExpiredData();
    }

    private function resolveIpAddress(Request $request): ?string
    {
        $candidates = [
            $request->header('CF-Connecting-IP'),
            $request->header('X-Forwarded-For'),
            $request->ip(),
        ];

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }

            $value = trim(explode(',', (string) $candidate)[0]);

            if ($value !== '') {
                return Str::limit($value, 45, '');
            }
        }

        return null;
    }

    private function resolveGeoData(?string $ipAddress): array
    {
        if (
            ! config('visitor-monitor.geo.enabled')
            || ! $ipAddress
            || ! filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
        ) {
            return [];
        }

        return Cache::remember(
            'visitor-monitor:geo:' . $ipAddress,
            now()->addSeconds((int) config('visitor-monitor.geo.cache_seconds', 86400)),
            function () use ($ipAddress) {
                $endpoint = str_replace('{ip}', $ipAddress, (string) config('visitor-monitor.geo.endpoint'));

                try {
                    $response = Http::timeout((int) config('visitor-monitor.geo.timeout_seconds', 3))
                        ->acceptJson()
                        ->get($endpoint);

                    if (! $response->successful()) {
                        return [];
                    }

                    $data = $response->json();

                    if (is_array($data) && array_key_exists('success', $data) && $data['success'] === false) {
                        return [];
                    }

                    return [
                        'country' => $this->cleanValue($data['country'] ?? null, 120),
                        'city' => $this->cleanValue($data['city'] ?? null, 120),
                    ];
                } catch (\Throwable $exception) {
                    Log::warning('Visitor monitor geolocation lookup failed.', [
                        'ip' => $ipAddress,
                        'message' => $exception->getMessage(),
                    ]);

                    return [];
                }
            }
        );
    }

    private function pageChanged(?string $currentPath, ?string $incomingPath, ?string $currentUrl, ?string $incomingUrl): bool
    {
        return ($incomingPath && $incomingPath !== $currentPath)
            || ($incomingUrl && $incomingUrl !== $currentUrl);
    }

    private function sessionRequiresNewVisit(?VisitorSession $session, VisitorProfile $profile): bool
    {
        if (! $session) {
            return true;
        }

        if ($session->visitor_profile_id !== $profile->id) {
            return true;
        }

        if ($session->ended_at) {
            return true;
        }

        return ! $this->sessionIsOnline($session);
    }

    private function resumeEndedSessionForInternalNavigation(?VisitorSession $session, VisitorProfile $profile, array $payload): void
    {
        if (! $this->canResumeEndedSessionForInternalNavigation($session, $profile, $payload)) {
            return;
        }

        $session->forceFill([
            'ended_at' => null,
        ])->save();
    }

    private function canResumeEndedSessionForInternalNavigation(?VisitorSession $session, VisitorProfile $profile, array $payload): bool
    {
        if (! $session || ! $session->ended_at || $session->visitor_profile_id !== $profile->id) {
            return false;
        }

        if ($session->ended_at->lt(now()->subSeconds($this->internalNavigationGraceSeconds()))) {
            return false;
        }

        $referrerUrl = $payload['referrer_url'] ?? null;
        $pageUrl = $payload['page_url'] ?? null;

        if (! $this->urlsShareHost($referrerUrl, $pageUrl)) {
            return false;
        }

        return $this->urlsMatch($referrerUrl, $session->current_url)
            || $this->pathsMatch($this->extractPath($referrerUrl), $session->current_path);
    }

    private function nextVisitCount(VisitorProfile $profile): int
    {
        return max(
            1,
            (int) $profile->visit_count + 1,
            (int) $profile->sessions()->count() + 1,
        );
    }

    private function sessionIsOnline(VisitorSession $session, ?Carbon $referenceTime = null): bool
    {
        if ($session->ended_at) {
            return false;
        }

        $lastSeenAt = $session->last_seen_at ?: $session->started_at ?: $session->created_at;

        if (! $lastSeenAt) {
            return false;
        }

        $referenceTime ??= now();

        return $lastSeenAt->gte(
            $referenceTime->copy()->subSeconds($this->sessionActivityWindowSeconds($session))
        );
    }

    private function sessionActivityWindowSeconds(VisitorSession $session): int
    {
        $visibilityState = data_get($session->metadata, 'visibility_state', 'visible');

        return $visibilityState === 'hidden'
            ? $this->backgroundWindowSeconds()
            : $this->onlineWindowSeconds();
    }

    private function sessionMetadata(string $visibilityState, Carbon $now, ?VisitorSession $session = null): array
    {
        $metadata = is_array($session?->metadata) ? $session->metadata : [];
        $previousVisibilityState = data_get($metadata, 'visibility_state', 'visible');

        $metadata['visibility_state'] = $visibilityState;

        if ($visibilityState === 'hidden') {
            $metadata['hidden_at'] = ($previousVisibilityState === 'hidden'
                ? data_get($metadata, 'hidden_at')
                : $now->toIso8601String()) ?: $now->toIso8601String();
        } else {
            $metadata['visible_at'] = $now->toIso8601String();
            unset($metadata['hidden_at']);
        }

        return $metadata;
    }

    private function extractHost(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? Str::limit($host, 255, '') : null;
    }

    private function extractPath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($path) || $path === '') {
            return null;
        }

        return $query ? $path . '?' . $query : $path;
    }

    private function urlsShareHost(?string $firstUrl, ?string $secondUrl): bool
    {
        $firstHost = $this->extractHost($firstUrl);
        $secondHost = $this->extractHost($secondUrl);

        return $firstHost !== null && $firstHost === $secondHost;
    }

    private function urlsMatch(?string $firstUrl, ?string $secondUrl): bool
    {
        return filled($firstUrl) && filled($secondUrl) && $firstUrl === $secondUrl;
    }

    private function pathsMatch(?string $firstPath, ?string $secondPath): bool
    {
        return filled($firstPath) && filled($secondPath) && $firstPath === $secondPath;
    }

    private function cleanValue(mixed $value, int $maxLength): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $cleaned = trim((string) $value);

        return $cleaned === '' ? null : Str::limit($cleaned, $maxLength, '');
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;
        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($minutes > 0 || $hours > 0) {
            $parts[] = $minutes . 'm';
        }

        $parts[] = $remainingSeconds . 's';

        return implode(' ', $parts);
    }

    private function initials(?string $displayName): string
    {
        if (! $displayName) {
            return 'V';
        }

        $parts = preg_split('/\s+/', trim($displayName)) ?: [];
        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'V';
    }

    private function onlineWindowSeconds(): int
    {
        return (int) config('visitor-monitor.online_window_seconds', 35);
    }

    private function backgroundWindowSeconds(): int
    {
        return max($this->onlineWindowSeconds(), (int) config('visitor-monitor.background_window_seconds', 900));
    }

    private function internalNavigationGraceSeconds(): int
    {
        return max(3, (int) config('visitor-monitor.heartbeat_interval_seconds', 15) * 2);
    }

    private function retentionDays(): int
    {
        return max(1, (int) config('visitor-monitor.retention_days', 7));
    }

    private function retentionCutoff()
    {
        return now()->subDays($this->retentionDays());
    }
}
