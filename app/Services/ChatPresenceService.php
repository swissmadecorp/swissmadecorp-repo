<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ChatPresenceService
{
    public function heartbeat(User $user): bool
    {
        if (! $this->isAvailable($user)) {
            Cache::forget($this->cacheKey($user->id));
            return false;
        }

        Cache::put($this->cacheKey($user->id), now()->toIso8601String(), now()->addSeconds($this->ttlSeconds()));

        return true;
    }

    public function isAvailable(User $user): bool
    {
        return (bool) $user->is_chat_ready && ! $this->isPaused($user->id);
    }

    public function setAvailability(User $user, bool $available): bool
    {
        if (! $user->is_chat_ready) {
            return false;
        }

        if ($available) {
            Cache::forget($this->pausedCacheKey($user->id));
            $this->heartbeat($user);

            return true;
        }

        Cache::forever($this->pausedCacheKey($user->id), true);
        Cache::forget($this->cacheKey($user->id));

        return false;
    }

    public function isOnline(int $userId): bool
    {
        return Cache::has($this->cacheKey($userId));
    }

    public function isPaused(int $userId): bool
    {
        return (bool) Cache::get($this->pausedCacheKey($userId), false);
    }

    public function availableUsers(): Collection
    {
        return User::query()
            ->where('is_chat_ready', 1)
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => ! $this->isPaused($user->id) && $this->isOnline($user->id))
            ->values();
    }

    public function availableCount(): int
    {
        return $this->availableUsers()->count();
    }

    public function ttlSeconds(): int
    {
        return (int) config('chat.presence_ttl_seconds', 90);
    }

    private function cacheKey(int $userId): string
    {
        return "chat:presence:user:{$userId}";
    }

    private function pausedCacheKey(int $userId): string
    {
        return "chat:presence:user:{$userId}:paused";
    }
}
