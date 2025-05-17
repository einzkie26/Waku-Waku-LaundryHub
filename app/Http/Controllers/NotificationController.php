<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLog;

class NotificationController extends Controller
{
    public function destroy($id)
    {
        $notification = UserLog::findOrFail($id);
        
        if ($notification->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $notification->delete();
        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    public function markAllAsRead()
    {
        auth()->user()->logs()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function getCount()
    {
        $count = auth()->user()->logs()->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }
} 