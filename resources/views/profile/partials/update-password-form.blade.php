<section>
    <header>
        <h2 class="text-xl font-bold text-[#D4A843]">
            {{ __('Ubah Kata Sandi') }}
        </h2>

        <p class="mt-1 text-sm text-[#A0A0A8]">
            {{ __('Gunakan kata sandi yang panjang dan unik agar akun tetap aman.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" />
            <x-password-input
                id="update_password_current_password"
                name="current_password"
                autocomplete="current-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-[#1A1A1E] bg-[#0A0A0B] text-[#E8E8EC] shadow-sm focus:border-[#D4A843] focus:ring-[#D4A843]/20"
                button-class="text-[#A0A0A8] hover:text-[#D4A843]"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" />
            <x-password-input
                id="update_password_password"
                name="password"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-[#1A1A1E] bg-[#0A0A0B] text-[#E8E8EC] shadow-sm focus:border-[#D4A843] focus:ring-[#D4A843]/20"
                button-class="text-[#A0A0A8] hover:text-[#D4A843]"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
            <x-password-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-[#1A1A1E] bg-[#0A0A0B] text-[#E8E8EC] shadow-sm focus:border-[#D4A843] focus:ring-[#D4A843]/20"
                button-class="text-[#A0A0A8] hover:text-[#D4A843]"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-emerald-400"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
