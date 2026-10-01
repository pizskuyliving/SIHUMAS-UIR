<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RencanaWisuda;
use Illuminate\Http\Request;

class RencanaWisudaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $validated['urutan'] = RencanaWisuda::max('urutan') + 1;

        RencanaWisuda::create($validated);

        ActivityLog::catat('tambah_rencana_wisuda', "Menambah pilihan rencana wisuda \"{$validated['nama']}\".");
        return back()->with('status', 'Pilihan rencana wisuda ditambahkan.');
    }

    public function update(Request $request, RencanaWisuda $rencanaWisuda)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $lama = $rencanaWisuda->nama;
        $rencanaWisuda->update($validated);

        ActivityLog::catat('ubah_rencana_wisuda', "Mengubah pilihan rencana wisuda \"{$lama}\" menjadi \"{$validated['nama']}\".");
        return back()->with('status', 'Pilihan rencana wisuda diperbarui.');
    }

    public function destroy(RencanaWisuda $rencanaWisuda)
    {
        $nama = $rencanaWisuda->nama;
        $rencanaWisuda->delete();

        ActivityLog::catat('hapus_rencana_wisuda', "Menghapus pilihan rencana wisuda \"{$nama}\".");
        return back()->with('status', 'Pilihan rencana wisuda dihapus.');
    }
}
