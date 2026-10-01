<x-app-layout title="Statistik">
    {{-- Data fakultas dikirim lewat <script> terpisah, bukan atribut HTML,
         supaya tidak rawan rusak oleh tanda kutip/karakter khusus. --}}
    <script>
        window.__fakultasList = @json($fakultasJson);
    </script>

    <div class="flex items-center justify-between mb-4 no-print">
        <div></div>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 neu-btn text-sm px-4 py-2">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div x-data="fakultasPicker('{{ $selectedFakultasId }}')" class="neu-card p-4 sm:p-5 mb-6 no-print">
        <form method="GET" class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Fakultas</label>
                <select name="fakultas_id" x-model="selectedFakultasId"
                        class="w-full neu-input text-sm">
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
                        <option :value="p.id" :selected="p.id == '{{ $selectedProdiId }}'" x-text="p.nama"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="w-full neu-input text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="w-full neu-input text-sm">
            </div>
            <div class="sm:col-span-2 xl:col-span-4 flex items-center gap-3">
                <button class="neu-btn-primary text-sm px-5 py-2">
                    Terapkan Filter
                </button>
                <a href="{{ route('statistik') }}" class="text-sm text-gray-500 underline">Reset</a>
            </div>
        </form>
    </div>

    @if ($totalMahasiswa > 0)
        {{-- 1) Progress kelengkapan data --}}
        <x-completion-bar :percent="$completionPercent"
            :sublabel="\"{$totalLengkap} dari {$totalMahasiswa} mahasiswa sudah lengkap ketiga kolomnya (Status, Rencana Wisuda, Pertimbangan)\"" />

        {{-- 5) Tren follow up masuk, 8 minggu terakhir --}}
        <div class="neu-card p-4 sm:p-6 mb-6 print-avoid-break">
            <h2 class="font-semibold mb-4">Tren Follow Up Masuk (8 Minggu Terakhir)</h2>
            <div class="relative h-56 sm:h-64"><canvas id="chartTren"></canvas></div>
        </div>

        {{-- 3 grafik komposisi terpisah --}}
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
        <x-funnel-table :funnel="$funnel" />

        <p class="text-sm text-gray-500 mt-4">Total mahasiswa terdata (sesuai filter): <strong>{{ $totalMahasiswa }}</strong></p>

        <script>
            // app.js dimuat sebagai <script type="module"> (deferred), jadi kita
            // tunggu window 'load' supaya window.Chart sudah pasti tersedia.
            window.addEventListener('load', function () {
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

                buatPie('chartRencana', @json($rencanaLabels), @json($rencanaData));
                buatPie('chartPertimbangan', @json($pertimbanganLabels), @json($pertimbanganData));
                buatPie('chartStatus', @json($statusLabels), @json($statusData));

                new Chart(document.getElementById('chartTren'), {
                    type: 'line',
                    data: {
                        labels: @json($trenLabels),
                        datasets: [{
                            label: 'Follow Up Masuk',
                            data: @json($trenData),
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
            });
        </script>
    @else
        <div class="neu-card p-8 text-center text-sm text-gray-400">
            Belum ada data mahasiswa untuk filter ini.
        </div>
    @endif
</x-app-layout>
