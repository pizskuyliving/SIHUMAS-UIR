<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\StatusFollowUp;
use Illuminate\Http\Request;

class StatusFollowUpController extends Controller
{
    public function index()
    {
        $items = StatusFollowUp::orderBy('urutan')->get();

        return view('superadmin.status-follow-up.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $validated['urutan'] = StatusFollowUp::max('urutan') + 1;

        StatusFollowUp::create($validated);

        return back()->with('status', 'Pilihan status ditambahkan.');
    }

    public function update(Request $request, StatusFollowUp $statusFollowUp)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $statusFollowUp->update($validated);

        return back()->with('status', 'Pilihan status diperbarui.');
    }

    public function destroy(StatusFollowUp $statusFollowUp)
    {
        $statusFollowUp->delete();

        return back()->with('status', 'Pilihan status dihapus.');
    }
}
