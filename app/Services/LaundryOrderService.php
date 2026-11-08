<?php

namespace App\Services;

use App\Models\Order;
use App\Support\LaundryWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LaundryOrderService
{
    public function create(array $data, int $customerId, string $operatorType, int $operatorId): Order
    {
        if (\Illuminate\Support\Carbon::createFromFormat('!Y-m-d H:i', $data['date'].' '.$data['time'])->isPast()) {
            throw ValidationException::withMessages(['time' => 'Choose a pickup date and time that are still in the future.']);
        }
        if (in_array($data['service'], $data['additional_services'] ?? [], true)) {
            throw ValidationException::withMessages(['additional_services' => 'Your selected service is already included. Remove the matching extra to avoid paying twice.']);
        }
        return DB::transaction(function () use ($data, $customerId, $operatorType, $operatorId) {
            $extras = array_values(array_unique($data['additional_services'] ?? []));
            $additionalCharges = array_sum(array_intersect_key(LaundryWorkflow::EXTRAS, array_flip($extras)));
            $basePrice = round(LaundryWorkflow::SERVICES[$data['service']]['price'] * (float) $data['weight'], 2);

            do {
                $reference = 'REF-'.Str::upper(Str::random(8));
            } while (Order::where('reference_no', $reference)->exists());

            $paid = ($data['payment_status'] ?? 'pending') === 'paid';
            $order = Order::create([
                'user_id' => $customerId,
                'service' => $data['service'],
                'laundry_style' => $data['laundry_style'],
                'fabric_type' => $data['fabric_type'],
                'weight' => $data['weight'],
                'date' => $data['date'],
                'time' => $data['time'],
                'special_instructions' => $data['special_instructions'] ?? null,
                'additional_services' => $extras,
                'additional_charges' => $additionalCharges,
                'total_price' => $basePrice,
                'total_amount' => $basePrice + $additionalCharges,
                'reference_no' => $reference,
                'status' => 'Pending',
                'payment_status' => $paid ? 'paid' : 'pending',
                'payment_method' => $paid ? ($data['payment_method'] ?? null) : null,
                'payment_reference' => $paid ? ($data['payment_reference'] ?? null) : null,
                'paid_at' => $paid ? now() : null,
                'operator_type' => $operatorType,
                'operator_id' => $operatorId,
            ]);

            $order->statusHistories()->create([
                'status' => 'Pending',
                'changed_by_type' => $operatorType,
                'changed_by_id' => $operatorId,
            ]);

            return $order;
        });
    }

    public function recordPayment(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            if ($order->status === 'Refunded') {
                throw ValidationException::withMessages(['payment' => 'A refunded order cannot receive another payment.']);
            }
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid', 'payment_method' => $data['payment_method'],
                    'payment_reference' => $data['payment_reference'] ?? null, 'paid_at' => now(),
                ]);
            }
            return $order;
        });
    }

    public function advance(Order $order, string $operatorType, int $operatorId, ?string $expectedStatus = null): Order
    {
        return DB::transaction(function () use ($order, $operatorType, $operatorId, $expectedStatus) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            if ($expectedStatus !== null && $order->status !== $expectedStatus) {
                throw ValidationException::withMessages(['status' => 'This order has already changed. Review its current status before continuing.']);
            }

            $next = LaundryWorkflow::nextStatus($order->service, $order->status);
            if ($next === null) {
                throw ValidationException::withMessages(['status' => 'This order is closed and cannot advance further.']);
            }

            $order->update([
                'status' => $next,
                'completion_date' => $next === 'Completed' ? now() : null,
            ]);
            $order->statusHistories()->create([
                'status' => $next,
                'changed_by_type' => $operatorType,
                'changed_by_id' => $operatorId,
            ]);

            return $order;
        });
    }
}
