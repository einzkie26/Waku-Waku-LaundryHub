<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add laundry style fields
            $table->string('laundry_style')->nullable()->after('service');
            $table->string('fabric_type')->nullable()->after('laundry_style');
            $table->text('special_instructions')->nullable()->after('fabric_type');
            $table->json('additional_services')->nullable()->after('special_instructions');
            $table->decimal('additional_charges', 10, 2)->default(0)->after('additional_services');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'laundry_style',
                'fabric_type',
                'special_instructions',
                'additional_services',
                'additional_charges'
            ]);
        });
    }
}; 