<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('base_priority', ['low', 'medium', 'high', 'critical'])
                  ->default('low')
                  ->after('description');
            $table->enum('max_priority', ['low', 'medium', 'high', 'critical'])
                  ->default('high')
                  ->after('base_priority');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['base_priority', 'max_priority']);
        });
    }
};
