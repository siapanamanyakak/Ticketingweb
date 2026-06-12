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
            'low'      => ['response_time' => 480,  'resolution_time' => 4320], // 8 jam, 72 jam
            'medium'   => ['response_time' => 240,  'resolution_time' => 2880], // 4 jam, 48 jam
            'high'     => ['response_time' => 60,   'resolution_time' => 1800], // 1 jam, 30 jam
            'critical' => ['response_time' => 30,   'resolution_time' => 480],  // 30 menit, 8 jam
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
