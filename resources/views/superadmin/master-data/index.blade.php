<x-app-layout title="Master Data">
    {{-- Tab dirender server-side lewat query string ?tab=..., bukan Alpine,
         supaya setelah submit form (tambah/edit/hapus) otomatis kembali ke
         tab yang sama (lewat back()) tanpa perlu state JS tambahan. --}}
    <div class="neu-card p-2 mb-6 inline-flex flex-wrap gap-2">
        @php
            $tabs = [
                'status' => 'Status Follow Up',
                'rencana' => 'Rencana Wisuda',
                'pertimbangan' => 'Pertimbangan',
                'fakultas' => 'Fakultas & Prodi',
            ];
        @endphp
        @foreach ($tabs as $key => $label)
            <a href="{{ route('superadmin.master-data.index', ['tab' => $key]) }}"
               class="{{ $tab === $key ? 'neu-btn-primary' : 'neu-btn' }} text-sm px-4 py-2">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ==================== TAB: STATUS FOLLOW UP ==================== --}}
    @if ($tab === 'status')
        <div class="max-w-xl neu-card p-6 mb-6">
            <h2 class="font-semibold mb-3 text-sm">Tambah Pilihan Baru</h2>
            <form method="POST" action="{{ route('superadmin.status-follow-up.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="nama" placeholder="Contoh: Sudah Wisuda" required
                       class="flex-1 neu-input text-sm">
                <button class="neu-btn-primary text-sm px-4">Tambah</button>
            </form>
        </div>

        <div class="neu-card divide-y max-w-xl">
            @forelse ($statusItems as $item)
                <div class="flex items-center justify-between px-4 py-3">
                    <form method="POST" action="{{ route('superadmin.status-follow-up.update', $item) }}" class="flex-1 flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="text" name="nama" value="{{ $item->nama }}" class="flex-1 neu-input text-sm">
                        <button class="text-xs underline text-emerald-700">Simpan</button>
                    </form>
                    <form method="POST" action="{{ route('superadmin.status-follow-up.destroy', $item) }}"
                          onsubmit="return confirmAction(this, 'Hapus pilihan ini?')" class="ml-2">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 px-4 py-6">Belum ada pilihan. Tambahkan lewat form di atas.</p>
            @endforelse
        </div>
    @endif

    {{-- ==================== TAB: RENCANA WISUDA ==================== --}}
    @if ($tab === 'rencana')
        <div class="max-w-xl neu-card p-6 mb-6">
            <h2 class="font-semibold mb-3 text-sm">Tambah Pilihan Baru</h2>
            <form method="POST" action="{{ route('superadmin.rencana-wisuda.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="nama" placeholder="Contoh: Ditunda" required
                       class="flex-1 neu-input text-sm">
                <button class="neu-btn-primary text-sm px-4">Tambah</button>
            </form>
        </div>

        <div class="neu-card divide-y max-w-xl">
            @forelse ($rencanaItems as $item)
                <div class="flex items-center justify-between px-4 py-3">
                    <form method="POST" action="{{ route('superadmin.rencana-wisuda.update', $item) }}" class="flex-1 flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="text" name="nama" value="{{ $item->nama }}" class="flex-1 neu-input text-sm">
                        <button class="text-xs underline text-emerald-700">Simpan</button>
                    </form>
                    <form method="POST" action="{{ route('superadmin.rencana-wisuda.destroy', $item) }}"
                          onsubmit="return confirmAction(this, 'Hapus pilihan ini?')" class="ml-2">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 px-4 py-6">Belum ada pilihan. Tambahkan lewat form di atas.</p>
            @endforelse
        </div>
    @endif

    {{-- ==================== TAB: PERTIMBANGAN ==================== --}}
    @if ($tab === 'pertimbangan')
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
            @forelse ($pertimbanganItems as $item)
                <div class="flex items-center justify-between px-4 py-3">
                    <form method="POST" action="{{ route('superadmin.pertimbangan.update', $item) }}" class="flex-1 flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="text" name="nama" value="{{ $item->nama }}" class="flex-1 neu-input text-sm">
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
    @endif

    {{-- ==================== TAB: FAKULTAS & PRODI ==================== --}}
    @if ($tab === 'fakultas')
        <div class="max-w-2xl neu-card p-6 mb-6">
            <h2 class="font-semibold mb-3 text-sm">Tambah Fakultas Baru</h2>
            <form method="POST" action="{{ route('superadmin.fakultas.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="nama" placeholder="Contoh: Fakultas Teknik" required
                       class="flex-1 neu-input text-sm">
                <button class="neu-btn-primary text-sm px-4">Tambah</button>
            </form>
        </div>

        <div class="space-y-4 max-w-2xl">
            @forelse ($fakultasList as $fakultas)
                <div class="neu-card p-5" x-data="{ open: false }">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <form method="POST" action="{{ route('superadmin.fakultas.update', $fakultas) }}" class="flex-1 flex items-center gap-2 min-w-0">
                            @csrf @method('PATCH')
                            <input type="text" name="nama" value="{{ $fakultas->nama }}"
                                   class="min-w-0 flex-1 neu-input text-sm font-medium">
                            <button class="text-xs underline text-emerald-700 whitespace-nowrap">Simpan</button>
                        </form>
                        <div class="flex items-center gap-3 sm:ml-3">
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
                                    <input type="text" name="nama" value="{{ $prodi->nama }}" class="flex-1 neu-input text-xs">
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
                                   class="flex-1 neu-input text-xs">
                            <button class="neu-btn text-xs px-3">+ Prodi</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada fakultas. Tambahkan lewat form di atas.</p>
            @endforelse
        </div>
    @endif
</x-app-layout>
