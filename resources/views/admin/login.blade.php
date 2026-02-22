<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin | Manake</title>
    <link rel="icon" type="image/png" href="{{ asset('MANAKE-FAV-M.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-black via-slate-900 to-blue-900">

<div class="w-full max-w-md rounded-2xl bg-slate-800 p-8 shadow-xl">
    <div class="text-center mb-6">
        <img src="{{ asset('manake-logo-blue.png') }}" class="mx-auto h-12 mb-4">
        <h2 class="text-xl font-semibold text-white">Login Admin</h2>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-500 px-4 py-3 text-white">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
        @csrf

        <input type="email" name="email" placeholder="Email"
            class="w-full rounded-lg bg-slate-700 px-4 py-2 text-white">

        <div class="relative">
            <input id="admin-login-password" type="password" name="password" placeholder="Kata Sandi"
                class="w-full rounded-lg bg-slate-700 px-4 py-2 pr-10 text-white">
            <button type="button" data-password-toggle="admin-login-password"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-white">
                <svg data-icon-show xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <svg data-icon-hide class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.4 0-10-7-10-7a20.77 20.77 0 0 1 5.06-6.94" />
                    <path d="M1 1l22 22" />
                    <path d="M9.88 9.88a3 3 0 0 0 4.24 4.24" />
                </svg>
            </button>
        </div>

        <button class="w-full rounded-lg bg-blue-600 py-2 font-semibold text-white hover:bg-blue-700">
            Login
        </button>
    </form>
</div>

<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const targetId = button.getAttribute('data-password-toggle');
        const input = document.getElementById(targetId);
        const iconShow = button.querySelector('[data-icon-show]');
        const iconHide = button.querySelector('[data-icon-hide]');

        if (!input) return;

        button.addEventListener('click', () => {
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            if (iconShow && iconHide) {
                iconShow.classList.toggle('hidden', !isText);
                iconHide.classList.toggle('hidden', isText);
            }
        });
    });
</script>
</body>
</html>
