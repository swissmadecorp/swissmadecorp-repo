<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ChatPresenceService
{
    public function heartbeat(User $user): bool
    {
        if (! $user->is_chat_ready) {
            return false;
        }

        Cache::put($this->cacheKey($user->id), now()->toIso8601String(), now()->addSeconds($this->ttlSeconds()));

        return true;
    }

    public function isOnline(int $userId): bool
    {
        return Cache::has($this->cacheKey($userId));
    }

    public function availableUsers(): Collection
    {
        return User::query()
            ->where('is_chat_ready', 1)
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => $this->isOnline($user->id))
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
}
