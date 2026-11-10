<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\NotificationInboxController;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;

class StaffNotificationController extends NotificationInboxController
{
    protected function notifications(Request $request): MorphMany
    {
        return $request->attributes->get('staff')->notifications();
    }
}
