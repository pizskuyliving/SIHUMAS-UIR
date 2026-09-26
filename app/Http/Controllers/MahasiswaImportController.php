<?php

namespace App\Http\Controllers;

use App\Imports\MahasiswaImport;
use App\Models\Fakultas;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaImportController extends Controller
{
    public function create()
    {
        $fakultasList = Fakultas::with('prodis')->orderBy('nama')->get();

        // Disiapkan sebagai array PHP polos di sini (bukan di view) supaya
        // @json() di Blade tinggal menerima satu variabel sederhana.
        $fakultasJson = $fakultasList->map(function ($f) {
            return [
                'id' => $f->id,
                'nama' => $f->nama,
                'prodis' => $f->prodis->map(function ($p) {
                    return ['id' => $p->id, 'nama' => $p->nama];
                })->values(),
            ];
        })->values();

        return view('mahasiswa.import', compact('fakultasList', 'fakultasJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodis,id',
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new MahasiswaImport((int) $request->input('prodi_id'));
        Excel::import($import, $request->file('file_excel'));

        $prodi = Prodi::with('fakultas')->find($request->input('prodi_id'));

        return back()->with(
            'status',
            "Import untuk {$prodi->fakultas->nama} - {$prodi->nama} selesai. "
            . "Data baru: {$import->imported}, data diperbarui: {$import->updated}."
        );
    }
}
