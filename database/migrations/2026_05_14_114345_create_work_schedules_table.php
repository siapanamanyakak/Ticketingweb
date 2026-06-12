<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week');   // 0=Minggu, 1=Senin, ..., 6=Sabtu
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_working_day')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
