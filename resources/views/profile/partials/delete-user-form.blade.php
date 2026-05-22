<section class="space-y-6">
    <header>
        <h2 class="text-xl font-bold text-[#D4A843]">
            {{ __('Hapus Akun') }}
        </h2>

        <p class="mt-1 text-sm text-[#A0A0A8]">
            {{ __('Setelah akun dihapus, semua data akan dihapus permanen. Pastikan Anda sudah menyimpan data penting sebelum melanjutkan.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Hapus Akun') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-rose-300">
                {{ __('Yakin ingin menghapus akun?') }}
            </h2>

            <p class="mt-1 text-sm text-[#A0A0A8]">
                {{ __('Tindakan ini permanen dan tidak bisa dibatalkan. Masukkan kata sandi untuk konfirmasi penghapusan akun.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" :value="__('Kata Sandi')" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 rounded-md border-[#1A1A1E] bg-[#0A0A0B] text-[#E8E8EC]"
                    :placeholder="__('Kata Sandi')"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Hapus Akun') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
