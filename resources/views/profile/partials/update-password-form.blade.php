<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Ubah Kata Sandi
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Gunakan kata sandi yang panjang dan unik agar akun tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Kata Sandi Saat Ini" />
            <x-password-input
                id="update_password_current_password"
                name="current_password"
                autocomplete="current-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                button-class="text-gray-400 hover:text-indigo-600"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Kata Sandi Baru" />
            <x-password-input
                id="update_password_password"
                name="password"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                button-class="text-gray-400 hover:text-indigo-600"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi Kata Sandi" />
            <x-password-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                button-class="text-gray-400 hover:text-indigo-600"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
