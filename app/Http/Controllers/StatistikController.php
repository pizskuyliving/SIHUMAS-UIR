<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\FollowUp;
use App\Models\Mahasiswa;
use App\Models\Pertimbangan;
use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        // ==== 3 grafik terpisah: Rencana Wisuda, Pertimbangan, Status Follow Up ====
        $rencana = $this->rekap(RencanaWisuda::class, 'rencana_wisuda_id', $mahasiswaIds, $totalMahasiswa);
        $pertimbangan = $this->rekap(Pertimbangan::class, 'pertimbangan_id', $mahasiswaIds, $totalMahasiswa);
        $status = $this->rekap(StatusFollowUp::class, 'status_follow_up_id', $mahasiswaIds, $totalMahasiswa);

        // ==== 1) Progress kelengkapan data ====
        // "Lengkap" = follow up sudah punya Status, Rencana Wisuda, DAN Pertimbangan sekaligus.
        $totalLengkap = FollowUp::whereIn('mahasiswa_id', $mahasiswaIds)
            ->whereNotNull('status_follow_up_id')
            ->whereNotNull('rencana_wisuda_id')
            ->whereNotNull('pertimbangan_id')
            ->count();
        $completionPercent = $totalMahasiswa > 0 ? round($totalLengkap / $totalMahasiswa * 100, 1) : 0;

        // ==== 2) Funnel / cross-tab: Status Follow Up -> Rencana Wisuda ====
        $funnel = $this->funnelStatusKeRencana($mahasiswaIds);

        // ==== 5) Tren follow up masuk, 8 minggu terakhir ====
        $tren = $this->trenMingguan($mahasiswaIds);

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
            'fakultasJson' => $fakultasJson,
            'selectedFakultasId' => $fakultasId,
            'selectedProdiId' => $prodiId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Hitung rekap 1 kolom dropdown (Rencana Wisuda / Pertimbangan / Status
     * Follow Up) untuk sekumpulan mahasiswa, plus kategori "Belum Diisi"
     * supaya total selalu tepat 100% dari $totalMahasiswa.
     */
    private function rekap(string $masterModel, string $kolomId, $mahasiswaIds, int $totalMahasiswa): array
    {
        $labels = $masterModel::orderBy('urutan')->pluck('nama', 'id');
        $counts = FollowUp::whereIn('mahasiswa_id', $mahasiswaIds)
            ->selectRaw("{$kolomId}, count(*) as total")
            ->whereNotNull($kolomId)
            ->groupBy($kolomId)
            ->pluck('total', $kolomId);

        $data = $labels->map(fn ($nama, $id) => (int) ($counts[$id] ?? 0));
        $belumDiisi = max($totalMahasiswa - $data->sum(), 0);

        return [
            'labels' => $labels->values()->push('Belum Diisi'),
            'data' => $data->values()->push($belumDiisi),
        ];
    }

    /**
     * Cross-tab: untuk tiap Status Follow Up, bagaimana sebaran Rencana
     * Wisuda-nya. Menjawab pertanyaan "dari yang SUDAH dihubungi, berapa
     * persen yang bilang Ya ikut wisuda?" - bukan dari total keseluruhan.
     */
    private function funnelStatusKeRencana($mahasiswaIds): array
    {
        $statusLabels = StatusFollowUp::orderBy('urutan')->pluck('nama', 'id');
        $rencanaLabels = RencanaWisuda::orderBy('urutan')->pluck('nama', 'id');

        $raw = FollowUp::whereIn('mahasiswa_id', $mahasiswaIds)
            ->whereNotNull('status_follow_up_id')
            ->selectRaw('status_follow_up_id, rencana_wisuda_id, count(*) as total')
            ->groupBy('status_follow_up_id', 'rencana_wisuda_id')
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
     * Tren jumlah data follow up per minggu, 8 minggu terakhir (tetap,
     * tanpa perlu pilih rentang - biar simpel).
     */
    private function trenMingguan($mahasiswaIds): array
    {
        $now = Carbon::now();
        $minggu = collect(range(7, 0))->map(fn ($i) => $now->copy()->subWeeks($i)->startOfWeek());

        $labels = $minggu->map(fn ($w) => $w->format('d M'));
        $data = $minggu->map(function ($mulai) use ($mahasiswaIds) {
            $selesai = $mulai->copy()->endOfWeek();

            return FollowUp::whereIn('mahasiswa_id', $mahasiswaIds)
                ->whereBetween('created_at', [$mulai, $selesai])
                ->count();
        });

        return ['labels' => $labels->values(), 'data' => $data->values()];
    }
}
