<x-app-layout :title="$title">
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <a href="{{ $backUrl }}" class="text-sm text-emerald-700 underline">&larr; Kembali ke pilih Fakultas/Prodi</a>
        <div class="flex flex-wrap gap-2">
            <form method="GET" class="flex gap-2 w-full sm:w-auto">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NPM..."
                       class="min-w-0 flex-1 sm:flex-none neu-input text-sm sm:w-64">
                <button class="neu-btn text-sm px-4">Cari</button>
            </form>
            <a href="{{ route('mahasiswa.import') }}"
               class="inline-flex items-center gap-2 neu-btn-primary text-sm px-4 py-2 whitespace-nowrap">
                ⬆️ Import Excel
            </a>
            @if (isset($exportUrl))
                <a href="{{ $exportUrl }}"
                   class="inline-flex items-center gap-2 neu-btn-primary text-sm px-4 py-2 whitespace-nowrap">
                    ⬇️ Unduh Excel
                </a>
            @endif
            @if (auth()->user()?->isSuperAdmin() && isset($deleteAllUrl) && $deleteAllTotal > 0)
                <form method="POST" action="{{ $deleteAllUrl }}"
                      onsubmit="return confirmAction(this, 'Hapus SEMUA {{ $deleteAllTotal }} data di halaman ini (bukan cuma yang tampil di layar)? Follow up-nya juga ikut terhapus. Tindakan ini TIDAK BISA dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 neu-btn-danger text-sm px-4 py-2 whitespace-nowrap">
                        🗑️ Hapus Semua ({{ $deleteAllTotal }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- x-data di sini membungkus toolbar + tabel, supaya state 'selected' bisa
         dipakai bersama oleh checkbox "pilih semua" (di header) dan setiap
         checkbox baris (yang masing-masing masih punya x-data 'editing' sendiri). --}}
    <div x-data="{ selected: [], allIds: @json($mahasiswas->pluck('id')) }">

        {{-- Form hapus massal SENGAJA ditaruh di LUAR <table>, bukan di dalam <tr>,
             supaya tidak dipindah paksa oleh browser (lihat catatan di form per-baris
             di bawah). Input tersembunyi dibuat otomatis dari 'selected' lewat x-for. --}}
        <form id="bulk-delete-form" method="POST" action="{{ route('mahasiswa.destroy-bulk') }}"
              onsubmit="return confirmAction(this, 'Hapus ' + document.querySelectorAll('#bulk-delete-form input[type=hidden][name=\'mahasiswa_ids[]\']').length + ' data terpilih? Follow up-nya juga ikut terhapus. Tindakan ini tidak bisa dibatalkan.')"
              class="mb-3 flex flex-wrap items-center gap-x-3 gap-y-2" x-show="selected.length > 0" x-cloak>
            @csrf
            @method('DELETE')
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="mahasiswa_ids[]" :value="id">
            </template>
            <span class="text-sm text-gray-600" x-text="selected.length + ' data dipilih'"></span>
            <button type="submit" class="text-xs px-3 py-2 rounded-lg neu-btn-danger hover:bg-red-700">
                Hapus yang Dipilih
            </button>
            <button type="button" @click="selected = []" class="text-xs text-gray-500 underline">
                Batalkan pilihan
            </button>
        </form>

        <div class="neu-card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-3 py-3 text-left">
                            <input type="checkbox"
                                   :checked="allIds.length > 0 && selected.length === allIds.length"
                                   @change="selected = $event.target.checked ? [...allIds] : []"
                                   class="rounded border-gray-300">
                        </th>
                        <th class="px-3 py-3 text-left">No.</th>
                        <th class="px-3 py-3 text-left">Nama Mahasiswa</th>
                        <th class="px-3 py-3 text-left">NPM</th>
                        <th class="px-3 py-3 text-left">No. HP</th>
                        <th class="px-3 py-3 text-left">No. HP 2</th>
                        <th class="px-3 py-3 text-left">PIC Telemarketing</th>
                        <th class="px-3 py-3 text-left">Status Follow Up</th>
                        <th class="px-3 py-3 text-left">Rencana Wisuda</th>
                        <th class="px-3 py-3 text-left">Pertimbangan</th>
                        <th class="px-3 py-3 text-left">Catatan</th>
                        <th class="px-3 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($mahasiswas as $m)
                        @php $fu = $m->followUp; $formId = 'follow-form-' . $m->id; @endphp
                        <tr x-data="{ editing: false }" class="align-top">
                            {{-- Form follow-up ditaruh terpisah (tidak bersarang di <tr>) agar
                                 tidak dipindah paksa oleh browser. Input dihubungkan lewat
                                 atribut form="{{ $formId }}". --}}
                            <form id="{{ $formId }}" method="POST" action="{{ route('follow-up.update', $m) }}">
                                @csrf
                                @method('PUT')
                            </form>

                            <td class="px-3 py-3">
                                <input type="checkbox" :value="{{ $m->id }}" x-model.number="selected"
                                       class="rounded border-gray-300">
                            </td>
                            <td class="px-3 py-3">{{ $mahasiswas->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-3 font-medium">{{ $m->nama_mahasiswa }}</td>
                            <td class="px-3 py-3">{{ $m->npm }}</td>
                            <td class="px-3 py-3">{{ $m->no_hp ?: '-' }}</td>
                            {{-- No. HP 2 sama seperti No. HP: diisi dari Excel saat import,
                                 tidak bisa diedit manual lewat form follow-up ini. --}}
                            <td class="px-3 py-3">{{ $m->no_hp_2 ?: '-' }}</td>

                            <td class="px-3 py-3">{{ $fu?->pic?->name ?? '-' }}</td>

                            <td class="px-3 py-3">
                                <template x-if="!editing">
                                    <span>{{ $fu?->statusFollowUp?->nama ?? '-' }}</span>
                                </template>
                                <template x-if="editing">
                                    <select form="{{ $formId }}" name="status_follow_up_id" class="w-40 neu-input text-xs">
                                        <option value="">- pilih -</option>
                                        @foreach ($statusOptions as $opt)
                                            <option value="{{ $opt->id }}" @selected($fu?->status_follow_up_id === $opt->id)>{{ $opt->nama }}</option>
                                        @endforeach
                                    </select>
                                </template>
                            </td>

                            <td class="px-3 py-3">
                                <template x-if="!editing">
                                    <span>{{ $fu?->rencanaWisuda?->nama ?? '-' }}</span>
                                </template>
                                <template x-if="editing">
                                    <select form="{{ $formId }}" name="rencana_wisuda_id" class="w-40 neu-input text-xs">
                                        <option value="">- pilih -</option>
                                        @foreach ($rencanaOptions as $opt)
                                            <option value="{{ $opt->id }}" @selected($fu?->rencana_wisuda_id === $opt->id)>{{ $opt->nama }}</option>
                                        @endforeach
                                    </select>
                                </template>
                            </td>

                            <td class="px-3 py-3">
                                <template x-if="!editing">
                                    <span>{{ $fu?->pertimbangan?->nama ?? $fu?->keterangan ?? '-' }}</span>
                                </template>
                                <template x-if="editing">
                                    <select form="{{ $formId }}" name="pertimbangan_id" class="w-40 neu-input text-xs">
                                        <option value="">- pilih -</option>
                                        @foreach ($pertimbanganOptions as $opt)
                                            <option value="{{ $opt->id }}" @selected($fu?->pertimbangan_id === $opt->id)>{{ $opt->nama }}</option>
                                        @endforeach
                                    </select>
                                </template>
                            </td>

                            <td class="px-3 py-3 max-w-xs">
                                <template x-if="!editing">
                                    <span>{{ $fu?->follow_up_berikutnya ?: '-' }}</span>
                                </template>
                                <template x-if="editing">
                                    <textarea form="{{ $formId }}" name="follow_up_berikutnya" rows="2" class="w-48 neu-input text-xs">{{ $fu?->follow_up_berikutnya }}</textarea>
                                </template>
                            </td>

                            <td class="px-3 py-3 whitespace-nowrap space-x-1">
                                <button type="button" @click="editing = !editing"
                                        x-text="editing ? 'Batal' : 'Edit'"
                                        class="text-xs px-3 py-1 neu-btn-outline"></button>
                                <button form="{{ $formId }}" type="submit" x-show="editing"
                                        class="text-xs px-3 py-1 neu-btn-primary">
                                    Simpan
                                </button>

                                <form method="POST" action="{{ route('mahasiswa.destroy', $m) }}" class="inline"
                                      onsubmit="return confirmAction(this, 'Hapus data {{ addslashes($m->nama_mahasiswa) }}? Follow up-nya juga akan terhapus. Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" x-show="!editing"
                                            class="text-xs px-3 py-1 rounded-lg neu-btn-outline">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-3 py-8 text-center text-gray-400">
                                Belum ada data mahasiswa di sini. Silakan import file Excel terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $mahasiswas->links() }}</div>
</x-app-layout>
