<x-app-layout title="Sampah">
    <p class="text-sm text-gray-600 mb-4">
        Data yang dihapus dari Data &amp; Follow Up masuk ke sini dulu (soft delete), bisa dipulihkan kapan saja.
        "Hapus Permanen" tidak bisa dibatalkan.
    </p>

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NPM..."
               class="neu-input text-sm w-64">
        <button class="neu-btn px-4">Cari</button>
    </form>

    <div class="neu-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">NPM</th>
                    <th class="px-4 py-3 text-left">Prodi</th>
                    <th class="px-4 py-3 text-left">Dihapus</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($items as $m)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $m->nama_mahasiswa }}</td>
                        <td class="px-4 py-3">{{ $m->npm }}</td>
                        <td class="px-4 py-3">{{ $m->prodi ? $m->prodi->fakultas->nama . ' - ' . $m->prodi->nama : ($m->fakultas ?: '-') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->deleted_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap space-x-1">
                            <form method="POST" action="{{ route('superadmin.sampah.restore', $m->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="neu-btn-primary text-xs px-3 py-1.5">Pulihkan</button>
                            </form>
                            @if (auth()->user()->isSuperAdmin())
                                <form method="POST" action="{{ route('superadmin.sampah.force-destroy', $m->id) }}" class="inline"
                                      onsubmit="return confirmAction(this, 'Hapus PERMANEN data {{ addslashes($m->nama_mahasiswa) }}? Tidak bisa dibatalkan sama sekali.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="neu-btn-danger text-xs px-3 py-1.5">Hapus Permanen</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">Sampah kosong.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</x-app-layout>
