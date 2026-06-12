<?php

namespace Database\Seeders;

use App\Models\Priority;
use App\Models\PriorityKeyword;
use Illuminate\Database\Seeder;

class PriorityKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $keywords = [
            'critical' => [
                'urgent', 'darurat', 'segera', 'sangat mendesak', 'tidak bisa kerja',
                'seluruh', 'semua', 'down', 'mati total', 'server', 'tidak bisa operasional',
                'sistem mati', 'produksi berhenti', 'emergency',
            ],
            'high' => [
                'cepat', 'butuh cepat', 'mendesak', 'penting', 'hari ini',
                'sekarang', 'buru-buru', 'deadline', 'segera ditangani',
                'sangat penting', 'tolong cepat',
            ],
            'medium' => [
                'agak', 'lumayan', 'cukup penting', 'sesegera mungkin',
                'kalau bisa', 'mohon dibantu', 'perlu bantuan',
            ],
        ];

        foreach ($keywords as $level => $words) {
            $priority = Priority::where('level', $level)->first();
            if (!$priority) continue;

            foreach ($words as $word) {
                PriorityKeyword::firstOrCreate([
                    'priority_id' => $priority->id,
                    'keyword'     => $word,
                ]);
            }
        }
    }
}
