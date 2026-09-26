<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Mahasiswa;
use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;

class StatistikController extends Controller
{
    public function index()
    {
        $totalMahasiswa = Mahasiswa::count();

        // Rekap Rencana Wisuda 2027
        $rencanaLabels = RencanaWisuda::orderBy('urutan')->pluck('nama', 'id');
        $rencanaCounts = FollowUp::selectRaw('rencana_wisuda_id, count(*) as total')
            ->whereNotNull('rencana_wisuda_id')
            ->groupBy('rencana_wisuda_id')
            ->pluck('total', 'rencana_wisuda_id');

        $rencanaData = $rencanaLabels->map(fn ($nama, $id) => $rencanaCounts[$id] ?? 0);
        $belumDiisiRencana = $totalMahasiswa - $rencanaData->sum();

        // Rekap Status Follow Up
        $statusLabels = StatusFollowUp::orderBy('urutan')->pluck('nama', 'id');
        $statusCounts = FollowUp::selectRaw('status_follow_up_id, count(*) as total')
            ->whereNotNull('status_follow_up_id')
            ->groupBy('status_follow_up_id')
            ->pluck('total', 'status_follow_up_id');

        $statusData = $statusLabels->map(fn ($nama, $id) => $statusCounts[$id] ?? 0);
        $belumDiisiStatus = $totalMahasiswa - $statusData->sum();

        return view('statistik.index', [
            'totalMahasiswa' => $totalMahasiswa,
            'rencanaLabels' => $rencanaLabels->values()->push('Belum Diisi'),
            'rencanaData' => $rencanaData->values()->push(max($belumDiisiRencana, 0)),
            'statusLabels' => $statusLabels->values()->push('Belum Diisi'),
            'statusData' => $statusData->values()->push(max($belumDiisiStatus, 0)),
        ]);
    }
}
