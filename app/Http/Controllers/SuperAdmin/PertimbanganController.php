<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pertimbangan;
use Illuminate\Http\Request;

class PertimbanganController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $validated['urutan'] = Pertimbangan::max('urutan') + 1;

        Pertimbangan::create($validated);

        ActivityLog::catat('tambah_pertimbangan', "Menambah pilihan pertimbangan \"{$validated['nama']}\".");
        return back()->with('status', 'Pilihan pertimbangan ditambahkan.');
    }

    public function update(Request $request, Pertimbangan $pertimbangan)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $lama = $pertimbangan->nama;
        $pertimbangan->update($validated);

        ActivityLog::catat('ubah_pertimbangan', "Mengubah pilihan pertimbangan \"{$lama}\" menjadi \"{$validated['nama']}\".");
        return back()->with('status', 'Pilihan pertimbangan diperbarui.');
    }

    public function destroy(Pertimbangan $pertimbangan)
    {
        $nama = $pertimbangan->nama;
        $pertimbangan->delete();

        ActivityLog::catat('hapus_pertimbangan', "Menghapus pilihan pertimbangan \"{$nama}\".");
        return back()->with('status', 'Pilihan pertimbangan dihapus.');
    }
}
