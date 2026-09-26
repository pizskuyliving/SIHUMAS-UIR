<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\FollowUp;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\StatusFollowUp;
use App\Models\RencanaWisuda;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
            ->with(['followUp.pic', 'followUp.statusFollowUp', 'followUp.rencanaWisuda'])
            ->orderBy('no');

        return $this->renderTabel($request, $query, [
            'title' => $prodi->fakultas->nama . ' - ' . $prodi->nama,
            'backUrl' => route('dashboard'),
        ]);
    }

    /**
     * Tabel untuk data lama yang belum dikategorikan Fakultas/Prodi
     * (diimport sebelum fitur Fakultas & Prodi ada).
     */
    public function legacy(Request $request)
    {
        $query = Mahasiswa::whereNull('prodi_id')
            ->with(['followUp.pic', 'followUp.statusFollowUp', 'followUp.rencanaWisuda'])
            ->orderBy('no');

        return $this->renderTabel($request, $query, [
            'title' => 'Data Belum Dikategorikan (Fakultas/Prodi lama)',
            'backUrl' => route('dashboard'),
        ]);
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
        ]));
    }

    /**
     * Simpan / update follow up untuk 1 baris mahasiswa.
     * PIC yang login otomatis tercatat sebagai PIC Telemarketing baris ini.
     */
    public function updateFollowUp(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'status_follow_up_id' => 'nullable|exists:status_follow_ups,id',
            'rencana_wisuda_id' => 'nullable|exists:rencana_wisudas,id',
            'keterangan' => 'nullable|string',
            'follow_up_berikutnya' => 'nullable|string',
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
                'keterangan' => $validated['keterangan'] ?? null,
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
}
