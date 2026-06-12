<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->string('field_changed');
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_logs');
    }
};
