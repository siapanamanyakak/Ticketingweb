<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priority_keywords', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['critical', 'high', 'medium']);
            $table->string('keyword');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_keywords');
    }
};
