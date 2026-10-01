<x-app-layout :title="$isSuperAdmin ? 'Kinerja PIC' : 'Kinerja Saya'">

    <div class="flex items-center justify-end mb-4 no-print">
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 neu-btn text-sm px-4 py-2">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    @if ($isSuperAdmin)
        {{-- SuperAdmin: pilih PIC mana yang mau dilihat --}}
        <div class="neu-card p-4 sm:p-5 mb-6 no-print">
            <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium mb-1 text-gray-600">Pilih PIC</label>
                    <select name="pic_id" onchange="this.form.submit()" class="w-full sm:w-80 neu-input text-sm">
                        <option value="">- Lihat leaderboard semua PIC -</option>
                        @foreach ($picList as $p)
                            <option value="{{ $p->id }}" @selected((string) $selectedPicId === (string) $p->id)>
                                {{ $p->name }} ({{ $p->role === 'superadmin' ? 'SuperAdmin' : 'PIC' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    @endif

    {{-- ==================== LEADERBOARD (SuperAdmin, belum pilih PIC) ==================== --}}
    @if ($isSuperAdmin && ! $selectedPicId)
        {{-- 3) Perbandingan visual antar PIC --}}
        @if ($leaderboard->count() > 1)
            <div class="neu-card p-4 sm:p-6 mb-6">
                <h2 class="font-semibold mb-4">Perbandingan Jumlah Follow Up antar PIC</h2>
                <div class="relative" style="height: {{ max(160, $leaderboard->count() * 48 + 60) }}px">
                    <canvas id="chartLeaderboard"></canvas>
                </div>
            </div>
            <script>
                window.__leaderboardLabels = @json($leaderboard->map(fn ($r) => $r->pic?->name ?? '(Akun dihapus)'));
                window.__leaderboardData = @json($leaderboard->pluck('total'));
            </script>
        @endif

        <div class="neu-card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Peringkat</th>
                        <th class="px-4 py-3 text-left">Nama PIC</th>
                        <th class="px-4 py-3 text-left">Total Follow Up</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($leaderboard as $i => $row)
                        <tr>
                            <td class="px-4 py-3">#{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-avatar :user="$row->pic" class="w-8 h-8 text-xs" />
                                    <span class="font-medium">{{ $row->pic?->name ?? '(Akun dihapus)' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $row->total }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('kinerja.index', ['pic_id' => $row->pic_id]) }}"
                                   class="text-xs underline text-emerald-700 whitespace-nowrap">Lihat Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                Belum ada data follow up dari PIC manapun.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <script>
            window.addEventListener('load', function () {
                if (!window.__leaderboardLabels) return;
                new Chart(document.getElementById('chartLeaderboard'), {
                    type: 'bar',
                    data: {
                        labels: window.wrapLabels(window.__leaderboardLabels, 20),
                        datasets: [{
                            label: 'Total Follow Up',
                            data: window.__leaderboardData,
                            backgroundColor: '#1E8C86',
                            borderRadius: 6,
                            maxBarThickness: 32,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                            y: { grid: { display: false } },
                        },
                        plugins: {
                            legend: { display: false },
                            datalabels: { anchor: 'end', align: 'right', color: '#1E8C86', font: { weight: 'bold' } },
                        },
                    },
                });
            });
        </script>
    @endif

    {{-- ==================== DETAIL KINERJA 1 PIC ==================== --}}
    @if ($detail)
        <script>
            window.__fakultasList = @json($detail['fakultasJson']);
        </script>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="flex items-center gap-3 min-w-0">
                <x-avatar :user="$detail['picUser']" class="w-12 h-12 text-lg" />
                <div class="min-w-0">
                    <p class="text-sm text-gray-500">Menampilkan kinerja:</p>
                    <p class="font-semibold text-lg break-words">{{ $detail['picUser']?->name ?? '-' }}</p>
                </div>
            </div>
            @if ($isSuperAdmin)
                <a href="{{ route('kinerja.index') }}" class="text-sm text-emerald-700 underline whitespace-nowrap no-print">&larr; Kembali ke leaderboard</a>
            @endif
        </div>

        <div x-data="fakultasPicker('{{ $detail['selectedFakultasId'] }}')" class="neu-card p-4 sm:p-5 mb-6 no-print">
            <form method="GET" class="grid sm:grid-cols-2 xl:grid-cols-5 gap-3 items-end">
                @if ($isSuperAdmin)
                    <input type="hidden" name="pic_id" value="{{ $selectedPicId }}">
                @endif
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Fakultas</label>
                    <select name="fakultas_id" x-model="selectedFakultasId" class="w-full neu-input text-sm">
                        <option value="">Semua Fakultas</option>
                        <template x-for="f in fakultasList" :key="f.id">
                            <option :value="f.id" x-text="f.nama"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Prodi</label>
                    <select name="prodi_id" class="w-full neu-input text-sm">
                        <option value="">Semua Prodi</option>
                        <template x-for="p in prodiOptions" :key="p.id">
                            <option :value="p.id" :selected="p.id == '{{ $detail['selectedProdiId'] }}'" x-text="p.nama"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $detail['dateFrom'] }}" class="w-full neu-input text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $detail['dateTo'] }}" class="w-full neu-input text-sm">
                </div>
                <div>
                    <button class="neu-btn-primary text-sm px-5 py-2 w-full">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        @if ($detail['totalFollowUp'] > 0)
            <p class="text-sm text-gray-500 mb-6">Total mahasiswa yang di-follow up (sesuai filter): <strong>{{ $detail['totalFollowUp'] }}</strong></p>

            {{-- 1) Progress kelengkapan data milik PIC ini --}}
            <x-completion-bar :percent="$detail['completionPercent']"
                :sublabel="\"{$detail['totalLengkap']} dari {$detail['totalFollowUp']} follow up sudah lengkap ketiga kolomnya (Status, Rencana Wisuda, Pertimbangan)\"" />

            {{-- 5) Tren follow up PIC ini, 8 minggu terakhir --}}
            <div class="neu-card p-4 sm:p-6 mb-6 print-avoid-break">
                <h2 class="font-semibold mb-4">Tren Follow Up (8 Minggu Terakhir)</h2>
                <div class="relative h-56 sm:h-64"><canvas id="chartTren"></canvas></div>
            </div>

            <div class="neu-card p-4 sm:p-6 mb-6 min-w-0 print-avoid-break">
                <h2 class="font-semibold mb-4">Asal Data yang Di-follow Up (per Fakultas)</h2>
                {{-- Tinggi container diatur ulang lewat JS sesuai jumlah fakultas --}}
                <div class="relative" style="height: 240px"><canvas id="chartFakultasBreakdown"></canvas></div>
            </div>

            <div class="grid lg:grid-cols-3 gap-4 md:gap-6 mb-6">
                <div class="neu-card p-4 sm:p-6 min-w-0 print-avoid-break">
                    <h2 class="font-semibold mb-4">Rencana Wisuda Januari 2027</h2>
                    <div class="relative h-72 sm:h-80"><canvas id="chartRencana"></canvas></div>
                </div>
                <div class="neu-card p-4 sm:p-6 min-w-0 print-avoid-break">
                    <h2 class="font-semibold mb-4">Pertimbangan</h2>
                    <div class="relative h-72 sm:h-80"><canvas id="chartPertimbangan"></canvas></div>
                </div>
                <div class="neu-card p-4 sm:p-6 min-w-0 print-avoid-break">
                    <h2 class="font-semibold mb-4">Status Follow Up</h2>
                    <div class="relative h-72 sm:h-80"><canvas id="chartStatus"></canvas></div>
                </div>
            </div>

            {{-- 2) Funnel / cross-tab Status Follow Up -> Rencana Wisuda --}}
            <x-funnel-table :funnel="$detail['funnel']" />

            {{-- 4) Daftar perlu ditindaklanjuti --}}
            <div class="neu-card p-4 sm:p-6 mb-6 overflow-x-auto">
                <h2 class="font-semibold mb-1">Perlu Ditindaklanjuti</h2>
                <p class="text-xs text-gray-400 mb-4">
                    10 mahasiswa yang follow up-nya belum lengkap (Status/Rencana Wisuda/Pertimbangan masih ada
                    yang kosong), diurutkan dari yang paling lama tidak disentuh.
                </p>

                @if ($detail['perluDitindaklanjuti']->isEmpty())
                    <p class="text-sm text-emerald-700">🎉 Semua follow up sudah lengkap untuk filter ini.</p>
                @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left">Nama</th>
                                <th class="px-3 py-2 text-left">NPM</th>
                                <th class="px-3 py-2 text-left">Terakhir Disentuh</th>
                                <th class="px-3 py-2 text-left no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($detail['perluDitindaklanjuti'] as $item)
                                <tr>
                                    <td class="px-3 py-2 font-medium">{{ $item->nama_mahasiswa }}</td>
                                    <td class="px-3 py-2">{{ $item->npm }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $item->updated_at?->diffForHumans() }}</td>
                                    <td class="px-3 py-2 no-print">
                                        <a href="{{ $item->prodi_id ? route('dashboard.prodi', $item->prodi_id) : route('dashboard.legacy') }}?q={{ urlencode($item->npm) }}"
                                           class="text-xs underline text-emerald-700 whitespace-nowrap">Buka & Follow Up</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <script>
                window.addEventListener('load', function () {
                    const isMobile = window.innerWidth < 640;
                    const fakultasLabels = @json($detail['fakultasBreakdownLabels']);
                    const fakultasData = @json($detail['fakultasBreakdownData']);

                    new Chart(document.getElementById('chartTren'), {
                        type: 'line',
                        data: {
                            labels: @json($detail['trenLabels']),
                            datasets: [{
                                label: 'Follow Up',
                                data: @json($detail['trenData']),
                                borderColor: '#1E8C86',
                                backgroundColor: 'rgba(43,168,162,0.15)',
                                tension: 0.3,
                                fill: true,
                                pointBackgroundColor: '#1E8C86',
                            }],
                        },
                        options: {
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                            plugins: { legend: { display: false }, datalabels: { display: false } },
                        },
                    });

                    // Tinggi grafik horizontal menyesuaikan jumlah fakultas (dinamis)
                    const breakdownCanvas = document.getElementById('chartFakultasBreakdown');
                    breakdownCanvas.parentElement.style.height =
                        Math.max(200, fakultasLabels.length * (isMobile ? 64 : 52) + 70) + 'px';

                    new Chart(breakdownCanvas, {
                        type: 'bar',
                        data: {
                            labels: window.wrapLabels(fakultasLabels, isMobile ? 14 : 28),
                            datasets: [{
                                label: 'Jumlah Di-follow Up',
                                data: fakultasData,
                                backgroundColor: '#0369a1',
                                borderRadius: 6,
                                maxBarThickness: 40,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            layout: { padding: { right: 8 } },
                            scales: {
                                // grace = ruang kosong di kanan batang terpanjang, tempat label persen
                                x: { beginAtZero: true, grace: '20%', ticks: { precision: 0 } },
                                y: { grid: { display: false }, ticks: { autoSkip: false, font: { size: isMobile ? 10 : 12 } } },
                            },
                            plugins: {
                                legend: { display: false },
                                datalabels: window.barDataLabels({ color: '#0369a1' }),
                                tooltip: window.percentTooltip(),
                            },
                        },
                    });

                    const buatPie = (canvasId, labels, data) => {
                        new Chart(document.getElementById(canvasId), {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: data,
                                    backgroundColor: window.chartPalette(labels.length),
                                    borderColor: '#fff',
                                    borderWidth: 2,
                                }],
                            },
                            options: {
                                plugins: {
                                    legend: window.pieLegendWithPercent(),
                                    datalabels: window.pieDataLabels(),
                                    tooltip: window.percentTooltip({ withName: true }),
                                },
                            },
                        });
                    };

                    buatPie('chartRencana', @json($detail['rencanaLabels']), @json($detail['rencanaData']));
                    buatPie('chartPertimbangan', @json($detail['pertimbanganLabels']), @json($detail['pertimbanganData']));
                    buatPie('chartStatus', @json($detail['statusLabels']), @json($detail['statusData']));
                });
            </script>
        @else
            <div class="neu-card p-8 text-center text-sm text-gray-400">
                Belum ada data follow up untuk filter ini.
            </div>
        @endif
    @endif
</x-app-layout>
