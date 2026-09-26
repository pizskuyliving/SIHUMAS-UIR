<x-app-layout title="Data & Follow Up Mahasiswa">
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NPM..."
                   class="rounded-lg border-gray-300 text-sm focus:ring-emerald-600 focus:border-emerald-600 w-64">
            <button class="bg-gray-800 text-white text-sm px-4 rounded-lg hover:bg-gray-900">Cari</button>
        </form>
        <a href="{{ route('mahasiswa.import') }}"
           class="inline-flex items-center gap-2 bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg hover:bg-emerald-800">
            ⬆️ Import Excel
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-3 py-3 text-left">No.</th>
                    <th class="px-3 py-3 text-left">Fakultas</th>
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
                        {{-- Form ditaruh terpisah (tidak bersarang di <tr>) agar tidak dipindah paksa
                             oleh browser. Semua input dihubungkan lewat atribut form="{{ $formId }}". --}}
                        <form id="{{ $formId }}" method="POST" action="{{ route('follow-up.update', $m) }}">
                            @csrf
                            @method('PUT')
                        </form>

                        <td class="px-3 py-3">{{ $m->no ?? $loop->iteration }}</td>

                        <td class="px-3 py-3">
                            <template x-if="!editing">
                                <span>{{ $m->nama_fakultas ?: '-' }}</span>
                            </template>
                            <template x-if="editing">
                                @if ($m->prodi)
                                    {{-- Sudah punya Prodi dari hasil import per Fakultas/Prodi, tidak perlu diisi manual --}}
                                    <span class="text-xs text-gray-400">{{ $m->nama_fakultas }}</span>
                                @else
                                    <input form="{{ $formId }}" type="text" name="fakultas" value="{{ $m->fakultas }}"
                                           class="w-32 rounded border-gray-300 text-xs">
                                @endif
                            </template>
                        </td>
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

                        <td class="px-3 py-3 whitespace-nowrap">
                            <button type="button" @click="editing = !editing"
                                    x-text="editing ? 'Batal' : 'Edit'"
                                    class="text-xs px-3 py-1 rounded-lg border hover:bg-gray-50"></button>
                            <button form="{{ $formId }}" type="submit" x-show="editing"
                                    class="text-xs px-3 py-1 rounded-lg bg-emerald-700 text-white hover:bg-emerald-800">
                                Simpan
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-3 py-8 text-center text-gray-400">
                            Belum ada data. Silakan import file Excel terlebih dahulu.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $mahasiswas->links() }}</div>
</x-app-layout>
