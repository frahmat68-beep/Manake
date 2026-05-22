@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border border-[#1A1A1E] bg-[#111113] px-4 py-2.5 text-sm text-[#E8E8EC] shadow-sm focus:border-[#D4A843] focus:ring-2 focus:ring-[#D4A843]/20 focus:outline-none']) }}>
