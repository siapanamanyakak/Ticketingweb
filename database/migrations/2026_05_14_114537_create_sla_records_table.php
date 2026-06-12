<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->timestamp('response_deadline')->nullable();
            $table->timestamp('resolution_deadline')->nullable();
            $table->timestamp('response_met_at')->nullable();
            $table->timestamp('resolution_met_at')->nullable();
            $table->integer('total_paused_minutes')->default(0);
            $table->boolean('response_breached')->default(false);
            $table->boolean('resolution_breached')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_records');
    }
};
