<?php

namespace Database\Seeders;

use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            ['day_of_week' => 0, 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_working_day' => false], // Minggu
            ['day_of_week' => 1, 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_working_day' => true],  // Senin
            ['day_of_week' => 2, 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_working_day' => true],  // Selasa
            ['day_of_week' => 3, 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_working_day' => true],  // Rabu
            ['day_of_week' => 4, 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_working_day' => true],  // Kamis
            ['day_of_week' => 5, 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_working_day' => true],  // Jumat
            ['day_of_week' => 6, 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_working_day' => false], // Sabtu
        ];

        foreach ($schedules as $schedule) {
            WorkSchedule::create($schedule);
        }
    }
}
