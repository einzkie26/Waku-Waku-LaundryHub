<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;

class UserNotificationController extends NotificationInboxController
{
    protected function notifications(Request $request): MorphMany
    {
        $user = $request->user('web');
        $role = $request->routeIs('admin.notifications.*') ? 'admin' : 'customer';
        abort_unless($user && $user->role === $role, 403);

        return $user->laundryNotifications();
    }
}
