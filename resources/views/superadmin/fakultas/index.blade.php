<x-app-layout title="Master Fakultas & Prodi">
    <div class="max-w-2xl bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="font-semibold mb-3 text-sm">Tambah Fakultas Baru</h2>
        <form method="POST" action="{{ route('superadmin.fakultas.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="nama" placeholder="Contoh: Fakultas Teknik" required
                   class="flex-1 rounded-lg border-gray-300 text-sm">
            <button class="bg-emerald-700 text-white text-sm px-4 rounded-lg hover:bg-emerald-800">Tambah</button>
        </form>
    </div>

    <div class="space-y-4 max-w-2xl">
        @forelse ($fakultasList as $fakultas)
            <div class="bg-white rounded-xl shadow-sm border p-5" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <form method="POST" action="{{ route('superadmin.fakultas.update', $fakultas) }}" class="flex-1 flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="text" name="nama" value="{{ $fakultas->nama }}"
                               class="flex-1 rounded-lg border-gray-300 text-sm font-medium">
                        <button class="text-xs underline text-emerald-700 whitespace-nowrap">Simpan</button>
                    </form>
                    <div class="flex items-center gap-3 ml-3">
                        <button type="button" @click="open = !open" class="text-xs underline text-gray-600">
                            <span x-show="!open">Lihat Prodi ({{ $fakultas->prodis->count() }})</span>
                            <span x-show="open">Tutup</span>
                        </button>
                        <form method="POST" action="{{ route('superadmin.fakultas.destroy', $fakultas) }}"
                              onsubmit="return confirmAction(this, 'Hapus fakultas ini beserta semua prodinya?')">
                            @csrf @method('DELETE')
                            <button class="text-xs underline text-red-600">Hapus</button>
                        </form>
                    </div>
                </div>

                <div x-show="open" x-cloak class="mt-4 pt-4 border-t space-y-2">
                    @foreach ($fakultas->prodis as $prodi)
                        <div class="flex items-center justify-between text-sm">
                            <form method="POST" action="{{ route('superadmin.prodi.update', $prodi) }}" class="flex-1 flex items-center gap-2">
                                @csrf @method('PATCH')
                                <input type="text" name="nama" value="{{ $prodi->nama }}"
                                       class="flex-1 rounded-lg border-gray-300 text-xs">
                                <button class="text-xs underline text-emerald-700">Simpan</button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.prodi.destroy', $prodi) }}"
                                  onsubmit="return confirmAction(this, 'Hapus prodi ini?')" class="ml-2">
                                @csrf @method('DELETE')
                                <button class="text-xs underline text-red-600">Hapus</button>
                            </form>
                        </div>
                    @endforeach

                    <form method="POST" action="{{ route('superadmin.fakultas.prodi.store', $fakultas) }}" class="flex gap-2 pt-2">
                        @csrf
                        <input type="text" name="nama" placeholder="Nama prodi baru, contoh: Teknik Informatika" required
                               class="flex-1 rounded-lg border-gray-300 text-xs">
                        <button class="bg-gray-800 text-white text-xs px-3 rounded-lg hover:bg-gray-900">+ Prodi</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Belum ada fakultas. Tambahkan lewat form di atas.</p>
        @endforelse
    </div>
</x-app-layout>
