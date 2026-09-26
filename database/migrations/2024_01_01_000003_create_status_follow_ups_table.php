<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: "Sudah Dihubungi", "Belum Dihubungi"
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_follow_ups');
    }
};
