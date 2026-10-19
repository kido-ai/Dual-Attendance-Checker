{{--
    components/input-error.blade.php

    Usage: <x-input-error :messages="$errors->get('email')" class="mt-2" />
--}}
@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-sm text-red-600']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center gap-1.5">
                <x-icon name="exclamation-triangle" class="h-3.5 w-3.5 shrink-0" />
                {{ $message }}
            </li>
        @endforeach
    </ul>
@endif
