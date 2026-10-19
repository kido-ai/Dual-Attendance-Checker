{{--
    teacher/dashboard.blade.php

    Consumes real data from Teacher\DashboardController. No QR/Face
    preview widgets here (that's the real "Take Attendance" page's job,
    not the dashboard's), no fake schedule. "Today's Sessions" is real,
    queried directly from AttendanceSession for this teacher.

    NOTE: this file intentionally uses plain ASCII punctuation only
    (no em-dashes, no smart quotes). A previous version of this file
    used Unicode em-dashes in the header comment and caused:
        ParseError: syntax error, unexpected token "endif"
    All Blade @forelse/@empty/@endforelse and @foreach/@endforeach pairs
    were verified balanced by simulating Blade's actual compiled output
    (forelse -> foreach ... endforeach; if(empty): ... endif;) -- the
    directive nesting was never actually wrong. The most likely cause is
    the Unicode em-dash characters getting corrupted in transit on a
    Windows filesystem/editor that doesn't preserve UTF-8 cleanly, which
    can shift byte boundaries in ways that don't show up in a plain-text
    read but do break the PHP tokenizer. Removing them entirely is the
    safe, defensive fix regardless of exactly where the corruption
    happened.
--}}
@php
$greeting = now()->hour < 12 ? 'Good Morning' : (now()->hour < 18 ? 'Good Afternoon' : 'Good Evening' );
        @endphp

        <x-layouts.teacher
        title="Dashboard"
        :breadcrumbs="[['label' => 'Teacher'], ['label' => 'Dashboard']]">
        <div class="mx-auto max-w-7xl space-y-6">

            {{-- Welcome section --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-medium text-emerald-600">{{ $greeting }},</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">{{ $teacher->full_name }}</h2>
                <p class="mt-2 max-w-xl text-sm text-gray-500">
                    {{ now()->format('l, F j, Y') }} &middot; here is your attendance activity so far today.
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

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                {{-- Left: attendance summary + recent attendance + student list --}}
                <div class="space-y-6 xl:col-span-2">

                    {{-- Today's attendance summary --}}
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Today's Attendance</h3>
                                <p class="text-sm text-gray-500">Present vs. rejected check-ins recorded today</p>
                            </div>
                            <a
                                href="{{ route('teacher.attendance') }}"
                                class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                                <x-icon name="play" class="h-4 w-4" />
                                Take Attendance
                            </a>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-emerald-50 p-4 text-center">
                                <p class="text-2xl font-semibold text-emerald-700">{{ $attendanceSummary['present'] }}</p>
                                <p class="mt-0.5 text-xs font-medium text-emerald-600">Present</p>
                            </div>
                            <div class="rounded-xl bg-red-50 p-4 text-center">
                                <p class="text-2xl font-semibold text-red-700">{{ $attendanceSummary['rejected'] }}</p>
                                <p class="mt-0.5 text-xs font-medium text-red-600">Rejected</p>
                            </div>
                        </div>
                    </div>

                    {{-- Recent attendance table --}}
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-5">
                            <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
                            <p class="text-sm text-gray-500">Your last 5 recorded check-ins</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                        <th class="px-5 py-3 font-medium">Student</th>
                                        <th class="px-5 py-3 font-medium">Time</th>
                                        <th class="px-5 py-3 font-medium">QR Status</th>
                                        <th class="px-5 py-3 font-medium">Face Status</th>
                                        <th class="px-5 py-3 font-medium">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse ($recentAttendance as $record)
                                    <tr class="transition-colors duration-150 hover:bg-gray-50">
                                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ $record->student?->full_name ?? 'Unknown Student' }}</td>
                                        <td class="px-5 py-3.5 text-gray-600">{{ $record->time_in?->format('g:i A') ?? '-' }}</td>
                                        <td class="px-5 py-3.5"><x-status-badge :status="ucfirst($record->qr_status)" /></td>
                                        <td class="px-5 py-3.5"><x-status-badge :status="ucfirst($record->face_status)" /></td>
                                        <td class="px-5 py-3.5"><x-status-badge :status="ucfirst($record->attendance_status)" /></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No attendance recorded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Today's students at a glance --}}
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-5">
                            <h3 class="text-base font-semibold text-gray-900">Today's Students</h3>
                            <p class="text-sm text-gray-500">Everyone checked in today, with their attendance rate</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[560px] text-left text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                        <th class="px-5 py-3 font-medium">Student</th>
                                        <th class="px-5 py-3 font-medium">Student No.</th>
                                        <th class="px-5 py-3 font-medium">Course</th>
                                        <th class="px-5 py-3 font-medium">Section</th>
                                        <th class="px-5 py-3 font-medium">Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse ($studentList as $student)
                                    @php
                                    $rateColor = $student['rate'] >= 90 ? 'bg-emerald-500' : ($student['rate'] >= 50 ? 'bg-amber-500' : 'bg-red-500');
                                    @endphp
                                    <tr class="transition-colors duration-150 hover:bg-gray-50">
                                        <td class="px-5 py-3 font-medium text-gray-900">{{ $student['name'] }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $student['number'] }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $student['course'] }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $student['section'] }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100">
                                                    <div class="h-full rounded-full {{ $rateColor }}" style="width: {{ $student['rate'] }}%"></div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-600">{{ $student['rate'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No students checked in yet today.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Right: today's sessions + notifications --}}
                <div class="space-y-6">

                    {{-- Today's Sessions - real AttendanceSession rows for this teacher --}}
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">Today's Sessions</h3>
                        <p class="text-sm text-gray-500">Attendance sessions you have opened today</p>
                        <div class="mt-4 space-y-3">
                            @forelse ($todaysSessions as $session)
                            <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $session['title'] }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $session['start'] }} &ndash; {{ $session['end'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $session['is_open'] ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $session['status'] }}
                                </span>
                            </div>
                            @empty
                            <p class="text-sm text-gray-400">No sessions opened today yet.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Notifications --}}
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">Notifications</h3>
                        <p class="text-sm text-gray-500">Recent check-ins from your sessions</p>
                        <ul class="mt-4 space-y-4">
                            @forelse ($notifications as $note)
                            <li class="flex gap-3">
                                <x-icon :name="$note['icon']" class="mt-0.5 h-5 w-5 shrink-0 {{ $note['color'] }}" />
                                <div>
                                    <p class="text-sm text-gray-700">{{ $note['text'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $note['time'] }}</p>
                                </div>
                            </li>
                            @empty
                            <li class="text-sm text-gray-400">No notifications yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        </x-layouts.teacher>