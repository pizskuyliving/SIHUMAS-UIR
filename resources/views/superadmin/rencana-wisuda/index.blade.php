<x-app-layout title="Master Rencana Wisuda">
    <div class="max-w-xl bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="font-semibold mb-3 text-sm">Tambah Pilihan Baru</h2>
        <form method="POST" action="{{ route('superadmin.rencana-wisuda.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="nama" placeholder="Contoh: Ditunda" required
                   class="flex-1 rounded-lg border-gray-300 text-sm">
            <button class="bg-emerald-700 text-white text-sm px-4 rounded-lg hover:bg-emerald-800">Tambah</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border divide-y max-w-xl">
        @foreach ($items as $item)
            <div class="flex items-center justify-between px-4 py-3">
                <form method="POST" action="{{ route('superadmin.rencana-wisuda.update', $item) }}" class="flex-1 flex items-center gap-2">
                    @csrf @method('PATCH')
                    <input type="text" name="nama" value="{{ $item->nama }}"
                           class="flex-1 rounded-lg border-gray-300 text-sm">
                    <button class="text-xs underline text-emerald-700">Simpan</button>
                </form>
                <form method="POST" action="{{ route('superadmin.rencana-wisuda.destroy', $item) }}"
                      onsubmit="return confirm('Hapus pilihan ini?')" class="ml-2">
                    @csrf @method('DELETE')
                    <button class="text-xs underline text-red-600">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
</x-app-layout>
