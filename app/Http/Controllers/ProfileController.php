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
        $isSuperAdmin = $user->isSuperAdmin();

        $rules = [
            'name' => 'required|string|max:255',
            'photo_base64' => 'nullable|string',
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];

        // Email hanya boleh diubah SuperAdmin. PIC tidak divalidasi/diproses
        // sama sekali untuk field email, meskipun dikirim lewat request
        // (jaga-jaga kalau ada yang mengakali form di HTML).
        if ($isSuperAdmin) {
            $rules['email'] = ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];

        if ($isSuperAdmin) {
            $user->email = $validated['email'];
        }

        // Foto sudah di-crop di browser (lewat Cropper.js) sebelum form ini
        // disubmit, jadi yang diterima di sini adalah gambar JPEG hasil crop
        // dalam bentuk data URL base64 ("data:image/jpeg;base64,...."), BUKAN
        // file upload mentah.
        if (! empty($validated['photo_base64'])) {
            $dataUrl = $validated['photo_base64'];

            if (! preg_match('/^data:image\/(\w+);base64,(.+)$/', $dataUrl, $match)) {
                return back()->withErrors('Format foto tidak valid. Coba pilih & atur ulang fotonya.');
            }

            $ekstensi = $match[1] === 'jpeg' ? 'jpg' : $match[1];
            $isiFile = base64_decode($match[2]);

            if ($isiFile === false || strlen($isiFile) > 3 * 1024 * 1024) {
                return back()->withErrors('Foto gagal diproses atau ukurannya terlalu besar. Coba foto lain.');
            }

            // Simpan langsung ke public/avatars (bukan lewat storage:link)
            // supaya tidak perlu setup symlink tambahan di server/Windows.
            $filename = 'user-' . $user->id . '-' . time() . '.' . $ekstensi;
            file_put_contents(public_path('avatars/' . $filename), $isiFile);

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
