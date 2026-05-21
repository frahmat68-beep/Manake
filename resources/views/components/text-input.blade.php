@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200']) }}>
