<x-app-layout title="Kelola Pengguna">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('superadmin.users.create') }}"
           class="neu-btn-primary text-sm px-4 py-2">
            + Tambah Pengguna
        </a>
    </div>

    <div class="neu-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Foto</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($users as $u)
                    <tr>
                        <td class="px-4 py-3"><x-avatar :user="$u" /></td>
                        <td class="px-4 py-3">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3 capitalize">{{ $u->role }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500' }}">
                                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 space-x-2 whitespace-nowrap">
                            <form action="{{ route('superadmin.users.toggle', $u) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-xs underline text-gray-600">
                                    {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @if ($u->id !== auth()->id())
                                <form action="{{ route('superadmin.users.destroy', $u) }}" method="POST" class="inline"
                                      onsubmit="return confirmAction(this, 'Hapus akun ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs underline text-red-600">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-app-layout>
