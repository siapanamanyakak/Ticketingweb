<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_staff')->unique()->nullable()->after('id');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('id_staff');
            $table->enum('role', ['user', 'it_support', 'it_supervisor'])->default('user')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['id_staff', 'department_id', 'role', 'is_active']);
        });
    }
};
