<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Http\Request;

class FakultasController extends Controller
{
    public function index()
    {
        $fakultasList = Fakultas::with('prodis')->orderBy('nama')->get();

        return view('superadmin.fakultas.index', compact('fakultasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);

        Fakultas::create($validated);

        return back()->with('status', 'Fakultas berhasil ditambahkan.');
    }

    public function update(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $fakultas->update($validated);

        return back()->with('status', 'Fakultas berhasil diperbarui.');
    }

    public function destroy(Fakultas $fakultas)
    {
        $fakultas->delete();

        return back()->with('status', 'Fakultas dan seluruh prodinya dihapus.');
    }

    public function storeProdi(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);

        $fakultas->prodis()->create($validated);

        return back()->with('status', 'Prodi berhasil ditambahkan.');
    }

    public function updateProdi(Request $request, Prodi $prodi)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $prodi->update($validated);

        return back()->with('status', 'Prodi berhasil diperbarui.');
    }

    public function destroyProdi(Prodi $prodi)
    {
        $prodi->delete();

        return back()->with('status', 'Prodi dihapus.');
    }
}
