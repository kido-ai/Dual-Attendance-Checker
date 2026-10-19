{{--
    teacher/attendance/success.blade.php

    Full-page alternative to the "Attendance Success" modal on
    start.blade.php — useful if a real backend implementation redirects
    here after a successful scan rather than staying on a single reactive
    page. Sample data is inlined so the page renders standalone.
--}}
@php
    $record = [
        'name' => 'Ana Reyes',
        'number' => '2023-01452',
        'time' => '10:02 AM',
        'subject' => 'Database Systems',
        'section' => 'BSIT 3-A',
    ];
@endphp

<x-layouts.teacher
    title="Attendance Recorded"
    :breadcrumbs="[['label' => 'Teacher'], ['label' => 'Take Attendance'], ['label' => 'Success']]"
>
    <div class="mx-auto flex max-w-lg flex-col items-center py-10 text-center">
        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <x-icon name="check-circle" class="h-14 w-14" />
        </div>

        <h2 class="mt-6 text-2xl font-semibold tracking-tight text-gray-900">Attendance Successfully Recorded</h2>
        <p class="mt-1 text-sm text-gray-500">Verified through QR Code and Face Recognition for {{ $record['subject'] }}, {{ $record['section'] }}.</p>

        <div class="mt-6 w-full rounded-2xl border border-gray-100 bg-white p-6 text-left shadow-sm">
            <div class="flex items-center gap-4">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($record['name']) }}&background=059669&color=fff&size=96"
                    alt="{{ $record['name'] }}"
                    class="h-16 w-16 rounded-2xl shadow-sm"
                >
                <div>
                    <p class="text-base font-semibold text-gray-900">{{ $record['name'] }}</p>
                    <p class="text-sm text-gray-500">Student No. {{ $record['number'] }}</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-gray-50 px-4 py-3 text-center">
                    <p class="text-xs text-gray-400">Recorded Time</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800">{{ $record['time'] }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-center">
                    <p class="text-xs text-emerald-600">Status</p>
                    <p class="mt-0.5 text-sm font-semibold text-emerald-700">Present</p>
                </div>
            </div>
        </div>

        <a
            href="{{ route('teacher.attendance.take') }}"
            class="mt-6 flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700"
        >
            <x-icon name="arrow-right" class="h-5 w-5" />
            Scan Next Student
        </a>
    </div>
</x-layouts.teacher>
