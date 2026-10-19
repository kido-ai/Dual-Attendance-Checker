{{--
    components/modal.blade.php

    Generic Alpine-driven modal. `show` and `close` are Alpine expression
    strings evaluated in the parent scope (not Blade variables), e.g.:

        <x-modal show="showAddModal" close="showAddModal = false" max-width="2xl">
            ...
        </x-modal>

    If `close` is omitted, it defaults to `{$show} = false`.
--}}
@props([
    'show' => 'false',
    'close' => '',
    'maxWidth' => 'lg',
])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
    ][$maxWidth] ?? 'max-w-lg';

    $closeExpr = $close !== '' ? $close : ($show . ' = false');
@endphp

<div
    x-show="{{ $show }}"
    x-cloak
    class="fixed inset-0 z-50"
    aria-modal="true"
    role="dialog"
    @keydown.escape.window="{{ $closeExpr }}"
>
    {{-- Backdrop --}}
    <div
        x-show="{{ $show }}"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
    ></div>

    {{-- Panel --}}
    <div class="fixed inset-0 flex min-h-full items-center justify-center overflow-y-auto p-4">
        <div
            x-show="{{ $show }}"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="{{ $closeExpr }}"
            class="relative w-full {{ $maxWidthClass }} rounded-2xl bg-white shadow-xl"
        >
            {{ $slot }}
        </div>
    </div>
</div>
