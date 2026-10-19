{{--
    components/auth-session-status.blade.php

    Displays a flash message stored in the `status` session key — e.g.
    "We have emailed your password reset link." after a password reset
    request, or a confirmation after email verification.

    Usage: <x-auth-session-status :status="session('status')" />
--}}
@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700']) }}>
        {{ $status }}
    </div>
@endif
