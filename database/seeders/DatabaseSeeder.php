<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\Pertimbangan;
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

        // Pilihan default Pertimbangan
        foreach (['Kendala Administrasi', 'Kendala Biaya', 'Kendala Akademik', 'Sudah Siap'] as $i => $nama) {
            Pertimbangan::firstOrCreate(['nama' => $nama], ['urutan' => $i]);
        }

        // Contoh Fakultas + Prodi (silakan disesuaikan/ditambah lewat menu
        // Master Fakultas & Prodi setelah login sebagai SuperAdmin)
        $teknik = Fakultas::firstOrCreate(['nama' => 'Fakultas Teknik']);
        foreach ([
            'Teknik Informatika',
            'Teknik Sipil',
            'Teknik Elektro',
            'Teknik Mesin',
            'Teknik Industri',
            'Arsitektur',
        ] as $prodi) {
            $teknik->prodis()->firstOrCreate(['nama' => $prodi]);
        }

        $ekonomi = Fakultas::firstOrCreate(['nama' => 'Fakultas Ekonomi dan Bisnis']);
        foreach (['Manajemen', 'Akuntansi', 'Ekonomi Pembangunan'] as $prodi) {
            $ekonomi->prodis()->firstOrCreate(['nama' => $prodi]);
        }
    }
}
