<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // Ini kuncinya bang! Mengecek dulu sebelum bertindak.
    // Kalau kolomnya ada, hapus. Kalau tidak ada, ya sudah diam saja (skip).
    if (Schema::hasColumn('ticket_comments', 'parent_id')) {
        Schema::table('ticket_comments', function (Blueprint $table) {

            // Opsional: Kalau sebelumnya yakin parent_id itu foreign key, hapus foreign-nya dulu.
            // Biar aman, kita cek dulu apakah relasi foreign key-nya beneran ada.
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = $sm->listTableForeignKeys('ticket_comments');
            $hasForeignKey = collect($foreignKeys)->contains(function ($fk) {
                return in_array('parent_id', $fk->getLocalColumns());
            });

            if ($hasForeignKey) {
                $table->dropForeign(['parent_id']);
            }

            // Baru hapus kolom fisiknya
            $table->dropColumn('parent_id');
        });
    }
}

public function down(): void
{
    // Kalau mau di-rollback (kembali ke masa lalu), pastikan kolomnya belum ada sebelum dibuat
    if (!Schema::hasColumn('ticket_comments', 'parent_id')) {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('ticket_comments')
                  ->nullOnDelete();
        });
    }
}
};
