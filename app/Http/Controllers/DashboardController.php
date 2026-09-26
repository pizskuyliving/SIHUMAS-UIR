<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Mahasiswa;
use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Mahasiswa::query()
            ->with(['followUp.pic', 'followUp.statusFollowUp', 'followUp.rencanaWisuda'])
            ->orderBy('no');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_mahasiswa', 'like', "%{$search}%")
                  ->orWhere('npm', 'like', "%{$search}%");
            });
        }

        $mahasiswas = $query->paginate(20)->withQueryString();

        return view('dashboard.index', [
            'mahasiswas' => $mahasiswas,
            'statusOptions' => StatusFollowUp::orderBy('urutan')->get(),
            'rencanaOptions' => RencanaWisuda::orderBy('urutan')->get(),
        ]);
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
}
