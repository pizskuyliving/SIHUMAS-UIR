<x-app-layout title="Import Data Excel per Fakultas & Prodi">
    {{-- Data fakultas dikirim lewat <script> terpisah, bukan atribut HTML,
         supaya tidak rawan rusak oleh tanda kutip/karakter khusus. --}}
    <script>
        window.__fakultasList = @json($fakultasJson);
    </script>

    <div x-data="fakultasPicker()" class="max-w-xl bg-white rounded-xl shadow-sm border p-6">
        <p class="text-sm text-gray-600 mb-4">
            Format file Excel yang diterima: kolom <strong>No, Nama Mahasiswa, NPM, Nomor kontak</strong>
            pada baris pertama sebagai header. Data akan otomatis masuk ke Fakultas & Prodi yang dipilih
            di bawah; jika NPM sudah ada, data lama akan diperbarui (tidak duplikat).
        </p>

        @if ($fakultasList->isEmpty())
            <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
                Belum ada data Fakultas & Prodi. Minta SuperAdmin menambahkannya dulu di menu
                <strong>Master Fakultas & Prodi</strong>.
            </div>
        @else
            <form method="POST" action="{{ route('mahasiswa.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Fakultas</label>
                    <select x-model="selectedFakultasId" required
                            class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">- pilih fakultas -</option>
                        <template x-for="f in fakultasList" :key="f.id">
                            <option :value="f.id" x-text="f.nama"></option>
                        </template>
                    </select>
                </div>

                <div x-show="selectedFakultasId">
                    <label class="block text-sm font-medium mb-1">Program Studi</label>
                    <select name="prodi_id" x-bind:required="!!selectedFakultasId"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">- pilih prodi -</option>
                        <template x-for="p in prodiOptions" :key="p.id">
                            <option :value="p.id" x-text="p.nama"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-400 mt-1" x-show="prodiOptions.length === 0">
                        Fakultas ini belum punya Prodi. Tambahkan dulu di Master Fakultas & Prodi.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">File Excel (.xlsx / .xls / .csv)</label>
                    <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required
                           class="w-full text-sm border border-gray-300 rounded-lg p-2">
                </div>

                <button class="bg-emerald-700 text-white text-sm px-5 py-2 rounded-lg hover:bg-emerald-800">
                    Upload & Proses
                </button>
            </form>
        @endif
    </div>
</x-app-layout>
