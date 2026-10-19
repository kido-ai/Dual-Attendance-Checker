{{--
    components/table.blade.php

    Props:
        rows - collection/array of associative arrays with keys:
               student, teacher, class, time, status, qr_status, face_status
--}}
@props(['rows' => []])

@php
    $badge = fn (string $status) => match (strtolower($status)) {
        'present', 'verified', 'matched', 'online' => 'bg-emerald-50 text-emerald-700',
        'late', 'pending' => 'bg-amber-50 text-amber-700',
        'absent', 'rejected', 'mismatch', 'failed' => 'bg-red-50 text-red-700',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <!-- Header: title + search placeholder -->
    <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
            <p class="text-sm text-gray-500">Latest check-ins across all attendance sessions today</p>
        </div>
        <label class="relative block sm:w-64">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input
                type="search"
                placeholder="Search records..."
                class="w-full rounded-xl border border-gray-100 bg-gray-50 py-2 pl-9 pr-3 text-sm placeholder:text-gray-400 focus:border-emerald-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100"
            >
        </label>
    </div>

    <!-- Table (horizontally scrollable on mobile) -->
    <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                    <th class="px-5 py-3 font-medium">Student</th>
                    <th class="px-5 py-3 font-medium">Teacher</th>
                    <th class="px-5 py-3 font-medium">Session</th>
                    <th class="px-5 py-3 font-medium">Time</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">QR Status</th>
                    <th class="px-5 py-3 font-medium">Face Status</th>
                    <th class="px-5 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($rows as $row)
                    <tr class="transition-colors duration-150 hover:bg-gray-50">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($row['student']) }}&background=E2E8F0&color=334155" class="h-8 w-8 rounded-full" alt="{{ $row['student'] }}">
                                <span class="font-medium text-gray-900">{{ $row['student'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $row['teacher'] }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $row['class'] }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $row['time'] }}</td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge($row['status']) }}">{{ $row['status'] }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge($row['qr_status']) }}">{{ $row['qr_status'] }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge($row['face_status']) }}">{{ $row['face_status'] }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <button type="button" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                                <x-icon name="ellipsis-vertical" class="h-5 w-5" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-400">No attendance records yet today.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination placeholder -->
    <div class="flex flex-col gap-3 border-t border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-gray-500">Showing {{ count($rows) }} of 248 records</p>
        <div class="flex items-center gap-1">
            <button type="button" class="rounded-lg border border-gray-100 px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50" disabled>Previous</button>
            <button type="button" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">1</button>
            <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50">2</button>
            <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50">3</button>
            <button type="button" class="rounded-lg border border-gray-100 px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>
