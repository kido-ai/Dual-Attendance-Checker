<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In' }} &middot; {{ $systemSettings['system_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <main class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-5 py-10 sm:px-8">
        <div class="pointer-events-none absolute -top-40 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-emerald-100/70 blur-3xl"></div>

        <div class="relative w-full max-w-md">
            <a href="{{ route('login') }}" class="mb-8 flex items-center justify-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-50 text-emerald-700 shadow-sm shadow-emerald-100">
                    @if (!empty($systemSettings['school_logo']) && Storage::disk('public')->exists($systemSettings['school_logo']))
                    <img src="{{ Storage::disk('public')->url($systemSettings['school_logo']) }}" class="h-full w-full object-contain p-1" alt="{{ $systemSettings['school_name'] }} logo">
                    @else
                    <x-icon name="building-library" class="h-5 w-5" />
                    @endif
                </span>
                <span class="text-left leading-tight">
                    <span class="block text-sm font-semibold text-slate-900">{{ $systemSettings['school_name'] }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ $systemSettings['system_name'] }}</span>
                </span>
            </a>

            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-9">
                {{ $slot }}
            </section>

            <p class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ now()->year }} {{ $systemSettings['school_name'] }}
            </p>
        </div>
    </main>
</body>

</html>
