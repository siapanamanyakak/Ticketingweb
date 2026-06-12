<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['name' => 'Low',      'level' => 'low',      'description' => 'Tidak mengganggu produktivitas'],
            ['name' => 'Medium',   'level' => 'medium',   'description' => 'Sedikit mengganggu produktivitas'],
            ['name' => 'High',     'level' => 'high',     'description' => 'Mengganggu produktivitas'],
            ['name' => 'Critical', 'level' => 'critical', 'description' => 'Menghentikan operasional'],
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}
