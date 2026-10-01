<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            // Menggantikan kolom teks bebas 'keterangan' dengan dropdown bermaster.
            // Kolom 'keterangan' lama TIDAK dihapus, tetap dipakai sebagai fallback
            // tampilan untuk data yang sudah terlanjur diisi sebelum fitur ini ada.
            $table->foreignId('pertimbangan_id')->nullable()->after('rencana_wisuda_id')
                ->constrained('pertimbangans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pertimbangan_id');
        });
    }
};
