<x-app-layout title="Tambah Pengguna">
    <div class="max-w-lg bg-white rounded-xl shadow-sm border p-6">
        <form method="POST" action="{{ route('superadmin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="pic">PIC (Telemarketing)</option>
                    <option value="superadmin">SuperAdmin</option>
                </select>
            </div>
            <button class="bg-emerald-700 text-white text-sm px-5 py-2 rounded-lg hover:bg-emerald-800">
                Simpan
            </button>
        </form>
    </div>
</x-app-layout>
