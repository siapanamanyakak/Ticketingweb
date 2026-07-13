<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            CategorySeeder::class,
            PrioritySeeder::class,
            SlaSeeder::class,
            WorkScheduleSeeder::class,
            UserSeeder::class,
            FullKeywordSeeder::class,
            // CategoryKeywordSeeder::class,
            // PriorityKeywordSeeder::class,
        ]);
    }
}
