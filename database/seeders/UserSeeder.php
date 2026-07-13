<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Services\UsernameService;


class UserSeeder extends Seeder
{

public function run(): void
{
    $usernameService = new UsernameService();
    $itDept = Department::where('name', 'IT')->first();

    User::create([
        'name'          => 'IT Supervisor',
        'username'      => $usernameService->generate('IT_Supervisor'),
        'email'         => 'supervisor@helpdesk.com',
        'password'      => Hash::make('password'),
        'id_staff'      => 'STF-001',
        'department_id' => $itDept->id,
        'role'          => 'it_supervisor',
        'is_active'     => true,
    ]);

    User::create([
        'name'          => 'IT Support',
        'username'      => $usernameService->generate('IT_Support'),
        'email'         => 'support@helpdesk.com',
        'password'      => Hash::make('password'),
        'id_staff'      => 'STF-002',
        'department_id' => $itDept->id,
        'role'          => 'it_support',
        'is_active'     => true,
    ]);

    User::create([
        'name'          => 'Staff User',
        'username'      => $usernameService->generate('Staff_User'),
        'email'         => 'user@helpdesk.com',
        'password'      => Hash::make('password'),
        'id_staff'      => 'STF-003',
        'department_id' => Department::where('name', 'HRD')->first()->id,
        'role'          => 'user',
        'is_active'     => true,
    ]);
}
}
