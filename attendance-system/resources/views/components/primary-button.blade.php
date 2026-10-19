{{-- components/primary-button.blade.php --}}
<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-200 disabled:cursor-not-allowed disabled:opacity-60']) }}
>
    {{ $slot }}
</button>
