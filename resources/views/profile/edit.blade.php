<x-app-layout>
    @section('title', __('Profil Pengguna'))

    <div class="mx-auto max-w-4xl space-y-6 py-2 pb-12">
        <header class="mb-2">
            <h1 class="text-3xl font-bold tracking-tight text-[#D4A843]">{{ __('Pengaturan Profil') }}</h1>
            <p class="mt-1 text-sm text-[#A0A0A8]">{{ __('Kelola informasi identitas, keamanan, dan preferensi akun Anda.') }}</p>
        </header>

        <section class="rounded-[2rem] border border-[#1A1A1E] bg-[#111113] p-6 shadow-sm sm:p-10">
            <div class="max-w-2xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        <section class="rounded-[2rem] border border-[#1A1A1E] bg-[#111113] p-6 shadow-sm sm:p-10">
            <div class="max-w-2xl">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        <section class="rounded-[2rem] border border-rose-500/20 bg-rose-950/20 p-6 shadow-sm sm:p-10">
            <div class="max-w-2xl">
                @include('profile.partials.delete-user-form')
            </div>
        </section>
    </div>
</x-app-layout>
