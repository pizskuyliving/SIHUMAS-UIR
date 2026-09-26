<x-app-layout title="Statistik">
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

    <p class="text-sm text-gray-500 mt-4">Total mahasiswa terdata: <strong>{{ $totalMahasiswa }}</strong></p>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
    <script>
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
            },
        });
    </script>
</x-app-layout>
