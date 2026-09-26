<?php

namespace App\Http\Controllers;

use App\Imports\MahasiswaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaImportController extends Controller
{
    public function create()
    {
        return view('mahasiswa.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new MahasiswaImport();
        Excel::import($import, $request->file('file_excel'));

        return back()->with(
            'status',
            "Import selesai. Data baru: {$import->imported}, data diperbarui: {$import->updated}."
        );
    }
}
