{{--
    admin/dashboard.blade.php

    SIMPLIFIED per schema audit — every section on this page maps to a
    real column on a real model (Student, Teacher, Attendance,
    AttendanceSession). Removed in this pass:
      - The three chart placeholders (Weekly/Trend/Monthly) — no chart
        library was ever wired in, and they added visual noise without
        data.
      - "System Status" card — hardcoded strings ("Camera Feed: Online",
        etc.) with no query behind them at all.
      - The welcome banner's "4 classes are in session..." / "All systems
        operational" copy — both hardcoded, neither backed by a query.
      - The recent-attendance table's "Class" column — the Attendance
        model has no attendance_session_id / attendanceSession()
        relationship, so that column had nothing real to show.

    CORRECTION: AttendanceSession *is* a real model with a real
    `attendance_sessions` table (teacher_id, title, attendance_date,
    started_at, ended_at, status) — an earlier pass wrongly deleted it
    entirely. The only actually-broken code was `$attendance->attendanceSession`
    (Attendance has no relationship to it). Direct AttendanceSession
    queries are valid, so "Today's Sessions" below is restored, backed by
    a real `AttendanceSession::today()` query rather than removed.

    Card styling, spacing, and layout are otherwise unchanged from the
    original — this only touches what data is displayed and how it's
    queried.
--}}
@php
$greeting = now()->hour < 12 ? 'Good Morning' : (now()->hour < 18 ? 'Good Afternoon' : 'Good Evening' );
        @endphp

        <x-layouts.admin
        title="Dashboard"
        :breadcrumbs="[['label' => 'Admin'], ['label' => 'Dashboard']]">
        <div class="mx-auto max-w-7xl space-y-6">

            {{-- Welcome section --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-medium text-emerald-600">{{ $greeting }},</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">{{ $admin->full_name }}</h2>
                <p class="mt-2 max-w-xl text-sm text-gray-500">
                    Here's what's happening across campus today, {{ now()->format('l, F j, Y') }}.
                </p>
            </div>

            {{-- Statistics cards --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $stat)
                <x-stat-card
                    icon="{{ $stat['icon'] }}"
                    tone="{{ $stat['tone'] }}"
                    label="{{ $stat['label'] }}"
                    value="{{ $stat['value'] }}"
                    trend="{{ $stat['trend'] }}"
                    trend-value="{{ $stat['trendValue'] }}" />
                @endforeach
            </div>

            {{-- Recent attendance table — only real Attendance columns --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-5">
                    <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
                    <p class="text-sm text-gray-500">Latest records across all teachers</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                <th class="px-5 py-3 font-medium">Student</th>
                                <th class="px-5 py-3 font-medium">Teacher</th>
                                <th class="px-5 py-3 font-medium">Time</th>
                                <th class="px-5 py-3 font-medium">QR Status</th>
                                <th class="px-5 py-3 font-medium">Face Status</th>
                                <th class="px-5 py-3 font-medium">Attendance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($attendanceRows as $row)
                            <tr class="transition-colors duration-150 hover:bg-gray-50">
                                <td class="px-5 py-3.5 font-medium text-gray-900">{{ $row['student'] }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $row['teacher'] }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $row['time'] }}</td>
                                <td class="px-5 py-3.5"><x-status-badge :status="$row['qr_status']" /></td>
                                <td class="px-5 py-3.5"><x-status-badge :status="$row['face_status']" /></td>
                                <td class="px-5 py-3.5"><x-status-badge :status="$row['status']" /></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">No attendance recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent activities + Today's Sessions --}}
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm xl:col-span-2">
                    <h3 class="text-base font-semibold text-gray-900">Recent Activities</h3>
                    <p class="text-sm text-gray-500">A live feed of the latest recorded attendance</p>
                    <ul class="mt-5 space-y-5">
                        @forelse ($activities as $activity)
                        <li class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <x-icon :name="$activity['icon']" class="h-5 w-5 {{ $activity['color'] }}" />
                                @if (!$loop->last)
                                <span class="mt-1 w-px flex-1 bg-gray-100"></span>
                                @endif
                            </div>
                            <div class="pb-1">
                                <p class="text-sm text-gray-700">{{ $activity['text'] }}</p>
                                <p class="text-xs text-gray-400">{{ $activity['time'] }}</p>
                            </div>
                        </li>
                        @empty
                        <li class="text-sm text-gray-400">No activity yet.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Today's Sessions — real AttendanceSession rows, queried directly (not through Attendance) --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm xl:col-span-1">
                    <h3 class="text-base font-semibold text-gray-900">Today's Sessions</h3>
                    <p class="text-sm text-gray-500">Attendance sessions opened today, across all teachers</p>
                    <div class="mt-5 space-y-3">
                        @forelse ($todaysSessions as $session)
                        <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 transition-colors duration-150 hover:bg-gray-50">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $session['title'] }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $session['teacher'] }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-xs font-medium text-gray-700">{{ $session['time'] }}</p>
                                <span class="mt-0.5 inline-block rounded-full px-2 py-0.5 text-[11px] font-semibold
                                    {{ $session['status'] === 'Open' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $session['status'] }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400">No sessions opened today yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        </x-layouts.admin>