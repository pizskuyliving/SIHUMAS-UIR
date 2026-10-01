<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->paginate(15);

        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        return view('superadmin.users.create', ['isSuperAdmin' => auth()->user()->isSuperAdmin()]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
        ];

        // Hanya SuperAdmin yang boleh memilih role akun yang dibuat
        // (termasuk bikin SuperAdmin/Koordinator lain). Koordinator cuma
        // boleh bikin akun PIC - dipaksa di server, bukan cuma disembunyikan
        // di form, supaya tidak bisa diakali lewat request manual.
        $isSuperAdmin = $request->user()->isSuperAdmin();
        if ($isSuperAdmin) {
            $rules['role'] = 'required|in:superadmin,koordinator,pic';
        }

        $validated = $request->validate($rules);
        $role = $isSuperAdmin ? $validated['role'] : 'pic';

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'is_active' => true,
        ]);

        ActivityLog::catat('buat_akun', "Membuat akun baru \"{$validated['name']}\" ({$role}).");

        return redirect()->route('superadmin.users.index')
            ->with('status', 'Akun berhasil dibuat.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        ActivityLog::catat('ubah_status_akun', "Mengubah status akun \"{$user->name}\" jadi " . ($user->is_active ? 'aktif' : 'nonaktif') . '.');

        return back()->with('status', 'Status akun diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('Tidak bisa menghapus akun sendiri.');
        }

        $nama = $user->name;
        $user->delete();

        ActivityLog::catat('hapus_akun', "Menghapus akun \"{$nama}\".");

        return back()->with('status', 'Akun dihapus.');
    }
}
