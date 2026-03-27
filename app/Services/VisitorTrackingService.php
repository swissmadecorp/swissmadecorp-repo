<?php

namespace App\Services;

use App\Models\VisitorProfile;
use App\Models\VisitorSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VisitorTrackingService
{
    public function trackHeartbeat(Request $request, array $payload): VisitorSession
    {
        $this->expireInactiveSessions();

        $now = now();
        $visitorKey = $payload['visitor_key'];
        $sessionToken = $payload['session_token'];
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
        $isNewVisit = $this->sessionRequiresNewVisit($session, $profile);
        $activeSessionToken = $isNewVisit && $session ? (string) Str::uuid() : $sessionToken;

        if ($isNewVisit) {
            $profile->forceFill([
                'visit_count' => max(1, $profile->visit_count + 1),
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
        $this->expireInactiveSessions();

        return VisitorSession::query()
            ->with('profile')
            ->whereNull('ended_at')
            ->where('last_seen_at', '>=', now()->subSeconds($this->onlineWindowSeconds()))
            ->orderByDesc('last_seen_at')
            ->get()
            ->map(fn (VisitorSession $session) => $this->sessionSummary($session, true))
            ->values();
    }

    public function history(int $perPage = 12): LengthAwarePaginator
    {
        $this->expireInactiveSessions();

        return VisitorSession::query()
            ->with('profile')
            ->orderByDesc('started_at')
            ->paginate($perPage);
    }

    public function leftHistory(int $perPage = 12): LengthAwarePaginator
    {
        $this->expireInactiveSessions();

        return VisitorSession::query()
            ->with('profile')
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->paginate($perPage, ['*'], 'leftPage');
    }

    public function stats(): array
    {
        $this->expireInactiveSessions();

        return [
            'active_visitors' => VisitorSession::query()
                ->whereNull('ended_at')
                ->where('last_seen_at', '>=', now()->subSeconds($this->onlineWindowSeconds()))
                ->count(),
            'known_visitors' => VisitorProfile::query()->whereNotNull('display_name')->count(),
            'returning_visitors' => VisitorProfile::query()->where('visit_count', '>', 1)->count(),
            'total_visits' => VisitorSession::query()->count(),
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
        return VisitorSession::query()
            ->whereNull('ended_at')
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', now()->subSeconds($this->onlineWindowSeconds()))
            ->update(['ended_at' => now()]);
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

        return optional($session->last_seen_at)?->lt(now()->subSeconds($this->onlineWindowSeconds())) ?? true;
    }

    private function extractHost(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? Str::limit($host, 255, '') : null;
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
}
