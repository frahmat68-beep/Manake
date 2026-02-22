<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Ini area aman aplikasi. Silakan konfirmasi kata sandi Anda sebelum melanjutkan.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Kata Sandi -->
        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-password-input
                id="password"
                name="password"
                :required="true"
                autocomplete="current-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                button-class="text-gray-400 hover:text-indigo-600"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                Konfirmasi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
