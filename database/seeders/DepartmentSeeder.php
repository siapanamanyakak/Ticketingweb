<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'IT',
            'HRD',
            'Finance',
            'Marketing',
            'Operations',
            'Management',
        ];

        foreach ($departments as $dept) {
            Department::create(['name' => $dept]);
        }
    }
}
