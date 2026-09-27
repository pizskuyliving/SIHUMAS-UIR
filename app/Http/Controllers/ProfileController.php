<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'photo' => 'nullable|image|max:2048', // max 2MB
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->hasFile('photo')) {
            // Simpan langsung ke public/avatars (bukan lewat storage:link) supaya
            // tidak perlu setup symlink tambahan di server/Windows.
            $file = $request->file('photo');
            $filename = 'user-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('avatars'), $filename);

            // Hapus foto lama kalau ada, biar tidak menumpuk file tak terpakai
            if ($user->photo_path && file_exists(public_path('avatars/' . $user->photo_path))) {
                @unlink(public_path('avatars/' . $user->photo_path));
            }

            $user->photo_path = $filename;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}
