{{--
    teacher/attendance/failed.blade.php

    Full-page alternative to the "Attendance Failed" modal on
    start.blade.php. Sample data is inlined so the page renders
    standalone; in production $record/$reason would come from the
    controller action that handled the failed scan.
--}}
@php
    $record = [
        'name' => 'Bianca Ramos',
        'number' => '2023-01412',
        'reason' => 'Face Mismatch',
        'qr_status' => 'Verified',
        'face_status' => 'Mismatch',
    ];
@endphp

<x-layouts.teacher
    title="Attendance Failed"
    :breadcrumbs="[['label' => 'Teacher'], ['label' => 'Take Attendance'], ['label' => 'Failed']]"
>
    <div class="mx-auto flex max-w-lg flex-col items-center py-10 text-center">
        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-red-100 text-red-600">
            <x-icon name="exclamation-triangle" class="h-14 w-14" />
        </div>

        <h2 class="mt-6 text-2xl font-semibold tracking-tight text-gray-900">Attendance Failed</h2>
        <p class="mt-1 text-sm font-medium text-red-600">{{ $record['reason'] }}</p>

        <div class="mt-6 w-full rounded-2xl border border-gray-100 bg-white p-6 text-left shadow-sm">
            <div class="flex items-center gap-4">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($record['name']) }}&background=E2E8F0&color=334155&size=96"
                    alt="{{ $record['name'] }}"
                    class="h-16 w-16 rounded-2xl shadow-sm"
                >
                <div>
                    <p class="text-base font-semibold text-gray-900">{{ $record['name'] }}</p>
                    <p class="text-sm text-gray-500">Student No. {{ $record['number'] }}</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-center">
                    <p class="text-xs text-emerald-600">QR Verification</p>
                    <p class="mt-0.5 text-sm font-semibold text-emerald-700">{{ $record['qr_status'] }}</p>
                </div>
                <div class="rounded-xl bg-red-50 px-4 py-3 text-center">
                    <p class="text-xs text-red-600">Face Verification</p>
                    <p class="mt-0.5 text-sm font-semibold text-red-700">{{ $record['face_status'] }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex w-full gap-3">
            <a
                href="{{ route('teacher.attendance.take') }}"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition-colors duration-150 hover:bg-gray-50"
            >
                <x-icon name="arrow-path" class="h-5 w-5" />
                Try Again
            </a>
            <a
                href="{{ route('teacher.attendance.take') }}"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700"
            >
                <x-icon name="arrow-right" class="h-5 w-5" />
                Scan Another Student
            </a>
        </div>
    </div>
</x-layouts.teacher>
