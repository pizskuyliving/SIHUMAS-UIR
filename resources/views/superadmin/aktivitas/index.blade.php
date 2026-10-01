<x-app-layout title="Log Aktivitas">
    <div class="neu-card p-4 sm:p-5 mb-6">
        <form method="GET" class="grid sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Cari deskripsi</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Contoh: hapus, import..."
                       class="w-full neu-input text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-600">Pengguna</label>
                <select name="user_id" class="w-full neu-input text-sm">
                    <option value="">Semua Pengguna</option>
                    @foreach ($userList as $u)
                        <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button class="neu-btn-primary text-sm px-5 py-2 w-full">Terapkan</button>
            </div>
        </form>
    </div>

    <div class="neu-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Pengguna</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                    <th class="px-4 py-3 text-left">Deskripsi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->user?->name ?? '(Sistem)' }}</td>
                        <td class="px-4 py-3"><span class="text-xs bg-gray-100 rounded px-2 py-1">{{ $log->aksi }}</span></td>
                        <td class="px-4 py-3">{{ $log->deskripsi }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada aktivitas tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-app-layout>
