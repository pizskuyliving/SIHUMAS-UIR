<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name')->orderByDesc('created_at');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($search = $request->get('q')) {
            $query->where('deskripsi', 'like', "%{$search}%");
        }

        $logs = $query->paginate(30)->withQueryString();
        $userList = User::orderBy('name')->get(['id', 'name']);

        return view('superadmin.aktivitas.index', compact('logs', 'userList'));
    }
}
