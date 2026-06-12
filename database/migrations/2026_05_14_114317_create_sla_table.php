<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('priority_id')->constrained('priorities')->onDelete('cascade');
            $table->integer('response_time');      // dalam menit
            $table->integer('resolution_time');    // dalam menit
            $table->boolean('working_hours_only')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla');
    }
};
