<?php

use App\Models\Tank;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('trissan.tank.{tankId}', function (User $user, int|string $tankId) {
    $tank = Tank::find($tankId);

    if (! $tank) {
        return false;
    }

    if ($tank->division === 'main_tank') {
        return $user->hasPermission('main_tank.view');
    }

    return $user->hasPermission('mobile_tanks.view');
});

Broadcast::channel('trissan.live.main-tank', function (User $user) {
    return $user->hasPermission('main_tank.view');
});

Broadcast::channel('trissan.live.mobile-tanks', function (User $user) {
    return $user->hasPermission('mobile_tanks.view');
});

Broadcast::channel('trissan.alarms', function (User $user) {
    return $user->hasPermission('alarms.view');
});

Broadcast::channel('trissan.live.alarms', function (User $user) {
    return $user->hasPermission('alarms.view');
});

Broadcast::channel('trissan.live.map', function (User $user) {
    return $user->hasPermission('map.view');
});
