<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id_staff');
        });

        DB::statement("
            ALTER TABLE users
            MODIFY email VARCHAR(255) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY email VARCHAR(255) NOT NULL
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
