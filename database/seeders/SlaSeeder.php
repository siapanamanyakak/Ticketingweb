<?php

namespace Database\Seeders;

use App\Models\Priority;
use App\Models\Sla;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    public function run(): void
    {
        $slaData = [
            'low'      => ['response_time' => 120,  'resolution_time' => 480],
            'medium'   => ['response_time' => 100,  'resolution_time' => 400],
            'high'     => ['response_time' => 60,   'resolution_time' => 240],
            'critical' => ['response_time' => 30,   'resolution_time' => 480],
        ];

        foreach ($slaData as $level => $times) {
            $priority = Priority::where('level', $level)->first();
            if ($priority) {
                Sla::create([
                    'priority_id'        => $priority->id,
                    'response_time'      => $times['response_time'],
                    'resolution_time'    => $times['resolution_time'],
                    'working_hours_only' => true,
                ]);
            }
        }
    }
}
