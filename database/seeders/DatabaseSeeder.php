<?php

namespace Database\Seeders;

use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun SuperAdmin awal (WAJIB ganti password setelah login pertama)
        User::firstOrCreate(
            ['email' => 'admin@uir.ac.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'is_active' => true,
            ]
        );

        // Pilihan default Status Follow Up
        foreach (['Belum Dihubungi', 'Sudah Dihubungi', 'Tidak Bisa Dihubungi'] as $i => $nama) {
            StatusFollowUp::firstOrCreate(['nama' => $nama], ['urutan' => $i]);
        }

        // Pilihan default Rencana Wisuda
        foreach (['Ya', 'Belum Pasti', 'Tidak'] as $i => $nama) {
            RencanaWisuda::firstOrCreate(['nama' => $nama], ['urutan' => $i]);
        }
    }
}
