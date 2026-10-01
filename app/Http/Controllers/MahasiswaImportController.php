<?php

namespace App\Http\Controllers;

use App\Imports\MahasiswaImport;
use App\Imports\MahasiswaPreviewImport;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use App\Models\ImportLog;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaImportController extends Controller
{
    private const TEMP_DIR = 'temp-imports';

    public function create()
    {
        $fakultasList = Fakultas::with('prodis')->orderBy('nama')->get();

        // Disiapkan sebagai array PHP polos di sini (bukan di view) supaya
        // @json() di Blade tinggal menerima satu variabel sederhana.
        $fakultasJson = $fakultasList->map(function ($f) {
            return [
                'id' => $f->id,
                'nama' => $f->nama,
                'prodis' => $f->prodis->map(function ($p) {
                    return ['id' => $p->id, 'nama' => $p->nama];
                })->values(),
            ];
        })->values();

        return view('mahasiswa.import', compact('fakultasList', 'fakultasJson'));
    }

    /**
     * Langkah 1: baca file Excel yang diupload, TANPA menyimpan apa pun ke
     * database. Tampilkan ringkasan (berapa baru/diperbarui/dilewati +
     * daftar barisnya) supaya user bisa cek dulu sebelum benar-benar import.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:prodis,id',
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->bersihkanFileTempLama();

        $prodi = Prodi::with('fakultas')->findOrFail($validated['prodi_id']);

        // File disimpan sementara (nama file asli ikut disimpan) supaya saat
        // user klik "Konfirmasi & Import" di langkah 2, kita tidak perlu
        // minta upload ulang - cukup baca file yang sudah tersimpan ini.
        $namaFileAsli = $request->file('file_excel')->getClientOriginalName();
        $namaTemp = uniqid('import_') . '.' . $request->file('file_excel')->getClientOriginalExtension();
        $request->file('file_excel')->storeAs(self::TEMP_DIR, $namaTemp, 'local');

        $preview = new MahasiswaPreviewImport();
        Excel::import($preview, self::TEMP_DIR . '/' . $namaTemp, 'local');

        return view('mahasiswa.import-preview', [
            'prodi' => $prodi,
            'namaFileAsli' => $namaFileAsli,
            'tempPath' => $namaTemp,
            'rows' => $preview->rows,
            'totalBaru' => $preview->totalBaru,
            'totalDiperbarui' => $preview->totalDiperbarui,
            'totalDilewati' => $preview->totalDilewati,
        ]);
    }

    /**
     * Langkah 2: user menekan "Konfirmasi & Import" di halaman preview ->
     * baru di sini data sungguhan masuk ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:prodis,id',
            'temp_path' => 'required|string',
            'nama_file_asli' => 'required|string',
        ]);

        $tempPath = self::TEMP_DIR . '/' . basename($validated['temp_path']);

        if (! Storage::disk('local')->exists($tempPath)) {
            return redirect()->route('mahasiswa.import')
                ->withErrors('File preview sudah kedaluwarsa (lebih dari 1 jam). Silakan upload ulang.');
        }

        $prodi = Prodi::with('fakultas')->findOrFail($validated['prodi_id']);

        $import = new MahasiswaImport((int) $validated['prodi_id']);
        Excel::import($import, $tempPath, 'local');

        Storage::disk('local')->delete($tempPath);

        ImportLog::create([
            'user_id' => $request->user()->id,
            'prodi_id' => $prodi->id,
            'nama_file' => $validated['nama_file_asli'],
            'total_baru' => $import->imported,
            'total_diperbarui' => $import->updated,
            'total_dilewati' => $import->dilewati,
            'created_at' => now(),
        ]);

        ActivityLog::catat(
            'import_excel',
            "Import Excel \"{$validated['nama_file_asli']}\" ke {$prodi->fakultas->nama} - {$prodi->nama}: "
            . "{$import->imported} baru, {$import->updated} diperbarui, {$import->dilewati} dilewati."
        );

        return redirect()->route('mahasiswa.import')->with(
            'status',
            "Import untuk {$prodi->fakultas->nama} - {$prodi->nama} selesai. "
            . "Data baru: {$import->imported}, diperbarui: {$import->updated}, dilewati: {$import->dilewati}."
        );
    }

    /**
     * Bersihkan file temp preview yang sudah lebih dari 1 jam (ditinggal
     * begitu saja karena user tidak jadi konfirmasi import). Dipanggil
     * setiap ada preview baru - tidak perlu setup cron terpisah.
     */
    private function bersihkanFileTempLama(): void
    {
        $disk = Storage::disk('local');

        if (! $disk->exists(self::TEMP_DIR)) {
            return;
        }

        foreach ($disk->files(self::TEMP_DIR) as $file) {
            if (now()->subHour()->timestamp > $disk->lastModified($file)) {
                $disk->delete($file);
            }
        }
    }
}
