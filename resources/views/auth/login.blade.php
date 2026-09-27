<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIHUMAS UIR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-primary-dark min-h-screen flex items-center justify-center px-4">
    <div class="bg-cream rounded-2xl shadow-lg border-2 border-primary-dark w-full max-w-sm p-8">
        <div class="text-center mb-6">
            <img src="{{ asset('images/LOGO.png') }}" alt="Logo UIR" class="h-16 w-16 object-contain mx-auto mb-2">
            <h1 class="text-2xl font-extrabold text-primary-dark tracking-wide">SI-HUMAS</h1>
            <div class="w-12 h-1.5 bg-accent rounded-full mx-auto my-2"></div>
            <p class="text-sm text-primary">Universitas Islam Riau</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-coral-light/20 border-2 border-coral text-coral-dark px-3 py-2 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-primary-dark mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border-2 border-primary-light bg-white focus:ring-accent focus:border-accent-dark">
            </div>
            <div>
                <label class="block text-sm font-bold text-primary-dark mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border-2 border-primary-light bg-white focus:ring-accent focus:border-accent-dark">
            </div>
            <label class="flex items-center gap-2 text-sm text-primary-dark">
                <input type="checkbox" name="remember" class="rounded border-primary-light text-primary focus:ring-accent">
                Ingat saya
            </label>
            <button type="submit"
                    class="btn-pill w-full bg-accent text-primary-dark py-3 shadow-accent-glow hover:bg-accent-dark">
                Masuk
            </button>
        </form>
        <p class="text-xs text-primary mt-6 text-center">
            Akun hanya dibuat oleh SuperAdmin. Hubungi atasan Anda jika belum punya akun.
        </p>
    </div>
</body>
</html>