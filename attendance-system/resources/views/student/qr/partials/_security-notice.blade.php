{{--
    student/qr/partials/_security-notice.blade.php

    Included from index.blade.php. Static content — no dynamic data.
--}}
<div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-6">
    <div class="flex items-center gap-2.5">
        <x-icon name="shield-exclamation" class="h-5 w-5 text-amber-600" />
        <h3 class="text-sm font-semibold text-amber-800">Important Reminders</h3>
    </div>
    <ul class="mt-4 space-y-2.5">
        <li class="flex items-start gap-2.5 text-sm text-amber-700">
            <x-icon name="lock-closed" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
            Do not share your QR Code with anyone.
        </li>
        <li class="flex items-start gap-2.5 text-sm text-amber-700">
            <x-icon name="lock-closed" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
            Keep your QR Code private at all times.
        </li>
        <li class="flex items-start gap-2.5 text-sm text-amber-700">
            <x-icon name="link" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
            Your QR Code is permanently linked to your account.
        </li>
        <li class="flex items-start gap-2.5 text-sm text-amber-700">
            <x-icon name="shield-check" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
            Attendance requires both QR Code and Face Recognition to succeed.
        </li>
    </ul>
</div>
