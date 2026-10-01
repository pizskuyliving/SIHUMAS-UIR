<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Pertimbangan;
use App\Models\RencanaWisuda;
use App\Models\StatusFollowUp;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    private const TABS = ['status', 'rencana', 'pertimbangan', 'fakultas'];

    public function index(Request $request)
    {
        $tab = in_array($request->get('tab'), self::TABS, true) ? $request->get('tab') : 'status';

        return view('superadmin.master-data.index', [
            'tab' => $tab,
            'statusItems' => StatusFollowUp::orderBy('urutan')->get(),
            'rencanaItems' => RencanaWisuda::orderBy('urutan')->get(),
            'pertimbanganItems' => Pertimbangan::orderBy('urutan')->get(),
            'fakultasList' => Fakultas::with('prodis')->orderBy('nama')->get(),
        ]);
    }
}
