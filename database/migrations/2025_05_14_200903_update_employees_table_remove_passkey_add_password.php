<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add password column if it doesn't exist
            if (!Schema::hasColumn('employees', 'password')) {
                $table->string('password')->after('status');
            }
            // Add remember_token if it doesn't exist
            if (!Schema::hasColumn('employees', 'remember_token')) {
                $table->rememberToken();
            }
        });

        // Update existing employees with a default password
        $employees = Employee::all();
        foreach ($employees as $employee) {
            if (empty($employee->password)) {
                $employee->password = Hash::make('password123'); // Default password
                $employee->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Remove password and remember_token
            $table->dropColumn(['password', 'remember_token']);
        });
    }
};
