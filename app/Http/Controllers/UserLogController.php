<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLog;

class UserLogController extends Controller
{
    public function destroy($id)
    {
        $log = UserLog::findOrFail($id);
        if ($log->user_id !== auth()->id()) {
            abort(403);
        }

        $log->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function getCount()
    {
        $count = auth()->user()->logs()
            ->where('created_at', '>=', now()->subDay())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAllAsRead()
    {
        auth()->user()->logs()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}

