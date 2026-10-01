<x-app-layout title="Data & Follow Up Mahasiswa">
    <p class="text-sm text-gray-600 mb-6">
        Pilih Fakultas untuk melihat daftar Prodi-nya, lalu pilih Prodi untuk membuka data & follow up mahasiswa di prodi tersebut.
    </p>

    <div class="space-y-4 max-w-3xl">
        @forelse ($fakultasList as $fakultas)
            <div class="neu-card p-5" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between text-left">
                    <div>
                        <p class="font-semibold">{{ $fakultas->nama }}</p>
                        <p class="text-xs text-gray-400">{{ $fakultas->prodis_count }} Prodi</p>
                    </div>
                    <span class="text-gray-400 text-sm" x-text="open ? '▲ Tutup' : '▼ Lihat Prodi'"></span>
                </button>

                <div x-show="open" x-cloak class="mt-4 pt-4 border-t grid sm:grid-cols-2 gap-3">
                    @forelse ($fakultas->prodis as $prodi)
                        <a href="{{ route('dashboard.prodi', $prodi) }}"
                           class="neu-btn flex items-center justify-between px-4 py-3 text-sm">
                            <span>{{ $prodi->nama }}</span>
                            <span class="text-xs text-gray-400">{{ $prodi->mahasiswas_count }} mahasiswa</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-400 col-span-2">Fakultas ini belum punya Prodi.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="neu-card p-6 text-sm text-gray-400">
                Belum ada Fakultas. Tambahkan dulu lewat menu <strong>Master Fakultas & Prodi</strong>.
            </div>
        @endforelse

        @if ($jumlahTanpaProdi > 0)
            <a href="{{ route('dashboard.legacy') }}"
               class="block bg-amber-50 border border-amber-200 rounded-xl p-5 hover:bg-amber-100">
                <p class="font-semibold text-amber-800">Data Belum Dikategorikan</p>
                <p class="text-xs text-amber-700 mt-1">
                    {{ $jumlahTanpaProdi }} data mahasiswa diimport sebelum fitur Fakultas & Prodi ada. Klik untuk melihat/mengelolanya.
                </p>
            </a>
        @endif
    </div>
</x-app-layout>
