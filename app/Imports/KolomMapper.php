<?php

namespace App\Imports;

/**
 * Dipakai bareng oleh MahasiswaImport & MahasiswaPreviewImport. Tujuannya:
 * file Excel "mentah" dari sumber manapun (SIAKAD, Forlap/PDDikti, dll)
 * bisa langsung diupload TANPA harus diubah dulu ke format baku kita -
 * sistem yang mencari sendiri baris mana berisi header, dan kolom mana
 * berisi NPM/Nama/No.HP, berdasarkan NAMA kolomnya (bukan posisinya).
 */
trait KolomMapper
{
    /**
     * Daftar nama kolom yang dikenali untuk tiap field kita. Kalau nanti
     * ketemu format baru yang belum terdeteksi, tinggal tambah sinonimnya
     * di sini - tidak perlu ubah logic lain.
     */
    private array $sinonimKolom = [
        'no' => ['no', 'nomor', 'nomor urut', 'no urut'],
        'npm' => ['npm', 'nim', 'no induk mahasiswa', 'nomor induk mahasiswa'],
        'nama_mahasiswa' => ['nama mahasiswa', 'nama', 'nama mhs', 'nama lengkap', 'nama lengkap mahasiswa'],
        'no_hp' => [
            'nomor kontak', 'no hp', 'no telepon', 'nomor telepon', 'nomor hp',
            'kontak', 'telepon', 'hp', 'no handphone', 'nomor handphone',
            'no wa', 'whatsapp', 'no whatsapp',
        ],
        'no_hp_2' => [
            'nomor kontak 2', 'no hp 2', 'no telepon 2', 'nomor telepon 2', 'nomor hp 2',
            'kontak 2', 'telepon 2', 'hp 2', 'no handphone 2', 'nomor handphone 2',
            'no wa 2', 'whatsapp 2', 'no whatsapp 2',
        ],
    ];

    private function normalisasiTeks(string $teks): string
    {
        $teks = strtolower(trim($teks));
        $teks = str_replace(['_', '-', '.'], ' ', $teks);
        $teks = preg_replace('/\s+/', ' ', $teks);

        return trim($teks);
    }

    /**
     * Ubah index kolom (0, 1, 2, ...) jadi huruf kolom Excel (A, B, C, ...)
     * supaya informasinya enak dibaca manusia di halaman preview.
     */
    private function kolomKeHuruf(int $index): string
    {
        $huruf = '';
        $index++;
        while ($index > 0) {
            $sisa = ($index - 1) % 26;
            $huruf = chr(65 + $sisa) . $huruf;
            $index = intdiv($index - 1, 26);
        }

        return $huruf;
    }

    /**
     * Cari baris mana yang berisi header, dan kolom mana yang cocok untuk
     * tiap field kita. NPM dan Nama Mahasiswa WAJIB ketemu supaya baris itu
     * dianggap valid sebagai header; No.HP, No.HP 2, dan No urut sifatnya
     * opsional. Mengecek maksimal 10 baris pertama saja (header biasanya
     * ada di awal file).
     *
     * @return array{baris_index:int, pemetaan: array<string, array{index:int, label:string}>}|null
     */
    private function deteksiHeaderDanPemetaan(array $baris): ?array
    {
        $maksBarisDicek = min(10, count($baris));
        $terbaik = null;

        for ($i = 0; $i < $maksBarisDicek; $i++) {
            $pemetaan = [];

            foreach ($baris[$i] as $kolIndex => $sel) {
                $norm = $this->normalisasiTeks((string) $sel);
                if ($norm === '') {
                    continue;
                }

                foreach ($this->sinonimKolom as $field => $sinonimList) {
                    if (isset($pemetaan[$field])) {
                        continue; // kolom pertama yang cocok untuk field ini yang dipakai
                    }
                    if (in_array($norm, $sinonimList, true)) {
                        $pemetaan[$field] = ['index' => $kolIndex, 'label' => trim((string) $sel)];
                        break;
                    }
                }
            }

            $valid = isset($pemetaan['npm']) && isset($pemetaan['nama_mahasiswa']);

            if ($valid && (! $terbaik || count($pemetaan) > count($terbaik['pemetaan']))) {
                $terbaik = ['baris_index' => $i, 'pemetaan' => $pemetaan];
            }
        }

        return $terbaik;
    }

    private function sanitizePhone(mixed $raw): ?string
    {
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        $bersih = preg_replace('/[^\d+]/', '', (string) $raw);

        return $bersih === '' ? null : $bersih;
    }
}
