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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('cli-session.{sessionId}', function ($user, $sessionId) {
    $session = \App\Models\CliSession::find($sessionId);

    return $session && ((int) $session->user_id === (int) $user->id || $user->can('audit.view'));
});
