<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\FollowUp;
use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KinerjaPicController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();

        // PIC hanya boleh melihat datanya sendiri; parameter pic_id dari
        // request diabaikan kalau bukan SuperAdmin, supaya tidak bisa
        // mengintip data PIC lain lewat URL.
        $picId = $isSuperAdmin ? $request->input('pic_id') : $user->id;

        $picList = $isSuperAdmin ? User::orderBy('name')->get(['id', 'name', 'role']) : collect();

        // ==== Leaderboard: total follow up per PIC (hanya untuk SuperAdmin, saat belum memilih PIC tertentu) ====
        $leaderboard = collect();
        if ($isSuperAdmin && ! $picId) {
            $leaderboard = FollowUp::selectRaw('pic_id, count(*) as total')
                ->whereNotNull('pic_id')
                ->groupBy('pic_id')
                ->with('pic:id,name')
                ->orderByDesc('total')
                ->get();
        }

        $detail = null;

        if ($picId) {
            $fakultasId = $request->input('fakultas_id');
            $prodiId = $request->input('prodi_id');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $baseQuery = FollowUp::where('pic_id', $picId)
                ->join('mahasiswas', 'mahasiswas.id', '=', 'follow_ups.mahasiswa_id');

            if ($prodiId) {
                $baseQuery->where('mahasiswas.prodi_id', $prodiId);
            } elseif ($fakultasId) {
                $baseQuery->whereIn('mahasiswas.prodi_id', function ($q) use ($fakultasId) {
                    $q->select('id')->from('prodis')->where('fakultas_id', $fakultasId);
                });
            }
            if ($dateFrom) {
                $baseQuery->whereDate('follow_ups.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $baseQuery->whereDate('follow_ups.created_at', '<=', $dateTo);
            }

            $totalFollowUp = (clone $baseQuery)->count();

            // Rekap Rencana Wisuda untuk PIC ini
            $rencanaLabels = RencanaWisuda::orderBy('urutan')->pluck('nama', 'id');
            $rencanaCounts = (clone $baseQuery)
                ->select('follow_ups.rencana_wisuda_id', DB::raw('count(*) as total'))
                ->whereNotNull('follow_ups.rencana_wisuda_id')
                ->groupBy('follow_ups.rencana_wisuda_id')
                ->pluck('total', 'follow_ups.rencana_wisuda_id');
            $rencanaData = $rencanaLabels->map(fn ($nama, $id) => $rencanaCounts[$id] ?? 0);

            // Rekap Status Follow Up untuk PIC ini
            $statusLabels = StatusFollowUp::orderBy('urutan')->pluck('nama', 'id');
            $statusCounts = (clone $baseQuery)
                ->select('follow_ups.status_follow_up_id', DB::raw('count(*) as total'))
                ->whereNotNull('follow_ups.status_follow_up_id')
                ->groupBy('follow_ups.status_follow_up_id')
                ->pluck('total', 'follow_ups.status_follow_up_id');
            $statusData = $statusLabels->map(fn ($nama, $id) => $statusCounts[$id] ?? 0);

            // Rekap asal data: dari Fakultas/Prodi mana saja mahasiswa yang di-follow up PIC ini
            $fakultasBreakdown = (clone $baseQuery)
                ->leftJoin('prodis', 'prodis.id', '=', 'mahasiswas.prodi_id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'prodis.fakultas_id')
                ->select(DB::raw("COALESCE(fakultas.nama, mahasiswas.fakultas, 'Belum Dikategorikan') as nama_fakultas"), DB::raw('count(*) as total'))
                ->groupBy('nama_fakultas')
                ->orderByDesc('total')
                ->pluck('total', 'nama_fakultas');

            $picUser = User::find($picId);

            $fakultasJsonForPicker = Fakultas::with('prodis')->orderBy('nama')->get()->map(function ($f) {
                return [
                    'id' => $f->id,
                    'nama' => $f->nama,
                    'prodis' => $f->prodis->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama])->values(),
                ];
            })->values();

            $detail = [
                'picUser' => $picUser,
                'totalFollowUp' => $totalFollowUp,
                'rencanaLabels' => $rencanaLabels->values(),
                'rencanaData' => $rencanaData->values(),
                'statusLabels' => $statusLabels->values(),
                'statusData' => $statusData->values(),
                'fakultasBreakdownLabels' => $fakultasBreakdown->keys()->values(),
                'fakultasBreakdownData' => $fakultasBreakdown->values(),
                'fakultasJson' => $fakultasJsonForPicker,
                'selectedFakultasId' => $fakultasId,
                'selectedProdiId' => $prodiId,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ];
        }

        return view('kinerja.index', [
            'isSuperAdmin' => $isSuperAdmin,
            'picList' => $picList,
            'selectedPicId' => $picId,
            'leaderboard' => $leaderboard,
            'detail' => $detail,
        ]);
    }
}
