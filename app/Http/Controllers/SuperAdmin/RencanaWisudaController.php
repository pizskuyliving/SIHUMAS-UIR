<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RencanaWisuda;
use Illuminate\Http\Request;

class RencanaWisudaController extends Controller
{
    public function index()
    {
        $items = RencanaWisuda::orderBy('urutan')->get();

        return view('superadmin.rencana-wisuda.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $validated['urutan'] = RencanaWisuda::max('urutan') + 1;

        RencanaWisuda::create($validated);

        return back()->with('status', 'Pilihan rencana wisuda ditambahkan.');
    }

    public function update(Request $request, RencanaWisuda $rencanaWisuda)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $rencanaWisuda->update($validated);

        return back()->with('status', 'Pilihan rencana wisuda diperbarui.');
    }

    public function destroy(RencanaWisuda $rencanaWisuda)
    {
        $rencanaWisuda->delete();

        return back()->with('status', 'Pilihan rencana wisuda dihapus.');
    }
}
