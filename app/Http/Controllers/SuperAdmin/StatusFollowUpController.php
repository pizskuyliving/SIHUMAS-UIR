<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StatusFollowUp;
use Illuminate\Http\Request;

class StatusFollowUpController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $validated['urutan'] = StatusFollowUp::max('urutan') + 1;

        StatusFollowUp::create($validated);

        ActivityLog::catat('tambah_status', "Menambah pilihan status follow up \"{$validated['nama']}\".");
        return back()->with('status', 'Pilihan status ditambahkan.');
    }

    public function update(Request $request, StatusFollowUp $statusFollowUp)
    {
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $lama = $statusFollowUp->nama;
        $statusFollowUp->update($validated);

        ActivityLog::catat('ubah_status', "Mengubah pilihan status follow up \"{$lama}\" menjadi \"{$validated['nama']}\".");
        return back()->with('status', 'Pilihan status diperbarui.');
    }

    public function destroy(StatusFollowUp $statusFollowUp)
    {
        $nama = $statusFollowUp->nama;
        $statusFollowUp->delete();

        ActivityLog::catat('hapus_status', "Menghapus pilihan status follow up \"{$nama}\".");
        return back()->with('status', 'Pilihan status dihapus.');
    }
}
