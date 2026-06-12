<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->enum('visibility', ['all', 'support_only'])
                  ->default('support_only')
                  ->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
