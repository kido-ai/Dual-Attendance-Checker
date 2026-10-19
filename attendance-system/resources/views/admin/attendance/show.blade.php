{{--
    admin/attendance/show.blade.php

    Full-page alternative to the details modal on index.blade.php —
    useful for direct links (e.g. shared from an export or notification).
    In production: $record would come from
    AttendanceController@show(AttendanceRecord $record) via route-model binding.
--}}
@php
    $record = [
        'id' => 1,
        'student' => 'Ana Reyes',
        'student_number' => '2023-01452',
        'course' => 'BS Information Technology',
        'section' => 'BSIT 3-A',
        'teacher' => 'Elena Villanueva',
        'subject' => 'Database Systems',
        'date' => 'July 16, 2026',
        'time' => '10:02 AM',
        'qr_status' => 'Verified',
        'face_status' => 'Matched',
        'status' => 'Present',
        'remarks' => '—',
    ];
@endphp

<x-layouts.admin
    title="Attendance Record"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Attendance Records'], ['label' => $record['student']]]"
>
    <div class="mx-auto max-w-3xl space-y-6">

        <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
            <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
            Back to Attendance Records
        </a>

        {{-- Student profile --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($record['student']) }}&background=059669&color=fff&size=96"
                    alt="{{ $record['student'] }}"
                    class="h-16 w-16 rounded-2xl shadow-sm"
                >
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $record['student'] }}</h2>
                    <p class="text-sm text-gray-500">{{ $record['student_number'] }} &middot; {{ $record['course'] }}, {{ $record['section'] }}</p>
                </div>
            </div>
        </div>

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
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $record['subject'] }}</p>
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
    </div>
</x-layouts.admin>
