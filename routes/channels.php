<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Personal notification channel — used by Laravel's broadcast notification system
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Group and private chat room channels
Broadcast::channel('room.{id}', function ($user, $id) {
    return [
        'id'   => $user->id,
        'name' => $user->name,
        'role' => $user->role,
    ];
});

// Global presence channel — tracks all online users across the app
Broadcast::channel('online', function ($user) {
    return [
        'id'           => $user->id,
        'name'         => $user->name,
        'email'        => $user->email,
        'role'         => $user->role,
        'new_messages' => 0,
    ];
});
