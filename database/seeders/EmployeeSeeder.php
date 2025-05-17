<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::create([
            'name' => 'Test Staff',
            'position' => 'Staff',
            'contact' => '09123456789',
            'email' => 'staff@test.com',
            'hire_date' => Carbon::now(),
            'shift' => 'Morning',
            'status' => 'active',
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);
    }
} 