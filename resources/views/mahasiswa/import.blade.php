<x-app-layout title="Import Data Excel">
    <div class="max-w-xl bg-white rounded-xl shadow-sm border p-6">
        <p class="text-sm text-gray-600 mb-4">
            Format file Excel yang diterima: kolom <strong>No, Nama Mahasiswa, NPM, Nomor kontak</strong>
            pada baris pertama sebagai header. Data akan otomatis masuk ke tabel utama; jika NPM sudah ada,
            data lama akan diperbarui (tidak duplikat).
        </p>

        <form method="POST" action="{{ route('mahasiswa.import.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">File Excel (.xlsx / .xls / .csv)</label>
                <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required
                       class="w-full text-sm border border-gray-300 rounded-lg p-2">
            </div>
            <button class="bg-emerald-700 text-white text-sm px-5 py-2 rounded-lg hover:bg-emerald-800">
                Upload & Proses
            </button>
        </form>
    </div>
</x-app-layout>
