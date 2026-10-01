<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Pertimbangan;
use Illuminate\Http\Request;

class PertimbanganController extends Controller
{
    public function index()
    {
        $items = Pertimbangan::orderBy('urutan')->get();

        return view('superadmin.pertimbangan.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $validated['urutan'] = Pertimbangan::max('urutan') + 1;

        Pertimbangan::create($validated);

        return back()->with('status', 'Pilihan pertimbangan ditambahkan.');
    }

    public function update(Request $request, Pertimbangan $pertimbangan)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $pertimbangan->update($validated);

        return back()->with('status', 'Pilihan pertimbangan diperbarui.');
    }

    public function destroy(Pertimbangan $pertimbangan)
    {
        $pertimbangan->delete();

        return back()->with('status', 'Pilihan pertimbangan dihapus.');
    }
}
