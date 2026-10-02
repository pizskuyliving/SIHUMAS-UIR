<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Call iHure' }} - Call iHure UIR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="neu-body text-primary-dark">
{{-- sidebarOpen: status drawer sidebar di layar kecil (mobile/tablet). Di layar >= md
     sidebar selalu tampil permanen, jadi state ini tidak berpengaruh. --}}
<div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen flex">

    <!-- Latar gelap di belakang drawer (hanya mobile) -->
    <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-primary-dark/60 md:hidden"></div>

    <!-- Sidebar: drawer di mobile, menempel (sticky) di desktop -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 bottom-0 left-0 z-40 w-64 neu-sidebar text-white flex flex-col transition-transform duration-200 ease-out md:sticky md:bottom-auto md:h-screen md:translate-x-0 md:shrink-0">
        <div class="px-5 py-6 border-b border-white/10 flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="Logo UIR" class="h-10 w-10 object-contain">
            <div class="flex-1 min-w-0">
                <p class="font-extrabold text-xl leading-tight tracking-wide">Call iHure</p>
                <p class="text-xs text-primary-light">Universitas Islam Riau</p>
            </div>
            <button type="button" @click="sidebarOpen = false" aria-label="Tutup menu"
                    class="md:hidden p-1.5 rounded-lg text-white hover:bg-white/10">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <!-- Kartu profil user, sengaja ditaruh di atas (bukan bawah) supaya lebih terlihat -->
        <a href="{{ route('profile.edit') }}"
           class="px-5 py-4 border-b border-white/10 flex items-center gap-3 hover:bg-white/5">
            <x-avatar :user="auth()->user()" class="w-11 h-11 neu-avatar-frame" />
            <div class="min-w-0 flex-1">
                <p class="font-bold text-sm truncate">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-primary-light">{{ auth()->user()?->roleLabel() }}</p>
            </div>
            <span class="text-xs text-accent-light">Edit</span>
        </a>

        <!-- Tombol Keluar sengaja ditaruh tepat di bawah kartu profil (bukan di
             paling bawah sidebar) supaya tidak perlu scroll jauh untuk logout -->
        <form method="POST" action="{{ route('logout') }}" class="px-5 py-2.5 border-b border-white/10">
            @csrf
            <button class="flex items-center gap-2 text-xs font-semibold text-accent-light hover:text-accent">
                <x-icon name="logout" class="w-4 h-4" /> Keluar
            </button>
        </form>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 text-sm">
            <p class="pb-1 px-4 text-xs font-bold uppercase tracking-widest text-primary-light">Kerja Harian</p>
            <a href="{{ route('dashboard') }}"
               class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('dashboard*') ? 'is-active' : '' }}">
                <x-icon name="clipboard" /> Data & Follow Up
            </a>
            <a href="{{ route('mahasiswa.import') }}"
               class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('mahasiswa.import*') ? 'is-active' : '' }}">
                <x-icon name="upload" /> Import Excel
            </a>
            <a href="{{ route('statistik') }}"
               class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('statistik') ? 'is-active' : '' }}">
                <x-icon name="chart" /> Statistik
            </a>
            @unless (auth()->user()?->isAtLeastKoordinator())
                <a href="{{ route('kinerja.index') }}"
                   class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('kinerja.index') ? 'is-active' : '' }}">
                    <x-icon name="chart" /> Kinerja Saya
                </a>
            @endunless

            @if(auth()->user()?->isAtLeastKoordinator())
                <p class="pt-4 pb-1 px-4 text-xs font-bold uppercase tracking-widest text-primary-light">Master Data</p>
                <a href="{{ route('superadmin.master-data.index') }}"
                   class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('superadmin.master-data.*') ? 'is-active' : '' }}">
                    <x-icon name="tag" /> Master Data
                </a>

                <p class="pt-4 pb-1 px-4 text-xs font-bold uppercase tracking-widest text-primary-light">Pengguna</p>
                <a href="{{ route('superadmin.users.index') }}"
                   class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('superadmin.users.*') ? 'is-active' : '' }}">
                    <x-icon name="users" /> Kelola Pengguna
                </a>

                <p class="pt-4 pb-1 px-4 text-xs font-bold uppercase tracking-widest text-primary-light">Laporan & Riwayat</p>
                <a href="{{ route('kinerja.index') }}"
                   class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('kinerja.index') ? 'is-active' : '' }}">
                    <x-icon name="chart" /> Kinerja PIC
                </a>
                <a href="{{ route('superadmin.riwayat-import.index') }}"
                   class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('superadmin.riwayat-import.*') ? 'is-active' : '' }}">
                    <x-icon name="clock" /> Riwayat Import
                </a>
                <a href="{{ route('superadmin.aktivitas.index') }}"
                   class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('superadmin.aktivitas.*') ? 'is-active' : '' }}">
                    <x-icon name="document" /> Log Aktivitas
                </a>

                <p class="pt-4 pb-1 px-4 text-xs font-bold uppercase tracking-widest text-primary-light">Lainnya</p>
                <a href="{{ route('superadmin.sampah.index') }}"
                   class="neu-nav-item flex items-center gap-3 px-4 py-2.5 text-white {{ request()->routeIs('superadmin.sampah.*') ? 'is-active' : '' }}">
                    <x-icon name="trash" /> Sampah
                </a>
            @endif
        </nav>
    </aside>

    <!-- Konten -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="sticky top-0 z-20 neu-header px-4 md:px-8 py-3 md:py-4 flex items-center gap-3">
            <button type="button" @click="sidebarOpen = true" aria-label="Buka menu"
                    class="md:hidden -ml-1 p-2 rounded-lg text-primary-dark hover:bg-primary-bg">
                <x-icon name="menu" class="w-6 h-6" />
            </button>
            <h1 class="text-base md:text-lg font-extrabold text-primary-dark truncate">{{ $title ?? 'Dashboard' }}</h1>
        </header>

        <main class="flex-1 p-4 md:p-8 min-w-0">
            @if (session('status'))
                <div class="mb-4 neu-card text-primary-dark px-4 py-3 text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 neu-card text-coral-dark px-4 py-3 text-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="neu-footer">
            <div class="max-w-6xl mx-auto px-4 md:px-8 py-8 grid sm:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo UIR" class="h-8 w-8 object-contain">
                        <span class="font-extrabold text-primary-dark">Call iHure</span>
                    </div>
                    <p class="text-xs text-primary leading-relaxed">
                        Sistem Informasi Follow Up Mahasiswa &mdash; alat bantu internal Humas
                        Universitas Islam Riau untuk mengelola data dan tindak lanjut calon wisudawan.
                    </p>
                </div>

                <div>
                    <p class="font-semibold text-primary-dark mb-3 text-xs uppercase tracking-wider">Tautan Cepat</p>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('dashboard') }}" class="text-primary hover:text-primary-dark hover:underline">Data & Follow Up</a></li>
                        <li><a href="{{ route('mahasiswa.import') }}" class="text-primary hover:text-primary-dark hover:underline">Import Excel</a></li>
                        <li><a href="{{ route('statistik') }}" class="text-primary hover:text-primary-dark hover:underline">Statistik</a></li>
                        <li><a href="{{ route('kinerja.index') }}" class="text-primary hover:text-primary-dark hover:underline">{{ auth()->user()?->isAtLeastKoordinator() ? 'Kinerja PIC' : 'Kinerja Saya' }}</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="text-primary hover:text-primary-dark hover:underline">Profil Saya</a></li>
                    </ul>
                </div>

                <div>
                    <p class="font-semibold text-primary-dark mb-3 text-xs uppercase tracking-wider">Informasi Sistem</p>
                    <ul class="space-y-2 text-xs text-gray-500">
                        <li>Versi 1.0</li>
                        <li>Masuk sebagai <span class="font-medium text-primary-dark">{{ auth()->user()?->name }}</span></li>
                        <li class="pt-1">Ada kendala teknis? Hubungi SuperAdmin Anda.</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-primary-bg px-4 md:px-8 py-4 text-center text-xs text-gray-400">
                &copy; {{ now()->year }} Call iHure &mdash; Universitas Islam Riau. Seluruh hak dilindungi.
            </div>
        </footer>
    </div>
</div>

<!-- Modal konfirmasi custom (ganti confirm() bawaan browser), dipakai lewat
     window.confirmAction(this, 'pesan...') di atribut onsubmit form manapun -->
<div id="confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="modal-overlay absolute inset-0 bg-primary-dark/60"></div>
    <div class="modal-card relative modal-surface w-full max-w-sm p-7 text-center">
        <div class="text-4xl mb-2">⚠️</div>
        <p id="confirm-modal-message" class="text-primary-dark font-semibold mb-6"></p>
        <div class="flex gap-3 justify-center">
            <button type="button" id="confirm-modal-cancel"
                    class="neu-btn px-5 py-2">
                Batal
            </button>
            <button type="button" id="confirm-modal-ok"
                    class="neu-btn-danger px-5 py-2">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
</body>
</html>
