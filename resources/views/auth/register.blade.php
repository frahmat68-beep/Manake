<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar | Manake.Id</title>
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
                <h2 class="text-2xl font-semibold text-blue-700">Daftar Akun Manake</h2>
                <p class="mt-2 text-sm text-slate-500">Mulai dengan email dan password.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
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
                            id="register-password"
                            name="password"
                            :required="true"
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password"
                            wrapper-class="mt-2"
                            input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Konfirmasi Kata Sandi</label>
                        <x-password-input
                            id="register-password-confirmation"
                            name="password_confirmation"
                            :required="true"
                            placeholder="Ulangi password"
                            autocomplete="new-password"
                            wrapper-class="mt-2"
                            input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        />
                    </div>

                    <button class="btn-primary w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                        Lanjutkan
                    </button>
                </form>

                <p class="mt-4 text-sm text-slate-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700">Login</a>
                </p>
            </div>

            <div class="relative hidden p-8 text-white lg:block lg:p-10 bg-gradient-to-br from-slate-900 via-blue-900 to-blue-700">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_top,_white,_transparent_60%)]"></div>
                <div class="relative z-10">
                    <img src="{{ asset('manake-logo-blue.png') }}" alt="Manake" class="h-12 w-auto bg-white rounded-xl p-2">
                    <h1 class="mt-6 text-2xl md:text-3xl font-semibold leading-tight">
                        Buat akun untuk menyimpan pesanan dan mempercepat pembayaran.
                    </h1>
                    <p class="mt-3 text-sm text-blue-100 leading-relaxed">
                        Langkah 1 hanya email + kata sandi. Setelah itu Anda akan diminta melengkapi data diri untuk transaksi.
                    </p>
                    <div class="mt-6 flex flex-col gap-3 text-sm">
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-3">
                            <p class="text-xs uppercase tracking-widest text-blue-100">Langkah 1</p>
                            <p class="mt-1 font-semibold">Email + Kata Sandi</p>
                        </div>
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-3">
                            <p class="text-xs uppercase tracking-widest text-blue-100">Langkah 2</p>
                            <p class="mt-1 font-semibold">Lengkapi data diri</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
