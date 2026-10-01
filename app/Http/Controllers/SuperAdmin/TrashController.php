<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request)
    {
        $query = Mahasiswa::onlyTrashed()
            ->with(['prodi.fakultas'])
            ->orderByDesc('deleted_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_mahasiswa', 'like', "%{$search}%")
                  ->orWhere('npm', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('superadmin.sampah.index', compact('items'));
    }

    public function restore(int $id)
    {
        $mahasiswa = Mahasiswa::onlyTrashed()->findOrFail($id);
        $mahasiswa->restore();

        ActivityLog::catat('pulihkan_mahasiswa', "Memulihkan data \"{$mahasiswa->nama_mahasiswa}\" ({$mahasiswa->npm}) dari Sampah.");

        return back()->with('status', "Data \"{$mahasiswa->nama_mahasiswa}\" berhasil dipulihkan.");
    }

    public function forceDestroy(int $id)
    {
        $mahasiswa = Mahasiswa::onlyTrashed()->findOrFail($id);
        $nama = $mahasiswa->nama_mahasiswa;
        $npm = $mahasiswa->npm;
        $mahasiswa->forceDelete(); // benar-benar dihapus, follow up-nya ikut terhapus permanen (cascade)

        ActivityLog::catat('hapus_permanen_mahasiswa', "Menghapus PERMANEN data \"{$nama}\" ({$npm}) dari Sampah.");

        return back()->with('status', "Data \"{$nama}\" dihapus permanen.");
    }
}
