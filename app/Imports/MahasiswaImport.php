<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

class MahasiswaImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;

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

        if ($npm === '') {
            return null;
        }

        $existing = Mahasiswa::where('npm', $npm)->first();

        $data = [
            'no' => $row['no'] ?? null,
            'nama_mahasiswa' => trim((string) ($row['nama_mahasiswa'] ?? '')),
            'npm' => $npm,
            'no_hp' => isset($row['nomor_kontak']) ? (string) $row['nomor_kontak'] : null,
            'no_hp_2' => isset($row['nomor_kontak_2']) ? (string) $row['nomor_kontak_2'] : null,
            'prodi_id' => $this->prodiId,
        ];

        if ($existing) {
            $existing->fill($data)->save();
            $this->updated++;

            return null; // sudah tersimpan manual, jangan dibuat duplikat oleh Laravel Excel
        }

        $this->imported++;

        return new Mahasiswa($data);
    }

    /**
     * Header di file excel sumber ada di baris ke-2 (baris 1 kosong),
     * jadi kita beri tahu Laravel Excel untuk membaca header dari baris 2.
     */
    public function headingRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            'nama_mahasiswa' => 'required',
            'npm' => 'required',
        ];
    }
}
