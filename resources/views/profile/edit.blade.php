<x-app-layout title="Edit Profil">
    <div class="max-w-lg neu-card p-6" x-data="profilePhotoCropper()">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4">
                <template x-if="!croppedPreview">
                    <x-avatar :user="$user" class="w-16 h-16 text-xl" />
                </template>
                <template x-if="croppedPreview">
                    <img :src="croppedPreview" alt="Preview foto baru"
                         class="w-16 h-16 rounded-full object-cover neu-avatar-frame">
                </template>
                <div>
                    <label class="block text-sm font-medium mb-1">Foto Profil</label>
                    <input type="file" accept="image/*" @change="pilihFile($event)" class="text-sm">
                    <p class="text-xs text-gray-400 mt-1">JPG/PNG, maksimal 2MB. Kosongkan kalau tidak ingin ganti.</p>
                    <p class="text-xs text-emerald-700 mt-1 font-medium" x-show="croppedPreview" x-cloak>
                        ✓ Foto baru siap disimpan (sudah dipotong rapi)
                    </p>
                </div>
            </div>

            {{-- Hasil crop dikirim sebagai gambar base64 di sini, BUKAN file mentah --}}
            <input type="hidden" name="photo_base64" x-ref="photoBase64">

            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full neu-input text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                @if ($user->isSuperAdmin())
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full neu-input text-sm">
                @else
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full neu-input bg-gray-100 text-sm text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">Hanya SuperAdmin yang bisa mengubah email. Hubungi SuperAdmin kalau perlu diganti.</p>
                @endif
            </div>

            <hr class="border-dashed">

            <p class="text-sm font-semibold text-gray-600">Ganti Password (opsional)</p>
            <div>
                <label class="block text-sm font-medium mb-1">Password Saat Ini</label>
                <input type="password" name="current_password"
                       class="w-full neu-input text-sm">
                <p class="text-xs text-gray-400 mt-1">Wajib diisi hanya kalau mau mengganti password.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password Baru</label>
                <input type="password" name="password"
                       class="w-full neu-input text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                       class="w-full neu-input text-sm">
            </div>

            <button class="neu-btn-primary text-sm px-5 py-2">
                Simpan Perubahan
            </button>
        </form>

        {{-- ==================== Modal Crop Foto ==================== --}}
        <div x-show="cropperOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-primary-dark/60 p-4">
            <div class="modal-surface p-6 w-full max-w-md">
                <p class="font-semibold text-primary-dark mb-1">Atur Posisi & Ukuran Foto</p>
                <p class="text-xs text-primary mb-4">Geser dan perbesar/perkecil kotaknya untuk memilih bagian foto yang dipakai.</p>

                <div class="bg-white rounded-2xl overflow-hidden p-1 shadow-inner" style="max-height: 320px;">
                    <img x-ref="cropImage" class="block max-w-full rounded-xl" alt="Foto yang sedang diatur">
                </div>

                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="batalkanCrop()" class="neu-btn px-5 py-2.5 text-sm">
                        Batal
                    </button>
                    <button type="button" @click="terapkanCrop()" class="neu-btn-primary px-5 py-2.5 text-sm">
                        Gunakan Foto Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
