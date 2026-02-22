<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Manake.Id</title>
    <link rel="icon" type="image/png" href="{{ asset('MANAKE-FAV-M.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 sm:py-10">
        <div class="card w-full max-w-5xl overflow-hidden rounded-3xl shadow-xl lg:grid lg:grid-cols-2">
            <div class="p-6 sm:p-8 lg:p-10">
                <h2 class="text-2xl font-semibold text-blue-700">Login Manake</h2>
                <p class="mt-2 text-sm text-slate-500">Masukkan email dan password Anda.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            placeholder="nama@email.com"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Kata Sandi</label>
                        <x-password-input
                            id="login-password"
                            name="password"
                            :required="true"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            wrapper-class="mt-2"
                            input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        />
                    </div>

                    <button class="btn-primary w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                        Login
                    </button>
                </form>

                <div class="mt-4 flex flex-col gap-2 text-sm text-slate-500">
                    <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-700">Lupa password?</a>
                    <p>
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700">Daftar sekarang</a>
                    </p>
                </div>
            </div>

            <div class="relative hidden p-8 text-white lg:block lg:p-10 bg-gradient-to-br from-slate-950 via-blue-900 to-slate-900">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_top,_white,_transparent_60%)]"></div>
                <div class="relative z-10">
                    <img src="{{ asset('manake-logo-blue.png') }}" alt="Manake" class="h-12 w-auto bg-white rounded-xl p-2">
                    <h1 class="mt-6 text-2xl md:text-3xl font-semibold leading-tight">
                        Masuk untuk melanjutkan transaksi rental profesional.
                    </h1>
                    <p class="mt-3 text-sm text-blue-100 leading-relaxed">
                        Login hanya diperlukan saat Anda ingin menambahkan item ke keranjang atau checkout. Jelajahi katalog bebas tanpa login.
                    </p>
                    <div class="mt-6 space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                            <p>Pantau status pesanan dan histori pembayaran.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                            <p>Notifikasi ketersediaan dan reminder pengembalian.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                            <p>Pembayaran cepat dengan data profil tersimpan.</p>
                        </div>
                    </div>
                    <a href="/" class="mt-6 inline-flex items-center justify-center rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
