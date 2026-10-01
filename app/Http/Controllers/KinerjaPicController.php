<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\FollowUp;
use App\Models\Pertimbangan;
use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
                ->with('pic:id,name,photo_path')
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

            // 3 grafik terpisah: Rencana Wisuda, Pertimbangan, Status Follow Up
            $rencana = $this->rekap($baseQuery, RencanaWisuda::class, 'rencana_wisuda_id', $totalFollowUp);
            $pertimbangan = $this->rekap($baseQuery, Pertimbangan::class, 'pertimbangan_id', $totalFollowUp);
            $status = $this->rekap($baseQuery, StatusFollowUp::class, 'status_follow_up_id', $totalFollowUp);

            // 1) Progress kelengkapan data milik PIC ini
            $totalLengkap = (clone $baseQuery)
                ->whereNotNull('follow_ups.status_follow_up_id')
                ->whereNotNull('follow_ups.rencana_wisuda_id')
                ->whereNotNull('follow_ups.pertimbangan_id')
                ->count();
            $completionPercent = $totalFollowUp > 0 ? round($totalLengkap / $totalFollowUp * 100, 1) : 0;

            // 2) Funnel / cross-tab Status Follow Up -> Rencana Wisuda, khusus PIC ini
            $funnel = $this->funnelStatusKeRencana($baseQuery);

            // 5) Tren follow up masuk, 8 minggu terakhir, khusus PIC ini
            $tren = $this->trenMingguan($baseQuery);

            // 4) Daftar "Perlu Ditindaklanjuti": follow up yang belum lengkap ketiga kolomnya,
            //    diurutkan dari yang paling lama tidak disentuh (paling mendesak).
            $perluDitindaklanjuti = (clone $baseQuery)
                ->where(function ($q) {
                    $q->whereNull('follow_ups.status_follow_up_id')
                      ->orWhereNull('follow_ups.rencana_wisuda_id')
                      ->orWhereNull('follow_ups.pertimbangan_id');
                })
                ->orderBy('follow_ups.updated_at')
                ->limit(10)
                ->get(['mahasiswas.id as mahasiswa_id', 'mahasiswas.nama_mahasiswa', 'mahasiswas.npm', 'mahasiswas.prodi_id', 'follow_ups.updated_at']);

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
                'completionPercent' => $completionPercent,
                'totalLengkap' => $totalLengkap,
                'rencanaLabels' => $rencana['labels'],
                'rencanaData' => $rencana['data'],
                'pertimbanganLabels' => $pertimbangan['labels'],
                'pertimbanganData' => $pertimbangan['data'],
                'statusLabels' => $status['labels'],
                'statusData' => $status['data'],
                'funnel' => $funnel,
                'trenLabels' => $tren['labels'],
                'trenData' => $tren['data'],
                'perluDitindaklanjuti' => $perluDitindaklanjuti,
                'fakultasBreakdownLabels' => $fakultasBreakdown->keys()->values(),
                'fakultasBreakdownData' => $fakultasBreakdown->values()->map(fn ($v) => (int) $v),
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

    /**
     * Hitung rekap 1 kolom dropdown (Rencana Wisuda / Pertimbangan / Status
     * Follow Up) untuk follow up milik 1 PIC, plus kategori "Belum Diisi"
     * supaya total selalu tepat 100% dari total follow up PIC tsb.
     */
    private function rekap($baseQuery, string $masterModel, string $kolomId, int $totalFollowUp): array
    {
        $labels = $masterModel::orderBy('urutan')->pluck('nama', 'id');
        $counts = (clone $baseQuery)
            ->select("follow_ups.{$kolomId}", DB::raw('count(*) as total'))
            ->whereNotNull("follow_ups.{$kolomId}")
            ->groupBy("follow_ups.{$kolomId}")
            ->pluck('total', "follow_ups.{$kolomId}");

        $data = $labels->map(fn ($nama, $id) => (int) ($counts[$id] ?? 0));
        $belumDiisi = max($totalFollowUp - $data->sum(), 0);

        return [
            'labels' => $labels->values()->push('Belum Diisi'),
            'data' => $data->values()->push($belumDiisi),
        ];
    }

    /**
     * Cross-tab: untuk tiap Status Follow Up, bagaimana sebaran Rencana
     * Wisuda-nya, khusus follow up milik 1 PIC.
     */
    private function funnelStatusKeRencana($baseQuery): array
    {
        $statusLabels = StatusFollowUp::orderBy('urutan')->pluck('nama', 'id');
        $rencanaLabels = RencanaWisuda::orderBy('urutan')->pluck('nama', 'id');

        $raw = (clone $baseQuery)
            ->whereNotNull('follow_ups.status_follow_up_id')
            ->select('follow_ups.status_follow_up_id', 'follow_ups.rencana_wisuda_id', DB::raw('count(*) as total'))
            ->groupBy('follow_ups.status_follow_up_id', 'follow_ups.rencana_wisuda_id')
            ->get();

        $kolomLabels = $rencanaLabels->values()->push('Belum Diisi');

        $baris = $statusLabels->map(function ($namaStatus, $statusId) use ($raw, $rencanaLabels) {
            $rowRaw = $raw->where('status_follow_up_id', $statusId);
            $rowTotal = $rowRaw->sum('total');

            $sel = $rencanaLabels->keys()->mapWithKeys(function ($rencanaId) use ($rowRaw) {
                return [$rencanaId => (int) $rowRaw->where('rencana_wisuda_id', $rencanaId)->sum('total')];
            });
            $belumDiisi = max($rowTotal - $sel->sum(), 0);

            return [
                'nama' => $namaStatus,
                'total' => $rowTotal,
                'sel' => $sel->values()->push($belumDiisi)->map(function ($jumlah) use ($rowTotal) {
                    return [
                        'jumlah' => $jumlah,
                        'persen' => $rowTotal > 0 ? round($jumlah / $rowTotal * 100, 1) : 0,
                    ];
                }),
            ];
        })->values();

        return ['kolomLabels' => $kolomLabels, 'baris' => $baris];
    }

    /**
     * Tren jumlah follow up 1 PIC per minggu, 8 minggu terakhir.
     */
    private function trenMingguan($baseQuery): array
    {
        $now = Carbon::now();
        $minggu = collect(range(7, 0))->map(fn ($i) => $now->copy()->subWeeks($i)->startOfWeek());

        $labels = $minggu->map(fn ($w) => $w->format('d M'));
        $data = $minggu->map(function ($mulai) use ($baseQuery) {
            $selesai = $mulai->copy()->endOfWeek();

            return (clone $baseQuery)
                ->whereBetween('follow_ups.created_at', [$mulai, $selesai])
                ->count();
        });

        return ['labels' => $labels->values(), 'data' => $data->values()];
    }
}
