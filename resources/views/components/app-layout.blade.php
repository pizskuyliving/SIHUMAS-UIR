<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'SIHUMAS' }} - SIHUMAS UIR</title>

    <link rel="icon" type="image/png" href="{{ asset('images/LOGO.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-surface text-primary-dark">

<div class="min-h-screen flex">

    <!-- Sidebar -->

    <aside class="w-64 bg-primary-dark text-white flex-shrink-0 hidden md:flex md:flex-col">

        <div class="px-5 py-6 border-b border-white/10 flex items-center gap-3">

            <img src="{{ asset('images/logo.png') }}" alt="Logo UIR" class="h-10 w-10 object-contain">

            <div>

                <p class="font-extrabold text-xl leading-tight tracking-wide">Call iHure</p>

                <p class="text-xs text-primary-light">Universitas Islam Riau</p>

            </div>

        </div>

        <nav class="flex-1 px-3 py-4 space-y-1.5 text-sm">

            <a href="{{ route('dashboard') }}"

               class="btn-pill flex items-center gap-2 px-4 py-2.5 {{ request()->routeIs('dashboard') ? 'bg-accent text-primary-dark shadow-accent-glow' : 'text-white hover:bg-white/10' }}">

                <img src="{{ asset('images/icons/data.png') }}" alt="Data" class="w-5 h-5 object-contain">

                Data & Follow Up

            </a>

            <a href="{{ route('mahasiswa.import') }}"

               class="btn-pill flex items-center gap-2 px-4 py-2.5 {{ request()->routeIs('mahasiswa.import') ? 'bg-accent text-primary-dark shadow-accent-glow' : 'text-white hover:bg-white/10' }}">

                <img src="{{ asset('images/icons/import.png') }}" alt="Import" class="w-5 h-5 object-contain">

                Import Excel

            </a>

            <a href="{{ route('statistik') }}"

               class="btn-pill flex items-center gap-2 px-4 py-2.5 {{ request()->routeIs('statistik') ? 'bg-accent text-primary-dark shadow-accent-glow' : 'text-white hover:bg-white/10' }}">

                <img src="{{ asset('images/icons/statistik.png') }}" alt="Statistik" class="w-5 h-5 object-contain">

                Statistik

            </a>

            @if(auth()->user()?->isSuperAdmin())

                <p class="pt-4 pb-1 px-4 text-xs font-bold uppercase tracking-widest text-primary-light">SuperAdmin</p>

                <a href="{{ route('superadmin.users.index') }}"

                   class="btn-pill flex items-center gap-2 px-4 py-2.5 {{ request()->routeIs('superadmin.users.*') ? 'bg-accent text-primary-dark shadow-accent-glow' : 'text-white hover:bg-white/10' }}">

                    <img src="{{ asset('images/icons/kelolapengguna.png') }}" alt="Kelola Pengguna" class="w-5 h-5 object-contain">

                    Kelola Pengguna

                </a>

                <a href="{{ route('superadmin.status-follow-up.index') }}"

                   class="btn-pill flex items-center gap-2 px-4 py-2.5 {{ request()->routeIs('superadmin.status-follow-up.*') ? 'bg-accent text-primary-dark shadow-accent-glow' : 'text-white hover:bg-white/10' }}">

                    <img src="{{ asset('images/icons/masterstatus.png') }}" alt="Master Status Follow Up" class="w-5 h-5 object-contain">

                    Master Status Follow Up

                </a>

                <a href="{{ route('superadmin.rencana-wisuda.index') }}"

                   class="btn-pill flex items-center gap-2 px-4 py-2.5 {{ request()->routeIs('superadmin.rencana-wisuda.*') ? 'bg-accent text-primary-dark shadow-accent-glow' : 'text-white hover:bg-white/10' }}">

                    <img src="{{ asset('images/icons/masterstatus.png') }}" alt="Master Rencana Wisuda" class="w-5 h-5 object-contain">

                    Master Rencana Wisuda

                </a>

                <a href="{{ route('superadmin.fakultas.index') }}"

                   class="btn-pill flex items-center gap-2 px-4 py-2.5 {{ request()->routeIs('superadmin.fakultas.*') ? 'bg-accent text-primary-dark shadow-accent-glow' : 'text-white hover:bg-white/10' }}">

                    <img src="{{ asset('images/icons/masterstatus.png') }}" alt="Master Fakultas & Prodi" class="w-5 h-5 object-contain">

                    Master Fakultas & Prodi

                </a>

            @endif

        </nav>

        <div class="px-5 py-4 border-t border-white/10 text-sm">

            <p class="font-bold">{{ auth()->user()?->name }}</p>

            <p class="text-xs text-primary-light mb-2">{{ auth()->user()?->role === 'superadmin' ? 'SuperAdmin' : 'PIC Telemarketing' }}</p>

            <form method="POST" action="{{ route('logout') }}">

                @csrf

                <button class="text-xs font-semibold underline decoration-accent text-accent-light hover:text-accent">Keluar</button>

            </form>

        </div>

    </aside>

    <!-- Content -->

    <div class="flex-1 flex flex-col min-w-0">

        <header class="bg-white border-b-2 border-primary-bg px-4 md:px-8 py-4 shadow-sm">

            <h1 class="text-lg font-extrabold text-primary-dark">{{ $title ?? 'Dashboard' }}</h1>

        </header>

        <main class="flex-1 p-4 md:p-8">

            @if (session('status'))

                <div class="mb-4 rounded-lg bg-primary-bg border-2 border-primary-light text-primary-dark px-4 py-3 text-sm font-medium">

                    {{ session('status') }}

                </div>

            @endif

            @if ($errors->any())

                <div class="mb-4 rounded-lg bg-coral-light/20 border-2 border-coral text-coral-dark px-4 py-3 text-sm font-medium">

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

<!-- Modal konfirmasi custom (ganti confirm() bawaan browser), dipakai lewat

     window.confirmAction(this, 'pesan...') di atribut onsubmit form manapun -->

<div id="confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">

    <div class="modal-overlay absolute inset-0 bg-primary-dark/60"></div>

    <div class="modal-card relative bg-cream rounded-2xl shadow-lg border-2 border-coral w-full max-w-sm p-6 text-center">

        <div class="text-4xl mb-2">⚠️</div>

        <p id="confirm-modal-message" class="text-primary-dark font-semibold mb-6"></p>

        <div class="flex gap-3 justify-center">

            <button type="button" id="confirm-modal-cancel"

                    class="btn-pill px-5 py-2 border-2 border-primary text-primary-dark hover:bg-primary-bg">

                Batal

            </button>

            <button type="button" id="confirm-modal-ok"

                    class="btn-pill px-5 py-2 bg-coral text-white shadow-coral-glow hover:bg-coral-dark">

                Ya, Hapus

            </button>

        </div>

    </div>

</div>

</body>

</html>