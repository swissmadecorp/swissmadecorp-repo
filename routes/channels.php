<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public broadcast channel
Broadcast::channel('products', function ($event) {
    return true;
});


Broadcast::channel('message.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('private-message.{userId}', function (User $user, $userId) {
    if ((int) $user->id === (int) $userId) {
        \Log::info("Channel authorized: private-message.{$userId}");
        return true;
    }
    \Log::info("Channel authorization failed for user: {$user->id} on private-message.{$userId}");
    return false;
});