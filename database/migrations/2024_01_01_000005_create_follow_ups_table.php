<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('status_follow_up_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rencana_wisuda_id')->nullable()->constrained()->nullOnDelete();
            $table->text('keterangan')->nullable();          // Keterangan / Hasil Follow Up
            $table->text('follow_up_berikutnya')->nullable(); // catatan rencana follow up selanjutnya
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
