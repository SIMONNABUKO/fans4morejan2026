<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that yourz
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// For public channels, we don't need to define authorization callbacks
// The following code is only needed for private channels

// Public channel for post events (created, updated, deleted)
// No authorization callback needed as it's a public channel
// Channel name: 'posts'

// Private channel for user-specific messages (if you still need private channels elsewhere)
Broadcast::channel('private-user.{id}', function ($user, $id) {
    $authorized = (int) $user->id === (int) $id;
    
    Log::info('🔐 Channel authorization request', [
        'channel' => 'private-user.' . $id,
        'user_id' => $user->id,
        'requested_id' => $id,
        'authorized' => $authorized,
        'request_time' => now()->toDateTimeString()
    ]);
    
    return $authorized;
});

// Presence channel for showing online users
Broadcast::channel('online', function ($user) {
    Log::info('👥 User joined online presence channel', [
        'user_id' => $user->id,
        'name' => $user->name
    ]);
    
    return ['id' => $user->id, 'name' => $user->name];
});