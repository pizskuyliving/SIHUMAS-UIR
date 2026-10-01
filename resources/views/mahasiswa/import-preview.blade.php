<x-app-layout title="Preview Import Excel">
    <div class="mb-4">
        <a href="{{ route('mahasiswa.import') }}" class="text-sm text-emerald-700 underline">&larr; Batal, upload file lain</a>
    </div>

    <div class="neu-card p-4 sm:p-6 mb-6">
        <p class="text-sm text-gray-600">
            Tujuan: <strong>{{ $prodi->fakultas->nama }} - {{ $prodi->nama }}</strong><br>
            File: <strong>{{ $namaFileAsli }}</strong>
        </p>

        <div class="grid grid-cols-3 gap-3 mt-4">
            <div class="neu-input p-3 text-center">
                <p class="text-2xl font-bold text-emerald-700">{{ $totalBaru }}</p>
                <p class="text-xs text-gray-500">Data Baru</p>
            </div>
            <div class="neu-input p-3 text-center">
                <p class="text-2xl font-bold text-amber-600">{{ $totalDiperbarui }}</p>
                <p class="text-xs text-gray-500">Akan Diperbarui</p>
            </div>
            <div class="neu-input p-3 text-center">
                <p class="text-2xl font-bold text-red-600">{{ $totalDilewati }}</p>
                <p class="text-xs text-gray-500">Dilewati (Error)</p>
            </div>
        </div>

        @if ($totalDilewati > 0)
            <p class="text-xs text-red-600 mt-3">
                ⚠️ Ada {{ $totalDilewati }} baris yang akan DILEWATI (tidak ikut diimport) karena nama/NPM kosong
                atau NPM bukan angka. Cek tabel di bawah, kolom "Keterangan".
            </p>
        @endif

        @if ($totalBaru + $totalDiperbarui > 0)
            <form method="POST" action="{{ route('mahasiswa.import.store') }}" class="mt-5">
                @csrf
                <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
                <input type="hidden" name="temp_path" value="{{ $tempPath }}">
                <input type="hidden" name="nama_file_asli" value="{{ $namaFileAsli }}">
                <button type="submit"
                        class="neu-btn-primary inline-flex items-center justify-center gap-2 text-sm sm:text-base px-6 sm:px-8 py-3 w-full sm:w-auto">
                    <x-icon name="check" class="w-5 h-5 shrink-0" />
                    <span>Konfirmasi & Import {{ $totalBaru + $totalDiperbarui }} Data</span>
                </button>
            </form>
        @else
            <p class="text-sm text-red-600 mt-4 font-medium">
                Tidak ada data yang bisa diimport dari file ini. Perbaiki file-nya lalu upload ulang.
            </p>
        @endif
    </div>

    <div class="neu-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-3 py-2 text-left">No</th>
                    <th class="px-3 py-2 text-left">Nama</th>
                    <th class="px-3 py-2 text-left">NPM</th>
                    <th class="px-3 py-2 text-left">No. HP</th>
                    <th class="px-3 py-2 text-left">No. HP 2</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($rows as $row)
                    <tr>
                        <td class="px-3 py-2">{{ $row['no'] }}</td>
                        <td class="px-3 py-2">{{ $row['nama'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $row['npm'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $row['no_hp'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $row['no_hp_2'] ?? '-' }}</td>
                        <td class="px-3 py-2">
                            @if ($row['status'] === 'baru')
                                <span class="text-xs bg-emerald-100 text-emerald-700 rounded-full px-2 py-1">Baru</span>
                            @elseif ($row['status'] === 'diperbarui')
                                <span class="text-xs bg-amber-100 text-amber-700 rounded-full px-2 py-1">Diperbarui</span>
                            @else
                                <span class="text-xs bg-red-100 text-red-700 rounded-full px-2 py-1">Dilewati</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ $row['alasan'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>