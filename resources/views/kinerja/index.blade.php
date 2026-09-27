<x-app-layout :title="$isSuperAdmin ? 'Kinerja PIC' : 'Kinerja Saya'">

    @if ($isSuperAdmin)
        {{-- SuperAdmin: pilih PIC mana yang mau dilihat --}}
        <div class="bg-white rounded-xl shadow-sm border p-5 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Pilih PIC</label>
                    <select name="pic_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm min-w-[220px]">
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
        <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
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
                                   class="text-xs underline text-emerald-700">Lihat Detail</a>
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
    @endif

    {{-- ==================== DETAIL KINERJA 1 PIC ==================== --}}
    @if ($detail)
        <script>
            window.__fakultasList = @json($detail['fakultasJson']);
        </script>

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <x-avatar :user="$detail['picUser']" class="w-12 h-12 text-lg" />
                <div>
                    <p class="text-sm text-gray-500">Menampilkan kinerja:</p>
                    <p class="font-semibold text-lg">{{ $detail['picUser']?->name ?? '-' }}</p>
                </div>
            </div>
            @if ($isSuperAdmin)
                <a href="{{ route('kinerja.index') }}" class="text-sm text-emerald-700 underline">&larr; Kembali ke leaderboard</a>
            @endif
        </div>

        <div x-data="fakultasPicker('{{ $detail['selectedFakultasId'] }}')" class="bg-white rounded-xl shadow-sm border p-5 mb-6">
            <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                @if ($isSuperAdmin)
                    <input type="hidden" name="pic_id" value="{{ $selectedPicId }}">
                @endif
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Fakultas</label>
                    <select name="fakultas_id" x-model="selectedFakultasId" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Semua Fakultas</option>
                        <template x-for="f in fakultasList" :key="f.id">
                            <option :value="f.id" x-text="f.nama"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Prodi</label>
                    <select name="prodi_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Semua Prodi</option>
                        <template x-for="p in prodiOptions" :key="p.id">
                            <option :value="p.id" :selected="p.id == '{{ $detail['selectedProdiId'] }}'" x-text="p.nama"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $detail['dateFrom'] }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-gray-600">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $detail['dateTo'] }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <button class="bg-emerald-700 text-white text-sm px-5 py-2 rounded-lg hover:bg-emerald-800 w-full">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        <p class="text-sm text-gray-500 mb-6">Total mahasiswa yang di-follow up (sesuai filter): <strong>{{ $detail['totalFollowUp'] }}</strong></p>

        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="font-semibold mb-4">Asal Data yang Di-follow Up (per Fakultas)</h2>
            <canvas id="chartFakultasBreakdown" height="100"></canvas>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="font-semibold mb-4">Rencana Wisuda Januari 2027</h2>
                <canvas id="chartRencana" height="220"></canvas>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="font-semibold mb-4">Status Follow Up</h2>
                <canvas id="chartStatus" height="220"></canvas>
            </div>
        </div>

        <script>
            window.addEventListener('load', function () {
                const fakultasBreakdownLabels = @json($detail['fakultasBreakdownLabels']);
                const fakultasBreakdownData = @json($detail['fakultasBreakdownData']);
                const rencanaLabels = @json($detail['rencanaLabels']);
                const rencanaData = @json($detail['rencanaData']);
                const statusLabels = @json($detail['statusLabels']);
                const statusData = @json($detail['statusData']);

                new Chart(document.getElementById('chartFakultasBreakdown'), {
                    type: 'bar',
                    data: {
                        labels: fakultasBreakdownLabels,
                        datasets: [{
                            label: 'Jumlah Di-follow Up',
                            data: fakultasBreakdownData,
                            backgroundColor: '#0369a1',
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: {
                            datalabels: window.percentageDataLabels({ anchor: 'end', align: 'end', color: '#0369a1', textStrokeWidth: 0 }),
                        },
                    },
                });

                new Chart(document.getElementById('chartRencana'), {
                    type: 'pie',
                    data: {
                        labels: rencanaLabels,
                        datasets: [{
                            data: rencanaData,
                            backgroundColor: ['#047857', '#f59e0b', '#dc2626', '#9ca3af'],
                        }],
                    },
                    options: {
                        plugins: { datalabels: window.percentageDataLabels() },
                    },
                });

                new Chart(document.getElementById('chartStatus'), {
                    type: 'bar',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            label: 'Jumlah',
                            data: statusData,
                            backgroundColor: '#047857',
                        }],
                    },
                    options: {
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: {
                            datalabels: window.percentageDataLabels({ anchor: 'end', align: 'top', color: '#047857', textStrokeWidth: 0 }),
                        },
                    },
                });
            });
        </script>
    @endif
</x-app-layout>
