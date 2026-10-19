{{--
    student/attendance/show.blade.php

    Full-page alternative to the details modal on index.blade.php —
    useful for direct links. In production: $record would come from
    StudentAttendanceController@show(AttendanceRecord $record), scoped to
    the logged-in student via route-model binding + a policy/gate check.
--}}
@php
    $record = [
        'id' => 1,
        'date' => 'July 16, 2026',
        'time' => '10:02 AM',
        'teacher' => 'Prof. Elena Villanueva',
        'subject' => 'Database Systems',
        'section' => 'BSIT 3-A',
        'qr_status' => 'Verified',
        'face_status' => 'Matched',
        'status' => 'Present',
        'remarks' => '—',
    ];
@endphp

<x-layouts.student
    title="Attendance Record"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Attendance History'], ['label' => $record['date']]]"
>
    <div class="mx-auto max-w-3xl space-y-6">

        <a href="{{ route('student.attendance.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
            <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
            Back to Attendance History
        </a>

        {{-- Session info --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900">Session Information</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-400">Teacher</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $record['teacher'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Subject</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $record['subject'] }} &middot; {{ $record['section'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Attendance Date</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $record['date'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Attendance Time</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $record['time'] }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400">Remarks</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $record['remarks'] }}</p>
                </div>
            </div>
        </div>

        {{-- Verification results --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900">Dual Authentication Results</h3>
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                    <span class="flex items-center gap-2 text-sm text-gray-600">
                        <x-icon name="qr-code" class="h-4 w-4 text-gray-400" /> QR Verification Result
                    </span>
                    <x-status-badge :status="$record['qr_status']" />
                </div>
                <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                    <span class="flex items-center gap-2 text-sm text-gray-600">
                        <x-icon name="video-camera" class="h-4 w-4 text-gray-400" /> Face Verification Result
                    </span>
                    <x-status-badge :status="$record['face_status']" />
                </div>
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <x-icon name="clipboard-check" class="h-4 w-4 text-gray-400" /> Final Attendance Status
                    </span>
                    <x-status-badge :status="$record['status']" />
                </div>
            </div>
        </div>

        {{-- Read-only notice --}}
        <div class="flex items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-xs text-gray-500">
            <x-icon name="shield-check" class="h-4 w-4 text-gray-400" />
            This record is read-only. Attendance records cannot be modified by students.
        </div>
    </div>
</x-layouts.student>
