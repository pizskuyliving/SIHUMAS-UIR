<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ImportLog;

class ImportLogController extends Controller
{
    public function index()
    {
        $logs = ImportLog::with(['user:id,name', 'prodi.fakultas'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('superadmin.riwayat-import.index', compact('logs'));
    }
}
