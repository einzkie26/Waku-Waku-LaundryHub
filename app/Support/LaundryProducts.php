<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaundryProducts
{
    public const DETERGENT_SACHET_CENTS = 2400;
    public const SOFTENER_LITRE_CENTS = 2700;
    public const CUP_MICROLITRES = 1500;
    public const MAX_CUPS = 1000;
    public const SERVICES = ['Wash & Fold', 'Express Service'];
    public const DETERGENTS = [
        'ariel' => ['name' => 'Ariel', 'logo' => 'ariel.webp'],
        'breeze' => ['name' => 'Breeze', 'logo' => 'breeze.png'],
        'surf' => ['name' => 'Surf', 'logo' => 'surf.png'],
    ];
    public const SOFTENERS = [
        'downy' => ['name' => 'Downy', 'logo' => 'downy.webp'],
        'surf' => ['name' => 'Surf', 'logo' => 'surf.png'],
    ];

    public static function supports(string $service): bool
    {
        return in_array($service, self::SERVICES, true);
    }

    public static function rules(): array
    {
        return [
            'detergent_brand' => ['nullable', Rule::in(array_merge(['own'], array_keys(self::DETERGENTS)))],
            'detergent_sachets' => ['nullable', 'integer', 'min:0', 'max:50'],
            'softener_brand' => ['nullable', Rule::in(array_merge(['none'], array_keys(self::SOFTENERS)))],
            'softener_cups' => ['nullable', 'numeric', 'min:0', 'max:'.self::MAX_CUPS, 'multiple_of:0.5'],
        ];
    }

    public static function catalog(): array
    {
        return [
            'detergents' => self::DETERGENTS,
            'softeners' => self::SOFTENERS,
            'detergentSachetCents' => self::DETERGENT_SACHET_CENTS,
            'softenerLitreCents' => self::SOFTENER_LITRE_CENTS,
            'cupMicrolitres' => self::CUP_MICROLITRES,
            'maxCups' => self::MAX_CUPS,
            'services' => self::SERVICES,
        ];
    }

    public static function quote(array $data): array
    {
        $items = [];
        $detergent = $data['detergent_brand'] ?? 'own';
        $sachets = (int) ($data['detergent_sachets'] ?? 0);
        $softener = $data['softener_brand'] ?? 'none';
        $cups = (float) ($data['softener_cups'] ?? 0);
        if (!is_string($detergent)) {
            throw ValidationException::withMessages(['detergent_brand' => 'Choose an available detergent.']);
        }
        if (!is_string($softener)) {
            throw ValidationException::withMessages(['softener_brand' => 'Choose an available fabric softener.']);
        }
        if ($detergent && $detergent !== 'own') {
            if (!isset(self::DETERGENTS[$detergent])) {
                throw ValidationException::withMessages(['detergent_brand' => 'Choose an available detergent.']);
            }
            if ($sachets < 1 || $sachets > 50) {
                throw ValidationException::withMessages(['detergent_sachets' => 'Choose between 1 and 50 sachets.']);
            }
            $items[] = [
                'category' => 'detergent', 'brand' => $detergent, 'name' => self::DETERGENTS[$detergent]['name'],
                'unit' => 'sachet', 'quantity' => $sachets, 'rate_unit' => 'sachet', 'rate_cents' => self::DETERGENT_SACHET_CENTS,
                'amount_cents' => $sachets * self::DETERGENT_SACHET_CENTS,
            ];
        } elseif ($sachets !== 0) {
            throw ValidationException::withMessages(['detergent_brand' => 'Choose a detergent brand for the sachets.']);
        }
        if ($softener && $softener !== 'none') {
            if (!isset(self::SOFTENERS[$softener])) {
                throw ValidationException::withMessages(['softener_brand' => 'Choose an available fabric softener.']);
            }
            if ($cups < .5 || $cups > self::MAX_CUPS || abs($cups * 2 - round($cups * 2)) > .00001) {
                throw ValidationException::withMessages(['softener_cups' => 'Choose an amount in half-cup steps.']);
            }
            $microlitres = (int) round($cups * 2) * intdiv(self::CUP_MICROLITRES, 2);
            $amount = intdiv(self::SOFTENER_LITRE_CENTS * $microlitres + 500000, 1000000);
            $items[] = [
                'category' => 'softener', 'brand' => $softener, 'name' => self::SOFTENERS[$softener]['name'],
                'unit' => 'cup', 'quantity' => $cups, 'cup_ml' => self::CUP_MICROLITRES / 1000,
                'volume_ml' => $microlitres / 1000, 'rate_unit' => 'litre', 'rate_cents' => self::SOFTENER_LITRE_CENTS,
                'amount_cents' => $amount,
            ];
        } elseif ($cups != 0) {
            throw ValidationException::withMessages(['softener_brand' => 'Choose a fabric softener for this amount.']);
        }
        if ($items && !self::supports($data['service'])) {
            throw ValidationException::withMessages(['products' => 'Liquid products are available with Wash & Fold and Express Service.']);
        }

        return ['items' => $items, 'amount_cents' => array_sum(array_column($items, 'amount_cents'))];
    }
}
