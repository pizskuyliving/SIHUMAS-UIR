<x-app-layout title="Master Pertimbangan">
    <div class="max-w-xl neu-card p-6 mb-6">
        <h2 class="font-semibold mb-3 text-sm">Tambah Pilihan Baru</h2>
        <form method="POST" action="{{ route('superadmin.pertimbangan.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="nama" placeholder="Contoh: Kendala Administrasi" required
                   class="flex-1 neu-input text-sm">
            <button class="neu-btn-primary text-sm px-4">Tambah</button>
        </form>
    </div>

    <div class="neu-card divide-y max-w-xl">
        @forelse ($items as $item)
            <div class="flex items-center justify-between px-4 py-3">
                <form method="POST" action="{{ route('superadmin.pertimbangan.update', $item) }}" class="flex-1 flex items-center gap-2">
                    @csrf @method('PATCH')
                    <input type="text" name="nama" value="{{ $item->nama }}"
                           class="flex-1 neu-input text-sm">
                    <button class="text-xs underline text-emerald-700">Simpan</button>
                </form>
                <form method="POST" action="{{ route('superadmin.pertimbangan.destroy', $item) }}"
                      onsubmit="return confirmAction(this, 'Hapus pilihan ini?')" class="ml-2">
                    @csrf @method('DELETE')
                    <button class="text-xs underline text-red-600">Hapus</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-400 px-4 py-6">Belum ada pilihan. Tambahkan lewat form di atas.</p>
        @endforelse
    </div>
</x-app-layout>
