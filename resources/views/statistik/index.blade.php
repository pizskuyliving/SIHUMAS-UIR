<x-app-layout title="Statistik">
    {{-- Data fakultas dikirim lewat <script> terpisah, bukan atribut HTML,
         supaya tidak rawan rusak oleh tanda kutip/karakter khusus. --}}
    <script>
        window.__fakultasList = @json($fakultasJson);
    </script>

    <div x-data="fakultasPicker('{{ $selectedFakultasId }}')" class="bg-white rounded-xl shadow-sm border p-5 mb-6">
        <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Fakultas</label>
                <select name="fakultas_id" x-model="selectedFakultasId"
                        class="w-full rounded-lg border-gray-300 text-sm">
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
                        <option :value="p.id" :selected="p.id == '{{ $selectedProdiId }}'" x-text="p.nama"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <button class="bg-emerald-700 text-white text-sm px-5 py-2 rounded-lg hover:bg-emerald-800">
                    Terapkan Filter
                </button>
                <a href="{{ route('statistik') }}" class="text-sm text-gray-500 underline ml-3">Reset</a>
            </div>
        </form>
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

    <p class="text-sm text-gray-500 mt-4">Total mahasiswa terdata (sesuai filter): <strong>{{ $totalMahasiswa }}</strong></p>

    <script>
        // app.js dimuat sebagai <script type="module"> (deferred), jadi kita
        // tunggu window 'load' supaya window.Chart sudah pasti tersedia
        // sebelum kode di bawah ini dijalankan.
        window.addEventListener('load', function () {
            const rencanaLabels = @json($rencanaLabels);
            const rencanaData = @json($rencanaData);
            const statusLabels = @json($statusLabels);
            const statusData = @json($statusData);

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
                        label: 'Jumlah Mahasiswa',
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
</x-app-layout>
