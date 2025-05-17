<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DiscountCode;

class DiscountCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $codes = ['WELCOME10', 'SUMMER15', 'VIP20', 'SPRINGMONTH20'];

        foreach ($codes as $code) {
            $discountPercent = intval(substr($code, -2));

            if ($discountPercent <= 0 || $discountPercent > 100) {
                continue;
            }

            DiscountCode::firstOrCreate([
                'code' => $code
            ], [
                'discount_percent' => $discountPercent,
                'used' => false,
                'user_id' => null,
            ]);
        }
    }
}

