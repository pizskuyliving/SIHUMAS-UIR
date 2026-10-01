<?php

namespace App\Http\Controllers;

use App\Exports\MahasiswaExport;
use App\Models\Fakultas;
use App\Models\FollowUp;
use App\Models\Mahasiswa;
use App\Models\Pertimbangan;
use App\Models\Prodi;
use App\Models\StatusFollowUp;
use App\Models\RencanaWisuda;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    private const EAGER = ['followUp.pic', 'followUp.statusFollowUp', 'followUp.rencanaWisuda', 'followUp.pertimbangan', 'prodi.fakultas'];

    /**
     * Halaman awal Data & Follow Up: pilih Fakultas dulu, lalu Prodi.
     */
    public function index()
    {
        $fakultasList = Fakultas::withCount('prodis')
            ->with(['prodis' => function ($q) {
                $q->withCount('mahasiswas')->orderBy('nama');
            }])
            ->orderBy('nama')
            ->get();

        $jumlahTanpaProdi = Mahasiswa::whereNull('prodi_id')->count();

        return view('dashboard.pilih-fakultas', compact('fakultasList', 'jumlahTanpaProdi'));
    }

    /**
     * Tabel Data & Follow Up untuk satu Prodi tertentu.
     */
    public function prodi(Request $request, Prodi $prodi)
    {
        $prodi->load('fakultas');

        $query = Mahasiswa::where('prodi_id', $prodi->id)
            ->with(self::EAGER)
            ->orderBy('no');

        return $this->renderTabel($request, $query, [
            'title' => $prodi->fakultas->nama . ' - ' . $prodi->nama,
            'backUrl' => route('dashboard'),
            'deleteAllUrl' => route('mahasiswa.destroy-all-prodi', $prodi),
            'deleteAllTotal' => Mahasiswa::where('prodi_id', $prodi->id)->count(),
            'exportUrl' => route('dashboard.prodi.export', $prodi),
        ]);
    }

    /**
     * Tabel untuk data lama yang belum dikategorikan Fakultas/Prodi
     * (diimport sebelum fitur Fakultas & Prodi ada).
     */
    public function legacy(Request $request)
    {
        $query = Mahasiswa::whereNull('prodi_id')
            ->with(self::EAGER)
            ->orderBy('no');

        return $this->renderTabel($request, $query, [
            'title' => 'Data Belum Dikategorikan (Fakultas/Prodi lama)',
            'backUrl' => route('dashboard'),
            'deleteAllUrl' => route('mahasiswa.destroy-all-legacy'),
            'deleteAllTotal' => Mahasiswa::whereNull('prodi_id')->count(),
            'exportUrl' => route('dashboard.legacy.export'),
        ]);
    }

    /**
     * Unduh Excel berisi SELURUH data di 1 Prodi (bukan cuma halaman yang
     * sedang tampil), dengan format kolom sesuai sistem.
     */
    public function exportProdi(Prodi $prodi)
    {
        $prodi->load('fakultas');

        $mahasiswas = Mahasiswa::where('prodi_id', $prodi->id)
            ->with(self::EAGER)
            ->orderBy('no')
            ->get();

        $namaFile = 'sihumas-' . str($prodi->fakultas->nama . '-' . $prodi->nama)->slug() . '.xlsx';

        return Excel::download(new MahasiswaExport($mahasiswas), $namaFile);
    }

    /**
     * Unduh Excel untuk data lama yang belum dikategorikan Fakultas/Prodi.
     */
    public function exportLegacy()
    {
        $mahasiswas = Mahasiswa::whereNull('prodi_id')
            ->with(self::EAGER)
            ->orderBy('no')
            ->get();

        return Excel::download(new MahasiswaExport($mahasiswas), 'sihumas-data-belum-dikategorikan.xlsx');
    }

    private function renderTabel(Request $request, $query, array $meta)
    {
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_mahasiswa', 'like', "%{$search}%")
                  ->orWhere('npm', 'like', "%{$search}%");
            });
        }

        $mahasiswas = $query->paginate(20)->withQueryString();

        return view('dashboard.table', array_merge($meta, [
            'mahasiswas' => $mahasiswas,
            'statusOptions' => StatusFollowUp::orderBy('urutan')->get(),
            'rencanaOptions' => RencanaWisuda::orderBy('urutan')->get(),
            'pertimbanganOptions' => Pertimbangan::orderBy('urutan')->get(),
        ]));
    }

    /**
     * Simpan / update follow up untuk 1 baris mahasiswa.
     * PIC yang login otomatis tercatat sebagai PIC Telemarketing baris ini.
     * (No. HP 1 & 2 TIDAK diisi lewat form ini - keduanya diisi dari Excel
     * saat import, sama seperti data mahasiswa lain.)
     */
    public function updateFollowUp(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'status_follow_up_id' => 'nullable|exists:status_follow_ups,id',
            'rencana_wisuda_id' => 'nullable|exists:rencana_wisudas,id',
            'pertimbangan_id' => 'nullable|exists:pertimbangans,id',
            'follow_up_berikutnya' => 'nullable|string', // ditampilkan sebagai "Catatan"
            'fakultas' => 'nullable|string|max:255',
        ]);

        if (array_key_exists('fakultas', $validated)) {
            $mahasiswa->update(['fakultas' => $validated['fakultas']]);
        }

        FollowUp::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            [
                'pic_id' => $request->user()->id,
                'status_follow_up_id' => $validated['status_follow_up_id'] ?? null,
                'rencana_wisuda_id' => $validated['rencana_wisuda_id'] ?? null,
                'pertimbangan_id' => $validated['pertimbangan_id'] ?? null,
                'follow_up_berikutnya' => $validated['follow_up_berikutnya'] ?? null,
            ]
        );

        return back()->with('status', 'Data follow up berhasil disimpan.');
    }

    /**
     * Hapus data mahasiswa (beserta follow up-nya, lewat cascade delete).
     * Boleh dilakukan PIC maupun SuperAdmin.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $nama = $mahasiswa->nama_mahasiswa;
        $mahasiswa->delete();

        return back()->with('status', "Data \"{$nama}\" berhasil dihapus.");
    }

    /**
     * Hapus banyak data mahasiswa sekaligus (dipilih lewat checkbox).
     * Boleh dilakukan PIC maupun SuperAdmin.
     */
    public function destroyBulk(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_ids' => 'required|array|min:1',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
        ]);

        $jumlah = Mahasiswa::whereIn('id', $validated['mahasiswa_ids'])->delete();

        return back()->with('status', "{$jumlah} data berhasil dihapus.");
    }

    /**
     * Hapus SEMUA data mahasiswa dalam 1 Prodi sekaligus (lintas halaman,
     * bukan cuma 20 baris yang sedang tampil). Khusus SuperAdmin.
     */
    public function destroyAllProdi(Prodi $prodi)
    {
        $jumlah = Mahasiswa::where('prodi_id', $prodi->id)->delete();

        return redirect()->route('dashboard.prodi', $prodi)
            ->with('status', "{$jumlah} data di prodi ini berhasil dihapus semua.");
    }

    /**
     * Hapus SEMUA data mahasiswa yang belum dikategorikan Fakultas/Prodi
     * (data lama) sekaligus. Khusus SuperAdmin.
     */
    public function destroyAllLegacy()
    {
        $jumlah = Mahasiswa::whereNull('prodi_id')->delete();

        return redirect()->route('dashboard.legacy')
            ->with('status', "{$jumlah} data lama berhasil dihapus semua.");
    }
}
