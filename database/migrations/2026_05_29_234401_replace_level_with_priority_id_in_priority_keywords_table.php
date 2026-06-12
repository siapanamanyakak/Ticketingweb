<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Priority;
use App\Models\PriorityKeyword;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: tambah kolom priority_id dulu
        Schema::table('priority_keywords', function (Blueprint $table) {
            $table->foreignId('priority_id')->nullable()->constrained('priorities')->onDelete('cascade')->after('id');
        });

        // Step 2: migrate data level → priority_id
        $priorities = Priority::all()->keyBy('level');

        PriorityKeyword::all()->each(function ($kw) use ($priorities) {
            $priority = $priorities->get($kw->level);
            if ($priority) {
                $kw->update(['priority_id' => $priority->id]);
            }
        });

        // Step 3: hapus kolom level
        Schema::table('priority_keywords', function (Blueprint $table) {
            $table->dropColumn('level');
        });

        // Step 4: set priority_id jadi not nullable
        Schema::table('priority_keywords', function (Blueprint $table) {
            $table->foreignId('priority_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('priority_keywords', function (Blueprint $table) {
            $table->enum('level', ['critical', 'high', 'medium'])->after('id');
        });

        // Restore data
        PriorityKeyword::with('priority')->get()->each(function ($kw) {
            $kw->update(['level' => $kw->priority->level]);
        });

        Schema::table('priority_keywords', function (Blueprint $table) {
            $table->dropForeign(['priority_id']);
            $table->dropColumn('priority_id');
        });
    }
};
