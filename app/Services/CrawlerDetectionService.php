<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CrawlerDetectionService
{
    public function detectExcludedBot(?string $ipAddress, string $userAgent): ?string
    {
        if (! config('visitor-monitor.bots.exclude_googlebot', true)) {
            return null;
        }

        return $this->isVerifiedGooglebot($ipAddress, $userAgent) ? 'Googlebot' : null;
    }

    public function isVerifiedGooglebot(?string $ipAddress, string $userAgent): bool
    {
        if (! $ipAddress || ! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return false;
        }

        if (! Str::contains(Str::lower($userAgent), 'googlebot')) {
            return false;
        }

        $cacheKey = 'visitor-monitor:verified-googlebot:' . sha1($ipAddress . '|' . Str::lower($userAgent));

        return Cache::remember(
            $cacheKey,
            now()->addSeconds((int) config('visitor-monitor.bots.verification_cache_seconds', 86400)),
            fn () => $this->verifyGooglebotDns($ipAddress)
        );
    }

    private function verifyGooglebotDns(string $ipAddress): bool
    {
        $reverseHost = gethostbyaddr($ipAddress);

        if (! is_string($reverseHost) || $reverseHost === '' || $reverseHost === $ipAddress) {
            return false;
        }

        $normalizedHost = Str::lower(rtrim($reverseHost, '.'));
        $allowedSuffixes = collect(config('visitor-monitor.bots.googlebot_host_suffixes', [
            '.googlebot.com',
            '.google.com',
            '.googleusercontent.com',
        ]));

        $matchesAllowedHost = $allowedSuffixes->contains(
            fn (string $suffix) => Str::endsWith($normalizedHost, Str::lower($suffix))
        );

        if (! $matchesAllowedHost) {
            return false;
        }

        $forwardIps = gethostbynamel($normalizedHost);

        return is_array($forwardIps) && in_array($ipAddress, $forwardIps, true);
    }
}
