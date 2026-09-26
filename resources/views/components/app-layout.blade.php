<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIHUMAS' }} - SIHUMAS UIR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800">
<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-emerald-800 text-white flex-shrink-0 hidden md:flex md:flex-col">
        <div class="px-5 py-5 border-b border-emerald-700">
            <p class="font-bold text-lg leading-tight">SIHUMAS</p>
            <p class="text-xs text-emerald-200">Universitas Islam Riau</p>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 rounded-lg hover:bg-emerald-700 {{ request()->routeIs('dashboard') ? 'bg-emerald-700 font-semibold' : '' }}">
                📋 Data & Follow Up
            </a>
            <a href="{{ route('mahasiswa.import') }}"
               class="block px-3 py-2 rounded-lg hover:bg-emerald-700 {{ request()->routeIs('mahasiswa.import') ? 'bg-emerald-700 font-semibold' : '' }}">
                ⬆️ Import Excel
            </a>
            <a href="{{ route('statistik') }}"
               class="block px-3 py-2 rounded-lg hover:bg-emerald-700 {{ request()->routeIs('statistik') ? 'bg-emerald-700 font-semibold' : '' }}">
                📊 Statistik
            </a>

            @if(auth()->user()?->isSuperAdmin())
                <p class="pt-4 pb-1 px-3 text-xs uppercase tracking-wide text-emerald-300">SuperAdmin</p>
                <a href="{{ route('superadmin.users.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-emerald-700 {{ request()->routeIs('superadmin.users.*') ? 'bg-emerald-700 font-semibold' : '' }}">
                    👤 Kelola Pengguna
                </a>
                <a href="{{ route('superadmin.status-follow-up.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-emerald-700 {{ request()->routeIs('superadmin.status-follow-up.*') ? 'bg-emerald-700 font-semibold' : '' }}">
                    🏷️ Master Status Follow Up
                </a>
                <a href="{{ route('superadmin.rencana-wisuda.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-emerald-700 {{ request()->routeIs('superadmin.rencana-wisuda.*') ? 'bg-emerald-700 font-semibold' : '' }}">
                    🎓 Master Rencana Wisuda
                </a>
                <a href="{{ route('superadmin.fakultas.index') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-emerald-700 {{ request()->routeIs('superadmin.fakultas.*') ? 'bg-emerald-700 font-semibold' : '' }}">
                    🏛️ Master Fakultas & Prodi
                </a>
            @endif
        </nav>
        <div class="px-4 py-4 border-t border-emerald-700 text-sm">
            <p class="font-medium">{{ auth()->user()?->name }}</p>
            <p class="text-xs text-emerald-200 mb-2">{{ auth()->user()?->role === 'superadmin' ? 'SuperAdmin' : 'PIC Telemarketing' }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs underline text-emerald-200 hover:text-white">Keluar</button>
            </form>
        </div>
    </aside>

    <!-- Content -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b px-4 md:px-8 py-4">
            <h1 class="text-lg font-semibold">{{ $title ?? 'Dashboard' }}</h1>
        </header>

        <main class="flex-1 p-4 md:p-8">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
