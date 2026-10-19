{{--
    components/teacher/class-card.blade.php

    Props:
        subject   - e.g. "Database Systems"
        section   - e.g. "BSIT 3-A"
        room      - e.g. "Room 204"
        schedule  - e.g. "Mon / Wed / Fri · 10:00 - 11:30 AM"
        students  - int, roster size
        status    - 'Ongoing' | 'Upcoming' | 'Completed'
--}}
@props([
    'subject' => 'Subject',
    'section' => 'Section',
    'room' => 'Room',
    'schedule' => 'Schedule',
    'students' => 0,
    'status' => 'Upcoming',
])

@php
    $statusTone = match ($status) {
        'Ongoing' => 'bg-emerald-50 text-emerald-700',
        'Completed' => 'bg-gray-100 text-gray-500',
        default => 'bg-amber-50 text-amber-700',
    };
@endphp

<div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-base font-semibold text-gray-900">{{ $subject }}</p>
            <p class="mt-0.5 text-sm text-gray-500">{{ $section }} &middot; {{ $room }}</p>
        </div>
        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone }}">{{ $status }}</span>
    </div>

    <div class="mt-5 flex items-center gap-4 text-sm text-gray-500">
        <span class="flex items-center gap-1.5">
            <x-icon name="clock" class="h-4 w-4 text-gray-400" />
            {{ $schedule }}
        </span>
    </div>
    <div class="mt-2 flex items-center gap-1.5 text-sm text-gray-500">
        <x-icon name="users" class="h-4 w-4 text-gray-400" />
        {{ $students }} students enrolled
    </div>

    <button
        type="button"
        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700"
    >
        <x-icon name="play" class="h-4 w-4" />
        Start Attendance
    </button>
</div>
