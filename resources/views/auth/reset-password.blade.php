<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Token Atur Ulang Kata Sandi -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Alamat Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Kata Sandi -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Kata Sandi')" />
            <x-password-input
                id="password"
                name="password"
                :required="true"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                button-class="text-gray-400 hover:text-indigo-600"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Kata Sandi -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
            <x-password-input
                id="password_confirmation"
                name="password_confirmation"
                :required="true"
                autocomplete="new-password"
                wrapper-class="mt-1"
                input-class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                button-class="text-gray-400 hover:text-indigo-600"
            />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Atur Ulang Kata Sandi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
