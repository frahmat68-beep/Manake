<section>
    @php
        $isNameLocked = (bool) ($nameLocked ?? false);
    @endphp
    <header>
        <h2 class="text-xl font-bold text-blue-700">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Perbarui informasi profil dan alamat email akun Anda.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full @if($isNameLocked) bg-slate-100 text-slate-500 @endif"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
                :readonly="$isNameLocked"
            />
            @if ($isNameLocked)
                <p class="mt-1 text-xs text-slate-500">{{ __('Nama akun dikunci mengikuti data identitas.') }}</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-800">
                        {{ __('Email Anda belum terverifikasi.') }}

                        <button form="send-verification" class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-600">
                            {{ __('Tautan verifikasi baru telah dikirim ke email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-emerald-600"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
