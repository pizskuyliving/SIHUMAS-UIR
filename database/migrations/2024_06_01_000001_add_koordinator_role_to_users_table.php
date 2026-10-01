<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enum MySQL tidak bisa diubah lewat Schema::table biasa tanpa
        // paket doctrine/dbal, jadi dipakai raw SQL langsung.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'koordinator', 'pic') NOT NULL DEFAULT 'pic'");
    }

    public function down(): void
    {
        // Kalau ada akun koordinator saat rollback, ubah dulu jadi 'pic'
        // supaya tidak melanggar enum lama waktu kolom dikembalikan.
        DB::statement("UPDATE users SET role = 'pic' WHERE role = 'koordinator'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'pic') NOT NULL DEFAULT 'pic'");
    }
};
