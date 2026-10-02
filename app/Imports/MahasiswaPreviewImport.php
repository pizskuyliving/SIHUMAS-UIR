<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Hanya MEMBACA file Excel dan menyusun ringkasan (tanpa menyimpan apa pun
 * ke database), dipakai untuk halaman Preview sebelum import sungguhan.
 * Memakai deteksi kolom otomatis (lihat KolomMapper) - jadi file Excel
 * "mentah" dari sumber manapun bisa langsung dibaca, tidak harus ikut
 * format baku (header baris 2, urutan kolom tertentu).
 */
class MahasiswaPreviewImport implements ToArray
{
    use KolomMapper;

    public array $rows = [];
    public int $totalBaru = 0;
    public int $totalDiperbarui = 0;
    public int $totalDilewati = 0;

    /** Info kolom yang berhasil dideteksi, utk ditampilkan ke user di preview */
    public ?array $kolomTerdeteksi = null;

    /** Kalau NPM/Nama Mahasiswa sama sekali tidak ketemu di file ini */
    public ?string $pesanGagalDeteksi = null;

    public function array(array $array): void
    {
        $deteksi = $this->deteksiHeaderDanPemetaan($array);

        if (! $deteksi) {
            $this->pesanGagalDeteksi = 'Tidak ditemukan kolom NPM dan Nama Mahasiswa di 10 baris pertama file ini. '
                . 'Pastikan file punya baris header dengan nama kolom yang jelas (misal "NPM" dan "Nama Mahasiswa").';

            return;
        }

        $pemetaan = $deteksi['pemetaan'];
        $this->kolomTerdeteksi = $pemetaan;

        $dataRows = array_slice($array, $deteksi['baris_index'] + 1);
        $npmTerlihat = [];
        $nomorTerlihat = [];
        $urutan = 1;

        foreach ($dataRows as $row) {
            $npm = trim((string) ($row[$pemetaan['npm']['index']] ?? ''));
            $nama = trim((string) ($row[$pemetaan['nama_mahasiswa']['index']] ?? ''));
            $noHp = isset($pemetaan['no_hp']) ? $this->sanitizePhone($row[$pemetaan['no_hp']['index']] ?? null) : null;
            $noHp2 = isset($pemetaan['no_hp_2']) ? $this->sanitizePhone($row[$pemetaan['no_hp_2']['index']] ?? null) : null;
            $noAsli = isset($pemetaan['no']) ? ($row[$pemetaan['no']['index']] ?? null) : null;

            if ($npm === '' && $nama === '') {
                continue; // baris benar-benar kosong, lewati tanpa dihitung sama sekali
            }

            $baris = $noAsli ?? $urutan;

            $item = [
                'no' => $baris,
                'nama' => $nama ?: null,
                'npm' => $npm ?: null,
                'no_hp' => $noHp,
                'no_hp_2' => $noHp2,
                'status' => null,
                'alasan' => null,
            ];

            if ($npm === '' || $nama === '') {
                $this->totalDilewati++;
                $item['status'] = 'dilewati';
                $item['alasan'] = 'Nama atau NPM kosong';
            } elseif (! preg_match('/^\d+$/', $npm)) {
                $this->totalDilewati++;
                $item['status'] = 'dilewati';
                $item['alasan'] = 'NPM harus berupa angka';
            } elseif (isset($npmTerlihat[$npm])) {
                $this->totalDiperbarui++;
                $item['status'] = 'diperbarui';
                $item['alasan'] = "NPM dobel dengan baris {$npmTerlihat[$npm]} di file ini - data baris itu akan TERTIMPA oleh baris ini";
            } elseif (Mahasiswa::withTrashed()->where('npm', $npm)->exists()) {
                $this->totalDiperbarui++;
                $item['status'] = 'diperbarui';
            } else {
                $this->totalBaru++;
                $item['status'] = 'baru';
            }

            $npmValid = $npm !== '' && preg_match('/^\d+$/', $npm);
            if ($npmValid) {
                $peringatanNomor = [];
                foreach (array_filter([$noHp, $noHp2]) as $nomor) {
                    if (isset($nomorTerlihat[$nomor]) && $nomorTerlihat[$nomor]['npm'] !== $npm) {
                        $peringatanNomor[] = "No. HP {$nomor} sama dengan baris {$nomorTerlihat[$nomor]['baris']}";
                    }
                }
                if ($peringatanNomor) {
                    $pesan = implode('; ', array_unique($peringatanNomor));
                    $item['alasan'] = $item['alasan'] ? "{$item['alasan']} | {$pesan}" : $pesan;
                }

                foreach (array_filter([$noHp, $noHp2]) as $nomor) {
                    if (! isset($nomorTerlihat[$nomor])) {
                        $nomorTerlihat[$nomor] = ['baris' => $baris, 'npm' => $npm];
                    }
                }

                $npmTerlihat[$npm] = $baris;
            }

            $this->rows[] = $item;
            $urutan++;
        }
    }
}
