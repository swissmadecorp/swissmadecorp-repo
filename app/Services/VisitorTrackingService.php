<?php

namespace App\Services;

use App\Models\VisitorProfile;
use App\Models\VisitorSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VisitorTrackingService
{
    public function __construct(
        private readonly CrawlerDetectionService $crawlerDetectionService,
    ) {
    }

    public function trackHeartbeat(Request $request, array $payload): ?VisitorSession
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        $now = now();
        $visitorKey = $payload['visitor_key'];
        $sessionToken = $payload['session_token'];
        $visibilityState = $payload['visibility_state'] ?? 'visible';
        $ipAddress = $this->resolveIpAddress($request);
        $userAgent = Str::limit((string) $request->userAgent(), 65535, '');

        if ($this->crawlerDetectionService->detectExcludedBot($ipAddress, $userAgent)) {
            return null;
        }

        $geo = $this->resolveGeoData($ipAddress);

        $profile = $this->resolveVisitorProfile($visitorKey, $ipAddress, $userAgent, $now);

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
                'user_agent' => $userAgent,
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
        $metadata = $this->sessionMetadata($visibilityState, $now, $session, $pageChanged);

        $session->forceFill([
            'ip_address' => $ipAddress ?: $session->ip_address,
            'user_agent' => $userAgent,
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
            'metadata' => $metadata,
        ])->save();

        $profile->forceFill([
            'last_seen_at' => $now,
            'last_known_ip' => $ipAddress ?: $profile->last_known_ip,
            'country' => $geo['country'] ?? $profile->country,
            'city' => $geo['city'] ?? $profile->city,
        ])->save();

        return $session->fresh(['profile']);
    }

    private function resolveVisitorProfile(string $visitorKey, ?string $ipAddress, string $userAgent, Carbon $now): VisitorProfile
    {
        $profile = VisitorProfile::query()->firstWhere('visitor_key', $visitorKey);

        if ($profile) {
            return $profile;
        }

        $profile = VisitorProfile::query()
            ->whereJsonContains('metadata->visitor_key_aliases', $visitorKey)
            ->first();

        if ($profile) {
            return $profile;
        }

        $profile = $this->matchLikelyReturningProfile($ipAddress, $userAgent);

        if ($profile) {
            $metadata = is_array($profile->metadata) ? $profile->metadata : [];
            $aliases = collect($metadata['visitor_key_aliases'] ?? [])
                ->filter(fn ($value) => is_string($value) && $value !== '')
                ->push($visitorKey)
                ->unique()
                ->values()
                ->all();

            $metadata['visitor_key_aliases'] = $aliases;
            $profile->forceFill(['metadata' => $metadata])->save();

            return $profile->fresh();
        }

        return VisitorProfile::query()->create([
            'visitor_key' => $visitorKey,
            'visit_count' => 0,
            'first_seen_at' => $now,
        ]);
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

    public function activeVisitors(string $searchTerm = ''): Collection
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return $this->trackedSessionsQuery($searchTerm)
            ->with('profile')
            ->whereNull('ended_at')
            ->orderByDesc('last_seen_at')
            ->get()
            ->filter(fn (VisitorSession $session) => $this->sessionIsLiveForMonitor($session))
            ->groupBy(fn (VisitorSession $session) => $session->profile?->visitor_key ?: 'session:' . $session->session_token)
            ->map(fn (Collection $sessions) => $this->activeVisitorSummary($sessions))
            ->values()
            ->values()
            ->map(function (array $visitor, int $index) {
                $visitor['visitor_number'] = $index + 1;
                $visitor['visitor_label'] = 'Visitor #' . ($index + 1);

                return $visitor;
            });
    }

    public function history(int $perPage = 12, string $searchTerm = ''): LengthAwarePaginator
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return $this->trackedSessionsQuery($searchTerm)
            ->with('profile')
            ->orderByDesc('started_at')
            ->paginate($perPage);
    }

    public function leftHistory(int $perPage = 12, string $searchTerm = ''): LengthAwarePaginator
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return $this->trackedSessionsQuery($searchTerm)
            ->with('profile')
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->paginate($perPage, ['*'], 'leftPage');
    }

    public function knownCustomersHistory(int $perPage = 12, string $searchTerm = ''): LengthAwarePaginator
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return $this->trackedSessionsQuery($searchTerm)
            ->with('profile')
            ->whereHas('profile', fn ($query) => $query->whereNotNull('display_name'))
            ->orderByDesc('started_at')
            ->paginate($perPage, ['*'], 'knownPage');
    }

    public function returningVisitorsHistory(int $perPage = 12, string $searchTerm = ''): LengthAwarePaginator
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        return $this->trackedSessionsQuery($searchTerm)
            ->with('profile')
            ->whereHas('profile', fn ($query) => $query->where('visit_count', '>', 1))
            ->orderByDesc('started_at')
            ->paginate($perPage, ['*'], 'returningPage');
    }

    public function totalVisitsHistory(int $perPage = 12, string $searchTerm = ''): LengthAwarePaginator
    {
        return $this->history($perPage, $searchTerm);
    }

    public function stats(string $searchTerm = ''): array
    {
        $this->purgeExpiredDataIfDue();
        $this->expireInactiveSessions();

        $activeVisitors = $this->activeVisitors($searchTerm);

        return [
            'active_visitors' => $activeVisitors->count(),
            'known_visitors' => VisitorProfile::query()
                ->whereNotNull('display_name')
                ->whereHas('sessions', fn (Builder $query) => $this->trackedSessionsScope($query, $searchTerm))
                ->count(),
            'returning_visitors' => VisitorProfile::query()
                ->where('visit_count', '>', 1)
                ->whereHas('sessions', fn (Builder $query) => $this->trackedSessionsScope($query, $searchTerm))
                ->count(),
            'total_visits' => $this->trackedSessionsQuery($searchTerm)->count(),
        ];
    }

    public function activePageGroups(string $searchTerm = ''): Collection
    {
        $activeVisitors = $this->activeVisitors($searchTerm);

        return $activeVisitors
            ->flatMap(function (array $visitor) {
                $paths = collect($visitor['active_pages'] ?? [])
                    ->pluck('path')
                    ->push($visitor['current_path'] ?? null)
                    ->filter()
                    ->map(fn ($path) => $this->pageGroupFromPath((string) $path))
                    ->filter(fn ($group) => filled($group['key'] ?? null) && filled($group['label'] ?? null))
                    ->unique('key')
                    ->values();

                return $paths->map(fn (array $group) => [
                    'visitor_key' => $visitor['monitor_key'],
                    'key' => $group['key'],
                    'label' => $group['label'],
                    'visitor_label' => $visitor['visitor_label'] ?? ($visitor['display_name'] ?: 'Visitor'),
                    'visitor_number' => $visitor['visitor_number'] ?? null,
                    'current_path' => $visitor['current_path'],
                ]);
            })
            ->groupBy('key')
            ->map(function (Collection $items, string $key) {
                $label = $items->first()['label'] ?? Str::headline($key);
                $count = $items->pluck('visitor_key')->unique()->count();
                $visitors = $items
                    ->unique('visitor_key')
                    ->map(fn (array $item) => [
                        'visitor_key' => $item['visitor_key'],
                        'label' => $item['visitor_label'],
                        'visitor_number' => $item['visitor_number'],
                        'current_path' => $item['current_path'],
                    ])
                    ->values()
                    ->all();

                return [
                    'key' => $key,
                    'label' => $label,
                    'active_count' => $count,
                    'visitor_label' => Str::plural('visitor', $count),
                    'visitors' => $visitors,
                ];
            })
            ->sortByDesc('active_count')
            ->values();
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
            'monitor_key' => $profile?->visitor_key ?: $session->session_token,
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
            'current_path' => $this->sanitizeDisplayPath($session->current_path),
            'current_url' => $this->sanitizeDisplayUrl($session->current_url),
            'previous_path' => $this->sanitizeDisplayPath(data_get($session->metadata, 'previous_page.path')),
            'previous_url' => $this->sanitizeDisplayUrl(data_get($session->metadata, 'previous_page.url')),
            'previous_title' => data_get($session->metadata, 'previous_page.title'),
            'previous_page_changed_at' => data_get($session->metadata, 'previous_page.left_at'),
            'previous_page_changed_label' => $this->formatMetadataDate(data_get($session->metadata, 'previous_page.left_at')),
            'referrer_url' => $session->referrer_url,
            'referrer_host' => $session->referrer_host,
            'landing_path' => $this->sanitizeDisplayPath($session->landing_path),
            'page_views' => $session->page_views,
            'started_at' => optional($session->started_at)?->toIso8601String(),
            'visit_date_label' => $this->formatDateTimeLabel($session->started_at),
            'last_seen_at' => optional($session->last_seen_at)?->toIso8601String(),
            'ended_at' => optional($session->ended_at)?->toIso8601String(),
            'ended_date_label' => $this->formatDateTimeLabel($session->ended_at),
            'seconds_on_site' => $secondsOnSite,
            'time_on_site' => $this->formatDuration($secondsOnSite),
            'status_label' => $active ? 'Browsing now' : ($session->ended_at ? 'Ended' : 'Timed out'),
            'location_label' => collect([$session->city ?: $profile?->city, $session->country ?: $profile?->country])
                ->filter()
                ->implode(', ') ?: 'Unknown location',
        ];
    }

    private function activeVisitorSummary(Collection $sessions): array
    {
        /** @var \App\Models\VisitorSession $primarySession */
        $primarySession = $sessions
            ->sortByDesc(fn (VisitorSession $session) => optional($session->last_seen_at)->timestamp ?? optional($session->started_at)->timestamp ?? 0)
            ->first();

        $summary = $this->sessionSummary($primarySession, true);
        $activePages = $sessions
            ->sortByDesc(fn (VisitorSession $session) => optional($session->last_seen_at)->timestamp ?? optional($session->started_at)->timestamp ?? 0)
            ->map(function (VisitorSession $session) {
                $url = $this->sanitizeDisplayUrl($session->current_url) ?: null;
                $path = $this->sanitizeDisplayPath($session->current_path)
                    ?: $this->sanitizeDisplayUrl($session->current_url)
                    ?: 'Unknown page';

                return [
                    'session_token' => $session->session_token,
                    'path' => $path ?: 'Unknown page',
                    'url' => $url,
                    'last_seen_at' => optional($session->last_seen_at)?->toIso8601String(),
                ];
            })
            ->unique(fn (array $page) => ($page['url'] ?: '') . '|' . $page['path'])
            ->values();

        $startedAt = $sessions
            ->pluck('started_at')
            ->filter()
            ->sort()
            ->first();

        if ($startedAt) {
            $secondsOnSite = max(0, $startedAt->diffInSeconds(now()));
            $summary['started_at'] = $startedAt->toIso8601String();
            $summary['visit_date_label'] = $this->formatDateTimeLabel($startedAt);
            $summary['seconds_on_site'] = $secondsOnSite;
            $summary['time_on_site'] = $this->formatDuration($secondsOnSite);
        }

        $summary['monitor_key'] = $summary['visitor_key'] ?: 'session:' . $summary['session_token'];
        $summary['active_page_count'] = $activePages->count();
        $summary['active_pages'] = $activePages->all();

        if ($activePages->count() > 1) {
            $summary['status_label'] = 'Browsing ' . $activePages->count() . ' pages';
        }

        return $summary;
    }

    private function pageGroupFromPath(string $path): array
    {
        $normalizedPath = trim(Str::before($path, '?'));

        if ($normalizedPath === '' || $normalizedPath === '/') {
            return [
                'key' => 'home',
                'label' => 'Home',
            ];
        }

        if (Str::startsWith($normalizedPath, '/product-details/')) {
            $label = $this->productLabelFromPath($normalizedPath);

            return [
                'key' => 'product:' . Str::slug($label),
                'label' => $label,
            ];
        }

        return [
            'key' => 'page:' . Str::slug(trim($normalizedPath, '/')),
            'label' => $this->generalPageLabelFromPath($normalizedPath),
        ];
    }

    private function productLabelFromPath(string $path): string
    {
        $slug = trim(Str::after($path, '/product-details/'));
        $tokens = collect(explode('-', $slug))
            ->filter(fn ($token) => $token !== '')
            ->values();
        $nameTokens = [];
        $stopWords = [
            'black', 'white', 'blue', 'green', 'silver', 'gold', 'grey', 'gray', 'brown', 'pink',
            'red', 'yellow', 'orange', 'purple', 'beige', 'ivory', 'champagne', 'steel', 'rose',
            'titanium', 'platinum', 'ceramic', 'leather', 'rubber',
        ];

        foreach ($tokens as $token) {
            $normalizedToken = Str::lower($token);

            if (preg_match('/\d/', $normalizedToken)) {
                break;
            }

            if (! empty($nameTokens) && in_array($normalizedToken, $stopWords, true)) {
                break;
            }

            $nameTokens[] = $token;
        }

        if (empty($nameTokens)) {
            $nameTokens = $tokens->take(4)->all();
        }

        return Str::headline(implode(' ', $nameTokens));
    }

    private function generalPageLabelFromPath(string $path): string
    {
        $trimmed = trim($path, '/');

        if ($trimmed === '') {
            return 'Home';
        }

        $segments = explode('/', $trimmed);
        $lastSegment = end($segments) ?: $trimmed;
        $aliases = [
            'aboutus' => 'About Us',
            'contactus' => 'Contact Us',
            'faq' => 'FAQ',
        ];
        $normalizedSegment = Str::lower($lastSegment);

        if (isset($aliases[$normalizedSegment])) {
            return $aliases[$normalizedSegment];
        }

        return Str::headline(str_replace(['-', '_'], ' ', $lastSegment));
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
            ->get()
            ->each(function (VisitorSession $session) {
                $session->forceFill([
                    'ended_at' => now(),
                ])->save();
            })
            ->count();
    }

    private function purgeExpiredDataIfDue(): void
    {
        Cache::add(
            'visitor-monitor:last-purge-at',
            now()->toIso8601String(),
            now()->addHour(),
        ) && $this->purgeExpiredData();
    }

    private function trackedSessionsQuery(string $searchTerm = ''): Builder
    {
        return $this->trackedSessionsScope(VisitorSession::query(), $searchTerm);
    }

    private function trackedSessionsScope(Builder $query, string $searchTerm = ''): Builder
    {
        $this->excludeKnownCrawlerUserAgents($query);

        return $this->applySearchTermToQuery($query, $searchTerm);
    }

    private function excludeKnownCrawlerUserAgents(Builder $query): Builder
    {
        return $query->where(function (Builder $userAgentQuery) {
            $userAgentQuery
                ->whereNull('user_agent')
                ->orWhere('user_agent', 'not like', '%Googlebot%');
        });
    }

    private function applySearchTermToQuery(Builder $query, string $searchTerm = ''): Builder
    {
        if (trim($searchTerm) === '') {
            return $query;
        }

        return $query->whereRaw('(' . $searchTerm . ')');
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

    private function matchLikelyReturningProfile(?string $ipAddress, string $userAgent): ?VisitorProfile
    {
        if (! $ipAddress || trim($userAgent) === '') {
            return null;
        }

        $candidates = VisitorProfile::query()
            ->where('last_known_ip', $ipAddress)
            ->where(function ($query) {
                $query->where('last_seen_at', '>=', now()->subDays($this->retentionDays()))
                    ->orWhere('first_seen_at', '>=', now()->subDays($this->retentionDays()));
            })
            ->whereHas('sessions', function ($query) use ($userAgent) {
                $query->where('user_agent', $userAgent);
            })
            ->orderByDesc('last_seen_at')
            ->limit(2)
            ->get();

        return $candidates->count() === 1 ? $candidates->first() : null;
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

    private function sessionIsLiveForMonitor(VisitorSession $session, ?Carbon $referenceTime = null): bool
    {
        if (! $this->sessionIsOnline($session, $referenceTime)) {
            return false;
        }

        return data_get($session->metadata, 'visibility_state', 'visible') !== 'hidden';
    }

    private function sessionActivityWindowSeconds(VisitorSession $session): int
    {
        $visibilityState = data_get($session->metadata, 'visibility_state', 'visible');

        return $visibilityState === 'hidden'
            ? $this->backgroundWindowSeconds()
            : $this->onlineWindowSeconds();
    }

    private function sessionMetadata(string $visibilityState, Carbon $now, ?VisitorSession $session = null, bool $pageChanged = false): array
    {
        $metadata = is_array($session?->metadata) ? $session->metadata : [];
        $previousVisibilityState = data_get($metadata, 'visibility_state', 'visible');

        if (
            $pageChanged
            && $session
            && ($session->current_path || $session->current_url || $session->current_title)
        ) {
            $metadata['previous_page'] = [
                'path' => $session->current_path,
                'url' => $session->current_url,
                'title' => $session->current_title,
                'left_at' => $now->toIso8601String(),
            ];
        }

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

    private function formatMetadataDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return $this->formatDateTimeLabel(Carbon::parse($value));
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatDateTimeLabel(Carbon|string|null $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            $dateTime = $value instanceof Carbon ? $value->copy() : Carbon::parse($value);

            return $dateTime
                ->timezone($this->displayTimezone())
                ->format('M j, Y g:i A');
        } catch (\Throwable) {
            return null;
        }
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

    private function sanitizeDisplayPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return $this->stripIgnoredQueryParameters($path);
    }

    private function sanitizeDisplayUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return $this->stripIgnoredQueryParameters($url);
    }

    private function stripIgnoredQueryParameters(string $value): string
    {
        $query = parse_url($value, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return $value;
        }

        $ignored = $this->ignoredQueryParameters();
        $filteredPairs = collect(explode('&', $query))
            ->filter(function (string $pair) use ($ignored) {
                $name = explode('=', $pair, 2)[0] ?? '';
                $normalized = Str::lower(rawurldecode($name));

                return $normalized !== '' && ! in_array($normalized, $ignored, true);
            })
            ->values()
            ->all();

        $path = parse_url($value, PHP_URL_PATH) ?? '';
        $fragment = parse_url($value, PHP_URL_FRAGMENT);
        $rebuiltQuery = implode('&', $filteredPairs);

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $this->rebuildAbsoluteUrl($value, $path, $rebuiltQuery, $fragment);
        }

        return $path
            . ($rebuiltQuery !== '' ? '?' . $rebuiltQuery : '')
            . ($fragment ? '#' . $fragment : '');
    }

    private function rebuildAbsoluteUrl(string $originalUrl, string $path, string $query, string|false|null $fragment): string
    {
        $parts = parse_url($originalUrl);

        if (! is_array($parts)) {
            return $originalUrl;
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $auth = $user !== '' ? $user . $pass . '@' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return $scheme
            . $auth
            . $host
            . $port
            . $path
            . ($query !== '' ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');
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
        return (int) config('visitor-monitor.online_window_seconds', 120);
    }

    private function backgroundWindowSeconds(): int
    {
        return max($this->onlineWindowSeconds(), (int) config('visitor-monitor.background_window_seconds', 900));
    }

    private function internalNavigationGraceSeconds(): int
    {
        return max(3, (int) config('visitor-monitor.heartbeat_interval_seconds', 5) * 2);
    }

    private function retentionDays(): int
    {
        return max(1, (int) config('visitor-monitor.retention_days', 7));
    }

    private function displayTimezone(): string
    {
        return (string) config('visitor-monitor.timezone', 'America/New_York');
    }

    private function ignoredQueryParameters(): array
    {
        $defaults = ['gad_source', 'gad_campaignid', 'gclid', 'wbraid', 'gbraid'];

        return collect(config('visitor-monitor.ignored_query_parameters', $defaults))
            ->merge($defaults)
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->map(fn (string $value) => Str::lower($value))
            ->unique()
            ->values()
            ->all();
    }

    private function retentionCutoff()
    {
        return now()->subDays($this->retentionDays());
    }
}
