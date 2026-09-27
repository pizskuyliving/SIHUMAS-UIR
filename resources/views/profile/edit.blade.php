<x-app-layout title="Edit Profil">
    <div class="max-w-lg bg-white rounded-xl shadow-sm border p-6">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4">
                <x-avatar :user="$user" class="w-16 h-16 text-xl" />
                <div>
                    <label class="block text-sm font-medium mb-1">Foto Profil</label>
                    <input type="file" name="photo" accept="image/*" class="text-sm">
                    <p class="text-xs text-gray-400 mt-1">JPG/PNG, maksimal 2MB. Kosongkan kalau tidak ingin ganti.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>

            <hr class="border-dashed">

            <p class="text-sm font-semibold text-gray-600">Ganti Password (opsional)</p>
            <div>
                <label class="block text-sm font-medium mb-1">Password Saat Ini</label>
                <input type="password" name="current_password"
                       class="w-full rounded-lg border-gray-300 text-sm">
                <p class="text-xs text-gray-400 mt-1">Wajib diisi hanya kalau mau mengganti password.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password Baru</label>
                <input type="password" name="password"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>

            <button class="bg-emerald-700 text-white text-sm px-5 py-2 rounded-lg hover:bg-emerald-800">
                Simpan Perubahan
            </button>
        </form>
    </div>
</x-app-layout>
