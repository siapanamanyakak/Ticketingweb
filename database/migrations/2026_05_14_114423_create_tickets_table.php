<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('priorities')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('attachment')->nullable();
            $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('pending_at')->nullable();
            $table->integer('pending_duration')->default(0); // akumulasi menit pending
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('had_pending')->default(false);
            $table->integer('pending_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
