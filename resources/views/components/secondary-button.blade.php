<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-xl border border-[#1A1A1E] bg-[#111113] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#E8E8EC] shadow-sm transition ease-in-out duration-150 hover:border-[#D4A843]/40 hover:text-[#D4A843] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/20 focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
