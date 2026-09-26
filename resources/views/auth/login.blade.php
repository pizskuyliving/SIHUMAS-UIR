<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIHUMAS UIR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-emerald-800 min-h-screen flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-8">
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-emerald-800">SIHUMAS</h1>
            <p class="text-sm text-gray-500">Universitas Islam Riau</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border-gray-300 focus:ring-emerald-600 focus:border-emerald-600">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border-gray-300 focus:ring-emerald-600 focus:border-emerald-600">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300">
                Ingat saya
            </label>
            <button type="submit"
                    class="w-full bg-emerald-800 text-white rounded-lg py-2 font-medium hover:bg-emerald-900">
                Masuk
            </button>
        </form>
        <p class="text-xs text-gray-400 mt-6 text-center">
            Akun hanya dibuat oleh SuperAdmin. Hubungi atasan Anda jika belum punya akun.
        </p>
    </div>
</body>
</html>
