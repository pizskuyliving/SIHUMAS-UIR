<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Soft delete: data "dihapus" cukup ditandai deleted_at, tidak
            // benar-benar dibuang dari database. Bisa dipulihkan dari menu
            // Sampah (khusus SuperAdmin) selama belum dihapus permanen.
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
