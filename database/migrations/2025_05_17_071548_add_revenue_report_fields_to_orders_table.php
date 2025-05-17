<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add total_amount field (for consistency with revenue calculations)
            $table->decimal('total_amount', 10, 2)->after('total_price');
            
            // Add service_id field for better service tracking
            $table->foreignId('service_id')->nullable()->after('service')->constrained('services');
            
            // Add customer_id field for better customer tracking
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('users');
            
            // Add completion_date for better revenue tracking
            $table->timestamp('completion_date')->nullable()->after('paid_at');
            
            // Add revenue_category for better revenue categorization
            $table->string('revenue_category')->nullable()->after('service_id');
            
            // Add payment_details for better payment tracking
            $table->json('payment_details')->nullable()->after('payment_reference');
        });

        // Update existing records
        DB::statement('UPDATE orders SET total_amount = total_price WHERE total_amount IS NULL');
        DB::statement('UPDATE orders SET customer_id = user_id WHERE customer_id IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'total_amount',
                'service_id',
                'customer_id',
                'completion_date',
                'revenue_category',
                'payment_details'
            ]);
        });
    }
};
