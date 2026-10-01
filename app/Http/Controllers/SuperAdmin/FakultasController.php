<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Http\Request;

class FakultasController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);

        Fakultas::create($validated);

        ActivityLog::catat('tambah_fakultas', "Menambah fakultas \"{$validated['nama']}\".");

        return back()->with('status', 'Fakultas berhasil ditambahkan.');
    }

    public function update(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $lama = $fakultas->nama;
        $fakultas->update($validated);

        ActivityLog::catat('ubah_fakultas', "Mengubah nama fakultas \"{$lama}\" menjadi \"{$validated['nama']}\".");

        return back()->with('status', 'Fakultas berhasil diperbarui.');
    }

    public function destroy(Fakultas $fakultas)
    {
        $nama = $fakultas->nama;
        $fakultas->delete();

        ActivityLog::catat('hapus_fakultas', "Menghapus fakultas \"{$nama}\" beserta seluruh prodinya.");

        return back()->with('status', 'Fakultas dan seluruh prodinya dihapus.');
    }

    public function storeProdi(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);

        $fakultas->prodis()->create($validated);

        ActivityLog::catat('tambah_prodi', "Menambah prodi \"{$validated['nama']}\" di fakultas \"{$fakultas->nama}\".");

        return back()->with('status', 'Prodi berhasil ditambahkan.');
    }

    public function updateProdi(Request $request, Prodi $prodi)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $lama = $prodi->nama;
        $prodi->update($validated);

        ActivityLog::catat('ubah_prodi', "Mengubah nama prodi \"{$lama}\" menjadi \"{$validated['nama']}\".");

        return back()->with('status', 'Prodi berhasil diperbarui.');
    }

    public function destroyProdi(Prodi $prodi)
    {
        $nama = $prodi->nama;
        $prodi->delete();

        ActivityLog::catat('hapus_prodi', "Menghapus prodi \"{$nama}\".");

        return back()->with('status', 'Prodi dihapus.');
    }
}
