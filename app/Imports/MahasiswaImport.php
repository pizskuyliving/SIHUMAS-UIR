<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Import sungguhan (menyimpan ke database). Memakai deteksi kolom otomatis
 * (lihat KolomMapper) - file Excel "mentah" dari sumber manapun bisa
 * langsung dibaca, tidak harus ikut format baku (header baris 2, urutan
 * kolom tertentu, nama kolom persis sama).
 */
class MahasiswaImport implements ToArray
{
    use KolomMapper;

    public int $imported = 0;
    public int $updated = 0;
    public int $dilewati = 0;
    public ?string $pesanGagalDeteksi = null;

    /**
     * @param int|null $prodiId Prodi tujuan (diisi dari halaman Import per Fakultas/Prodi).
     */
    public function __construct(protected ?int $prodiId = null)
    {
    }

    public function array(array $array): void
    {
        $deteksi = $this->deteksiHeaderDanPemetaan($array);

        if (! $deteksi) {
            $this->pesanGagalDeteksi = 'Tidak ditemukan kolom NPM dan Nama Mahasiswa di file ini.';

            return;
        }

        $pemetaan = $deteksi['pemetaan'];
        $dataRows = array_slice($array, $deteksi['baris_index'] + 1);

        foreach ($dataRows as $row) {
            $npm = trim((string) ($row[$pemetaan['npm']['index']] ?? ''));
            $nama = trim((string) ($row[$pemetaan['nama_mahasiswa']['index']] ?? ''));

            if ($npm === '' && $nama === '') {
                continue; // baris kosong total
            }

            if ($npm === '' || $nama === '' || ! preg_match('/^\d+$/', $npm)) {
                $this->dilewati++;
                continue;
            }

            $noHp = isset($pemetaan['no_hp']) ? $this->sanitizePhone($row[$pemetaan['no_hp']['index']] ?? null) : null;
            $noHp2 = isset($pemetaan['no_hp_2']) ? $this->sanitizePhone($row[$pemetaan['no_hp_2']['index']] ?? null) : null;
            $noAsli = isset($pemetaan['no']) ? ($row[$pemetaan['no']['index']] ?? null) : null;

            // withTrashed: kalau NPM ini pernah dihapus (ada di Sampah), kita
            // pulihkan & timpa datanya, bukan bikin baris baru yang akan
            // ditolak database karena NPM harus unik.
            $existing = Mahasiswa::withTrashed()->where('npm', $npm)->first();

            $data = [
                'no' => $noAsli,
                'nama_mahasiswa' => $nama,
                'npm' => $npm,
                'no_hp' => $noHp,
                'no_hp_2' => $noHp2,
                'prodi_id' => $this->prodiId,
            ];

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->fill($data)->save();
                $this->updated++;

                continue;
            }

            Mahasiswa::create($data);
            $this->imported++;
        }
    }
}
