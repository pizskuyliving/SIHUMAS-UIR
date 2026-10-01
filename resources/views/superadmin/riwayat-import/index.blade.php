<x-app-layout title="Riwayat Import">
    <div class="neu-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Oleh</th>
                    <th class="px-4 py-3 text-left">Fakultas / Prodi Tujuan</th>
                    <th class="px-4 py-3 text-left">Nama File</th>
                    <th class="px-4 py-3 text-left">Baru</th>
                    <th class="px-4 py-3 text-left">Diperbarui</th>
                    <th class="px-4 py-3 text-left">Dilewati</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $log->prodi ? $log->prodi->fakultas->nama . ' - ' . $log->prodi->nama : '-' }}</td>
                        <td class="px-4 py-3">{{ $log->nama_file }}</td>
                        <td class="px-4 py-3 text-emerald-700 font-medium">{{ $log->total_baru }}</td>
                        <td class="px-4 py-3 text-amber-600 font-medium">{{ $log->total_diperbarui }}</td>
                        <td class="px-4 py-3 text-red-600 font-medium">{{ $log->total_dilewati }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat import.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-app-layout>
