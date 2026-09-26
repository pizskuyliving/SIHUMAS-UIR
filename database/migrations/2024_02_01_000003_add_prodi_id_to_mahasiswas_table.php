<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Data lama yang fakultas-nya diisi manual (kolom teks 'fakultas') tetap
            // dipertahankan untuk kompatibilitas. Data baru yang diupload lewat
            // halaman Import per Fakultas/Prodi akan mengisi prodi_id ini.
            $table->foreignId('prodi_id')->nullable()->after('fakultas')->constrained('prodis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prodi_id');
        });
    }
};
