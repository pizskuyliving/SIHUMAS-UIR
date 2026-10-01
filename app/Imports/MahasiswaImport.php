<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class MahasiswaImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;
    public int $updated = 0;
    public int $dilewati = 0;

    /**
     * @param int|null $prodiId Prodi tujuan (diisi dari halaman Import per Fakultas/Prodi).
     *                          Boleh null kalau suatu saat masih ada import tanpa prodi.
     */
    public function __construct(protected ?int $prodiId = null)
    {
    }

    /**
     * Heading di excel sumber (baris 2, baris 1 kosong, sesuai gambar contoh):
     * No | Nama Mahasiswa | NPM | Nomor kontak | Nomor kontak 2
     * Laravel Excel otomatis mengubah heading jadi snake_case:
     * no | nama_mahasiswa | npm | nomor_kontak | nomor_kontak_2
     * (kolom "Nomor kontak 2" bersifat opsional; boleh tidak ada di file Excel)
     */
    public function model(array $row): Model|array|null
    {
        $npm = trim((string) ($row['npm'] ?? ''));
        $nama = trim((string) ($row['nama_mahasiswa'] ?? ''));

        if ($npm === '' || $nama === '') {
            $this->dilewati++;

            return null;
        }

        // Validasi format NPM: harus berupa angka saja. Kalau tidak, baris
        // dilewati (tidak diimport) supaya data NPM tidak kotor.
        if (! preg_match('/^\d+$/', $npm)) {
            $this->dilewati++;

            return null;
        }

        // withTrashed: kalau NPM ini pernah dihapus (ada di Sampah), kita
        // pulihkan & timpa datanya, bukan bikin baris baru yang akan
        // ditolak database karena NPM harus unik.
        $existing = Mahasiswa::withTrashed()->where('npm', $npm)->first();

        $data = [
            'no' => $row['no'] ?? null,
            'nama_mahasiswa' => $nama,
            'npm' => $npm,
            'no_hp' => $this->sanitizePhone($row['nomor_kontak'] ?? null),
            'no_hp_2' => $this->sanitizePhone($row['nomor_kontak_2'] ?? null),
            'prodi_id' => $this->prodiId,
        ];

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->fill($data)->save();
            $this->updated++;

            return null; // sudah tersimpan manual, jangan dibuat duplikat oleh Laravel Excel
        }

        $this->imported++;

        return new Mahasiswa($data);
    }

    /**
     * Bersihkan nomor HP dari spasi/tanda baca aneh, sisakan angka
     * (dan tanda + di depan kalau ada, misal "+62..."). Tidak menolak
     * baris walau formatnya mencurigakan - cuma dirapikan.
     */
    private function sanitizePhone(mixed $raw): ?string
    {
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        $bersih = preg_replace('/[^\d+]/', '', (string) $raw);

        return $bersih === '' ? null : $bersih;
    }

    /**
     * Header di file excel sumber ada di baris ke-2 (baris 1 kosong),
     * jadi kita beri tahu Laravel Excel untuk membaca header dari baris 2.
     */
    public function headingRow(): int
    {
        return 2;
    }
}
