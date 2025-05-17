<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default services
        DB::table('services')->insert([
            [
                'name' => 'Wash & Fold',
                'code' => 'WF',
                'description' => 'Basic laundry service including washing and folding',
                'base_price' => 50.00,
                'category' => 'Basic',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Dry Cleaning',
                'code' => 'DC',
                'description' => 'Professional dry cleaning service',
                'base_price' => 70.00,
                'category' => 'Premium',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Express Service',
                'code' => 'ES',
                'description' => 'Same day laundry service',
                'base_price' => 100.00,
                'category' => 'Express',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Ironing',
                'code' => 'IR',
                'description' => 'Professional ironing service',
                'base_price' => 40.00,
                'category' => 'Basic',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
}; 