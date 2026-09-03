<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{code}', function (Authenticatable $user, string $code): bool {
    return $user->getAuthIdentifier() === 'room-'.$code;
}, ['guards' => ['room']]);
