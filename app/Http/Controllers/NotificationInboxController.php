<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;

abstract class NotificationInboxController extends Controller
{
    abstract protected function notifications(Request $request): MorphMany;

    private function inbox(Request $request): MorphMany
    {
        $query = $this->notifications($request);
        $owner = $query->getParent();
        $role = $owner instanceof Employee ? 'staff' : $owner->role;
        $expected = $request->header('X-Notification-Recipient');
        abort_if($expected !== null && $expected !== $role.':'.$owner->id, 401);

        return $query;
    }

    public function index(Request $request)
    {
        $data = $request->validate(['after' => ['nullable', 'integer', 'min:0']]);
        $query = $this->inbox($request);
        $recipient = $query->getParent();
        $role = $recipient instanceof Employee ? 'staff' : $recipient->role;
        $latest = (int) (clone $query)->max('id');
        $items = (clone $query)->latest('id')->limit(30)->get();
        $events = isset($data['after'])
            ? (clone $query)->where('id', '>', $data['after'])->whereNull('read_at')->orderBy('id')->limit(100)->get()
            : collect();
        $serialize = fn ($item) => [
            'id' => $item->id, 'title' => $item->title, 'message' => $item->message,
            'url' => $item->action_url, 'created_at' => $item->created_at->toIso8601String(),
            'read_at' => $item->read_at?->toIso8601String(),
        ];

        return response()->json([
            'recipient_key' => $role.':'.$recipient->id,
            'notifications' => $items->map($serialize), 'events' => $events->map($serialize),
            'unread_count' => (clone $query)->whereNull('read_at')->count(),
            'latest_id' => $events->count() === 100 ? $events->last()->id : max($latest, $events->last()?->id ?? 0),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function read(Request $request, int $notification)
    {
        $this->inbox($request)->findOrFail($notification)->markAsRead();

        return response()->json(['success' => true]);
    }

    public function readAll(Request $request)
    {
        $this->inbox($request)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
