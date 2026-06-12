<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $itDept = Department::where('name', 'IT')->first();

        // IT Supervisor
        User::create([
            'name'          => 'IT Supervisor',
            'email'         => 'supervisor@helpdesk.com',
            'password'      => Hash::make('password'),
            'id_staff'      => 'STF-001',
            'department_id' => $itDept->id,
            'role'          => 'it_supervisor',
            'is_active'     => true,
        ]);

        // IT Support
        User::create([
            'name'          => 'IT Support',
            'email'         => 'support@helpdesk.com',
            'password'      => Hash::make('password'),
            'id_staff'      => 'STF-002',
            'department_id' => $itDept->id,
            'role'          => 'it_support',
            'is_active'     => true,
        ]);

        // User biasa
        User::create([
            'name'          => 'Staff User',
            'email'         => 'user@helpdesk.com',
            'password'      => Hash::make('password'),
            'id_staff'      => 'STF-003',
            'department_id' => Department::where('name', 'HRD')->first()->id,
            'role'          => 'user',
            'is_active'     => true,
        ]);
    }
}
