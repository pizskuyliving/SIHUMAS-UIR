<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SIHUMAS UIR</title>

    <link rel="icon" type="image/png" href="{{ asset('images/LOGO.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>


<body class="bg-primary-dark min-h-screen flex flex-col">


    <!-- Login Content -->

    <main class="flex-1 flex items-center justify-center px-4 py-8">

        <div class="bg-cream rounded-2xl shadow-lg border-2 border-primary-dark w-full max-w-sm p-8">

            <!-- Logo & Title -->

            <div class="text-center mb-6">

                <img src="{{ asset('images/LOGO.png') }}"
                     alt="Logo UIR"
                     class="h-16 w-16 object-contain mx-auto mb-2">

                <h1 class="text-2xl font-extrabold text-primary-dark tracking-wide">
                    Call iHure
                </h1>

                <div class="w-12 h-1.5 bg-accent rounded-full mx-auto my-2"></div>

                <p class="text-sm text-primary">
                    Call Information Human Relation
                </p>

                <p class="text-sm text-primary">
                    Universitas Islam Riau
                </p>

            </div>


            <!-- Error Message -->

            @if ($errors->any())

                <div class="mb-4 rounded-lg bg-coral-light/20 border-2 border-coral text-coral-dark px-3 py-2 text-sm font-medium">

                    {{ $errors->first() }}

                </div>

            @endif


            <!-- Login Form -->

            <form method="POST"
                  action="{{ route('login') }}"
                  class="space-y-4">

                @csrf


                <!-- Email -->

                <div>

                    <label class="block text-sm font-bold text-primary-dark mb-1">
                        Email
                    </label>

                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="w-full rounded-lg border-2 border-primary-light bg-white focus:ring-accent focus:border-accent-dark">

                </div>


                <!-- Password -->

                <div>

                    <label class="block text-sm font-bold text-primary-dark mb-1">
                        Password
                    </label>

                    <input type="password"
                           name="password"
                           required
                           class="w-full rounded-lg border-2 border-primary-light bg-white focus:ring-accent focus:border-accent-dark">

                </div>


                <!-- Remember Me -->

                <label class="flex items-center gap-2 text-sm text-primary-dark">

                    <input type="checkbox"
                           name="remember"
                           class="rounded border-primary-light text-primary focus:ring-accent">

                    Ingat saya

                </label>


                <!-- Login Button -->

                <button type="submit"
                        class="btn-pill w-full bg-accent text-primary-dark py-3 shadow-accent-glow hover:bg-accent-dark">

                    Masuk

                </button>

            </form>


            <!-- Information -->

            <p class="text-xs text-primary mt-6 text-center">

                Akun hanya dibuat oleh SuperAdmin.
                Hubungi atasan Anda jika belum punya akun.

            </p>

        </div>

    </main>


    <!-- Footer -->

    <footer class="bg-primary-dark border-t border-white/10">

        <div class="min-h-[64px] px-6 md:px-8 flex flex-col md:flex-row items-center justify-between gap-2">

            <!-- Copyright -->

            <div class="flex items-center gap-2 text-xs">

                <span class="text-white/90 font-semibold">
                    Copyright © Muhammad Al Hafiz {{ date('Y') }}
                </span>

                <span class="text-white/30">
                    |
                </span>

                <span class="text-white/60">
                    Call iHure
                </span>

                <span class="text-white/30">
                    •
                </span>

                <span class="text-white/60">
                    Universitas Islam Riau
                </span>

            </div>


            <!-- System Information -->

            <div class="flex items-center gap-2 text-xs">

                 <span class="text-white/40">
                        Call Information Human Relation
                </span>

                <span class="text-accent font-extrabold tracking-wide">
                        Call iHure
                </span>

            </div>

        </div>

    </footer>


</body>

</html>