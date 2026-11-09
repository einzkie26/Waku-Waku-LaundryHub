<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class LaundryWorkflow
{
    public const SERVICES = [
        'Wash & Fold' => ['price' => 50, 'description' => 'Everyday clothes, freshly washed and neatly folded.', 'icon' => 'basket', 'image' => 'w&f.png'],
        'Dry Cleaning' => ['price' => 70, 'description' => 'Special care for delicate and structured garments.', 'icon' => 'stars', 'image' => 'drycleaning.png'],
        'Express Service' => ['price' => 100, 'description' => 'Priority processing when your laundry cannot wait.', 'icon' => 'lightning-charge', 'image' => 'xpress.png'],
        'Ironing' => ['price' => 40, 'description' => 'A smooth, crisp finish for your clean clothes.', 'icon' => 'wind', 'image' => 'ironing.png'],
    ];

    public const EXTRAS = [
        'Scent Boost' => 25,
        'Hang Dry' => 20,
        'With Packaging' => 35,
    ];

    public const STYLES = ['Regular Wash', 'Delicate Wash', 'Hand Wash', 'Dry Clean Only', 'Express Wash'];
    public const FABRICS = ['Cotton', 'Polyester', 'Wool', 'Silk', 'Linen', 'Denim', 'Synthetic', 'Mixed'];
    public const TIMES = ['09:00' => '9:00 AM', '10:00' => '10:00 AM', '11:00' => '11:00 AM', '14:00' => '2:00 PM', '16:00' => '4:00 PM'];

    public static function steps(string $service): array
    {
        return $service === 'Wash & Fold'
            ? ['Pending', 'Picked Up', 'Washing', 'Drying', 'Ready', 'Completed']
            : ['Pending', 'Picked Up', 'Processing', 'Ready', 'Completed'];
    }

    public static function nextStatus(string $service, string $status): ?string
    {
        $steps = self::steps($service);
        $index = array_search($status, $steps, true);

        return $index === false ? null : ($steps[$index + 1] ?? null);
    }

    public static function label(string $status): string
    {
        return match ($status) {
            'Pending' => 'Awaiting pickup',
            'Picked Up' => 'Picked up',
            'Ready' => 'Ready for delivery',
            default => $status,
        };
    }

    public static function action(?string $nextStatus): string
    {
        return match ($nextStatus) {
            'Picked Up' => 'Confirm pickup',
            'Washing' => 'Start washing',
            'Drying' => 'Start drying',
            'Processing' => 'Start processing',
            'Ready' => 'Mark ready for delivery',
            'Completed' => 'Confirm delivery',
            default => 'View order',
        };
    }

    public static function description(string $status): string
    {
        return match ($status) {
            'Pending' => 'Your booking is received. Have your laundry ready at the pickup address for your selected time.',
            'Picked Up' => 'Your laundry has been collected and is waiting for cleaning.',
            'Washing' => 'Your laundry is being washed with care.',
            'Drying' => 'Your laundry is being dried before folding and packing.',
            'Processing' => 'Your selected cleaning or ironing service is in progress.',
            'Ready' => 'Your laundry is clean and packed. Our team will arrange delivery.',
            'Completed' => 'Your laundry has been handed back to you. Thank you for choosing Waku-Waku!',
            'Refunded' => 'This order is closed as refunded. Contact the shop for any questions about the refund.',
            default => 'Our team is reviewing your order.',
        };
    }

    public static function rules(bool $operator = false): array
    {
        $rules = [
            'service' => ['required', Rule::in(array_keys(self::SERVICES))],
            'laundry_style' => ['required', Rule::in(array_merge(self::STYLES, ['Regular', 'Delicate']))],
            'fabric_type' => ['required', Rule::in(self::FABRICS)],
            'weight' => ['required', 'numeric', 'min:0.1', 'max:1000', 'decimal:0,1'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', Rule::in(array_keys(self::TIMES))],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'additional_services' => ['nullable', 'array'],
            'additional_services.*' => ['string', 'distinct', Rule::in(array_keys(self::EXTRAS))],
        ];

        if ($operator) {
            $rules += [
                'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'customer')],
                'payment_status' => ['required', Rule::in(['pending', 'paid'])],
                'payment_method' => ['nullable', 'required_if:payment_status,paid', Rule::in(['Cash', 'GCash', 'Bank'])],
                'payment_reference' => ['nullable', 'string', 'max:100', 'required_if:payment_method,GCash,Bank'],
            ];
        }

        return $rules + LaundryProducts::rules();
    }
}
