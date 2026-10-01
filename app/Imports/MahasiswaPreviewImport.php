<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Hanya MEMBACA file Excel dan menyusun ringkasan (tanpa menyimpan apa pun
 * ke database), dipakai untuk halaman Preview sebelum import sungguhan.
 */
class MahasiswaPreviewImport implements ToCollection, WithHeadingRow
{
    public array $rows = [];
    public int $totalBaru = 0;
    public int $totalDiperbarui = 0;
    public int $totalDilewati = 0;

    public function headingRow(): int
    {
        return 2;
    }

    public function collection(Collection $collection): void
    {
        // Lacak NPM yang sudah muncul di file ini (bukan di database), supaya
        // baris NPM dobel DALAM 1 file yang sama bisa terdeteksi dan
        // diperingatkan - karena kalau dibiarkan, baris yang diproses
        // belakangan akan menimpa baris sebelumnya tanpa pemberitahuan.
        $npmTerlihat = [];

        // Lacak nomor HP yang sudah muncul di file ini, dipakai NPM mana.
        // Beda dengan NPM dobel: ini CUMA peringatan info (nomor HP memang
        // boleh sama, misal kakak-adik pakai HP orang tua), bukan error -
        // jadi tidak mengubah status baru/diperbarui/dilewati.
        $nomorTerlihat = [];

        foreach ($collection as $row) {
            $npm = trim((string) ($row['npm'] ?? ''));
            $nama = trim((string) ($row['nama_mahasiswa'] ?? ''));
            $noHp = $this->sanitizePhone($row['nomor_kontak'] ?? null);
            $noHp2 = $this->sanitizePhone($row['nomor_kontak_2'] ?? null);
            $baris = $row['no'] ?? '?';

            $item = [
                'no' => $row['no'] ?? null,
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
                // NPM ini sudah pernah muncul di baris sebelumnya DI FILE INI JUGA.
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

            // Peringatan nomor HP sama dipakai NPM lain (bukan error, cuma info)
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
        }
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