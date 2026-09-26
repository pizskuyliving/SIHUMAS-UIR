<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\FollowUp;
use App\Models\Mahasiswa;
use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        $fakultasId = $request->input('fakultas_id');
        $prodiId = $request->input('prodi_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // ==== Query dasar mahasiswa sesuai drill-down Fakultas/Prodi ====
        $mahasiswaQuery = Mahasiswa::query();

        if ($prodiId) {
            $mahasiswaQuery->where('prodi_id', $prodiId);
        } elseif ($fakultasId) {
            $mahasiswaQuery->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId));
        }

        if ($dateFrom) {
            $mahasiswaQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $mahasiswaQuery->whereDate('created_at', '<=', $dateTo);
        }

        $mahasiswaIds = $mahasiswaQuery->pluck('id');
        $totalMahasiswa = $mahasiswaIds->count();

        // ==== Rekap Rencana Wisuda & Status Follow Up (mengikuti filter di atas) ====
        $rencanaLabels = RencanaWisuda::orderBy('urutan')->pluck('nama', 'id');
        $rencanaCounts = FollowUp::whereIn('mahasiswa_id', $mahasiswaIds)
            ->selectRaw('rencana_wisuda_id, count(*) as total')
            ->whereNotNull('rencana_wisuda_id')
            ->groupBy('rencana_wisuda_id')
            ->pluck('total', 'rencana_wisuda_id');
        $rencanaData = $rencanaLabels->map(fn ($nama, $id) => $rencanaCounts[$id] ?? 0);
        $belumDiisiRencana = $totalMahasiswa - $rencanaData->sum();

        $statusLabels = StatusFollowUp::orderBy('urutan')->pluck('nama', 'id');
        $statusCounts = FollowUp::whereIn('mahasiswa_id', $mahasiswaIds)
            ->selectRaw('status_follow_up_id, count(*) as total')
            ->whereNotNull('status_follow_up_id')
            ->groupBy('status_follow_up_id')
            ->pluck('total', 'status_follow_up_id');
        $statusData = $statusLabels->map(fn ($nama, $id) => $statusCounts[$id] ?? 0);
        $belumDiisiStatus = $totalMahasiswa - $statusData->sum();

        $fakultasList = Fakultas::with('prodis')->orderBy('nama')->get();
        $fakultasJson = $fakultasList->map(function ($f) {
            return [
                'id' => $f->id,
                'nama' => $f->nama,
                'prodis' => $f->prodis->map(function ($p) {
                    return ['id' => $p->id, 'nama' => $p->nama];
                })->values(),
            ];
        })->values();

        return view('statistik.index', [
            'totalMahasiswa' => $totalMahasiswa,
            'rencanaLabels' => $rencanaLabels->values()->push('Belum Diisi'),
            'rencanaData' => $rencanaData->values()->push(max($belumDiisiRencana, 0)),
            'statusLabels' => $statusLabels->values()->push('Belum Diisi'),
            'statusData' => $statusData->values()->push(max($belumDiisiStatus, 0)),
            'fakultasJson' => $fakultasJson,
            'selectedFakultasId' => $fakultasId,
            'selectedProdiId' => $prodiId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
