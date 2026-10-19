{{--
    components/text-input.blade.php

    Usage: <x-text-input type="email" name="email" :value="old('email')" required autofocus />
--}}
@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-colors duration-150 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 disabled:bg-gray-50 disabled:text-gray-400']) }}
>
