<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('major_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add major_categories_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('major_category_id')->nullable()->constrained('major_categories')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['major_category_id']);
            $table->dropColumn('major_category_id');
        });

        Schema::dropIfExists('major_categories');
    }
};
