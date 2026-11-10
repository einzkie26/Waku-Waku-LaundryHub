<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Support\LaundryWorkflow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LaundryOrderObserver
{
    public function created(Order $order): void
    {
        $this->sendOperations($order, 'New order in the queue', $order->reference_no.' · '.$order->service.'. Ready to review.',
            $order->operator_type, (int) $order->operator_id);
        $this->sendCustomer($order, 'Booking received', $order->reference_no.' · '.$order->service.'. '.LaundryWorkflow::description('Pending')
            .($order->payment_status === 'paid' ? ' Your payment has been recorded.' : ''));
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $this->sendOperations($order, 'Laundry status updated', $order->reference_no.' · '.LaundryWorkflow::label($order->status).'.');
            $title = match ($order->status) {
                'Picked Up' => 'Your laundry was picked up',
                'Washing' => 'Your laundry is being washed',
                'Drying' => 'Your laundry is being dried',
                'Processing' => 'Your laundry is in progress',
                'Ready' => 'Your laundry is ready',
                'Completed' => 'Your laundry has been delivered',
                'Refunded' => 'Refund recorded',
                default => 'Your laundry has an update',
            };
            $this->sendCustomer($order, $title, $order->reference_no.' · '.LaundryWorkflow::description($order->status));
        } elseif ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            $this->sendOperations($order, 'Payment recorded', $order->reference_no.' has been marked as paid.');
            $this->sendCustomer($order, 'Payment received', 'Your payment for '.$order->reference_no.' has been recorded.');
        } elseif ($order->wasChanged(['date', 'time'])) {
            $message = $order->reference_no.' · Pickup is now '.Carbon::parse($order->date)->format('M j, Y')
                .' at '.Carbon::parse($order->time)->format('g:i A').'.';
            $this->sendOperations($order, 'Pickup rescheduled', $message);
            $this->sendCustomer($order, 'Pickup rescheduled', $message);
        }
    }

    private function sendCustomer(Order $order, string $title, string $message): void
    {
        $customer = User::whereKey($order->user_id)->where('role', 'customer')->first();
        $customer?->laundryNotifications()->create([
            'type' => 'order', 'title' => $title, 'message' => $message,
            'action_url' => route('order.tracking', $order->reference_no, false),
        ]);
    }

    private function sendOperations(Order $order, string $title, string $message, ?string $creatorType = null, ?int $creatorId = null): void
    {
        $web = Auth::guard('web')->user();
        $staffActor = ! $web ? (Auth::guard('staff')->id() ?? ($creatorType === 'staff' ? $creatorId : null)) : null;
        $adminActor = $web?->role === 'admin' ? $web->id : ($creatorType === 'admin' ? $creatorId : null);
        $now = now();
        $insert = function ($recipients, string $route) use ($order, $title, $message, $now) {
            Notification::insert($recipients->map(fn ($recipient) => [
                'notifiable_type' => $recipient->getMorphClass(), 'notifiable_id' => $recipient->id,
                'type' => 'order', 'title' => $title, 'message' => $message,
                'action_url' => route($route, $order->id, false),
                'created_at' => $now, 'updated_at' => $now,
            ])->all());
        };
        Employee::where('is_active', true)->whereRaw('LOWER(status) = ?', ['active'])
            ->when($staffActor, fn ($query) => $query->whereKeyNot($staffActor))
            ->select('id')->chunkById(200, fn ($employees) => $insert($employees, 'staff.orders.show'));
        User::where('role', 'admin')->when($adminActor, fn ($query) => $query->whereKeyNot($adminActor))
            ->select('id')->chunkById(200, fn ($admins) => $insert($admins, 'orders.show'));
    }
}
