<x-app-layout :title="$title">
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <a href="{{ $backUrl }}" class="text-sm text-emerald-700 underline">&larr; Kembali ke pilih Fakultas/Prodi</a>
        <div class="flex gap-2">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NPM..."
                       class="rounded-lg border-gray-300 text-sm focus:ring-emerald-600 focus:border-emerald-600 w-64">
                <button class="bg-gray-800 text-white text-sm px-4 rounded-lg hover:bg-gray-900">Cari</button>
            </form>
            <a href="{{ route('mahasiswa.import') }}"
            class="inline-flex items-center gap-2 bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg hover:bg-emerald-800">
                <img src="{{ asset('images/icons/import.png') }}" 
                    alt="Import Excel" 
                    class="w-5 h-5 object-contain">
                Import Excel
            </a>
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
              class="mb-3 flex items-center gap-3" x-show="selected.length > 0" x-cloak>
            @csrf
            @method('DELETE')
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="mahasiswa_ids[]" :value="id">
            </template>
            <span class="text-sm text-gray-600" x-text="selected.length + ' data dipilih'"></span>
            <button type="submit" class="text-xs px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                Hapus yang Dipilih
            </button>
            <button type="button" @click="selected = []" class="text-xs text-gray-500 underline">
                Batalkan pilihan
            </button>
        </form>

        <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
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
                        <th class="px-3 py-3 text-left">PIC Telemarketing</th>
                        <th class="px-3 py-3 text-left">Status Follow Up</th>
                        <th class="px-3 py-3 text-left">Rencana Wisuda Jan 2027</th>
                        <th class="px-3 py-3 text-left">Keterangan / Hasil</th>
                        <th class="px-3 py-3 text-left">Follow Up Berikutnya</th>
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
                            <td class="px-3 py-3">{{ $fu?->pic?->name ?? '-' }}</td>

                            <td class="px-3 py-3">
                                <template x-if="!editing">
                                    <span>{{ $fu?->statusFollowUp?->nama ?? '-' }}</span>
                                </template>
                                <template x-if="editing">
                                    <select form="{{ $formId }}" name="status_follow_up_id" class="w-40 rounded border-gray-300 text-xs">
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
                                    <select form="{{ $formId }}" name="rencana_wisuda_id" class="w-40 rounded border-gray-300 text-xs">
                                        <option value="">- pilih -</option>
                                        @foreach ($rencanaOptions as $opt)
                                            <option value="{{ $opt->id }}" @selected($fu?->rencana_wisuda_id === $opt->id)>{{ $opt->nama }}</option>
                                        @endforeach
                                    </select>
                                </template>
                            </td>

                            <td class="px-3 py-3 max-w-xs">
                                <template x-if="!editing">
                                    <span>{{ $fu?->keterangan ?: '-' }}</span>
                                </template>
                                <template x-if="editing">
                                    <textarea form="{{ $formId }}" name="keterangan" rows="2" class="w-48 rounded border-gray-300 text-xs">{{ $fu?->keterangan }}</textarea>
                                </template>
                            </td>

                            <td class="px-3 py-3 max-w-xs">
                                <template x-if="!editing">
                                    <span>{{ $fu?->follow_up_berikutnya ?: '-' }}</span>
                                </template>
                                <template x-if="editing">
                                    <textarea form="{{ $formId }}" name="follow_up_berikutnya" rows="2" class="w-48 rounded border-gray-300 text-xs">{{ $fu?->follow_up_berikutnya }}</textarea>
                                </template>
                            </td>

                            <td class="px-3 py-3 whitespace-nowrap space-x-1">
                                <button type="button" @click="editing = !editing"
                                        x-text="editing ? 'Batal' : 'Edit'"
                                        class="text-xs px-3 py-1 rounded-lg border hover:bg-gray-50"></button>
                                <button form="{{ $formId }}" type="submit" x-show="editing"
                                        class="text-xs px-3 py-1 rounded-lg bg-emerald-700 text-white hover:bg-emerald-800">
                                    Simpan
                                </button>

                                <form method="POST" action="{{ route('mahasiswa.destroy', $m) }}" class="inline"
                                      onsubmit="return confirmAction(this, 'Hapus data {{ addslashes($m->nama_mahasiswa) }}? Follow up-nya juga akan terhapus. Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" x-show="!editing"
                                            class="text-xs px-3 py-1 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-3 py-8 text-center text-gray-400">
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
