{{--
    student/dashboard.blade.php

    Consumes the payload supplied by Student\DashboardController and
    renders a student-facing dashboard without relying on stale mock data.
--}}
@php
$greeting = now()->hour < 12 ? 'Good Morning' : (now()->hour < 18 ? 'Good Afternoon' : 'Good Evening' );
        $toneCycle=['emerald', 'blue' , 'indigo' , 'amber' ];

        $profile=[ 'name'=> $student['name'] ?? 'Student',
        'course' => $student['course'] ?? '—',
        'year' => $student['year_level'] ?? '—',
        'section' => $student['section'] ?? '—',
        'student_number' => $student['student_number'] ?? '—',
        ];

        $academicSummary = [
        ['icon' => 'academic-cap', 'label' => 'Total Attendance', 'value' => $attendanceStats['total_attendance'] ?? 0],
        ['icon' => 'check-circle', 'label' => 'Present', 'value' => $attendanceStats['present_count'] ?? 0],
        ['icon' => 'clock', 'label' => 'Late', 'value' => $attendanceStats['late_count'] ?? 0],
        ['icon' => 'chart-bar', 'label' => 'Attendance %', 'value' => ($attendanceStats['attendance_percentage'] ?? 0) . '%'],
        ];

        $qrTokenData = [
        'status' => $qrToken['status'] ?? 'Not Generated',
        'expires' => $qrToken['expires_at'] ? 'Expires ' . $qrToken['expires_at'] : 'Not available yet',
        ];

        $faceProfileData = [
        'status' => $faceProfile['status'] ?? 'Not Enrolled',
        'enrolled_at' => $faceProfile['last_updated'] ? 'Updated ' . $faceProfile['last_updated'] : 'Not enrolled yet',
        ];

        $attendanceRows = collect($recentAttendance ?? [])->map(function ($row) {
        return [
        'date' => $row['date'] ?? '-',
        'teacher' => $row['teacher'] ?? 'Unknown Teacher',
        'time' => $row['time_in'] ?? '-',
        'qr_status' => $row['qr_status'] ?? 'Unknown',
        'face_status' => $row['face_status'] ?? 'Unknown',
        'status' => $row['attendance_status'] ?? 'Unknown',
        ];
        })->values()->all();

        $quickActions = [
        ['icon' => 'qr-code', 'title' => 'View My QR Code', 'desc' => 'Open your current attendance QR token'],
        ['icon' => 'video-camera', 'title' => 'Face Verification', 'desc' => 'Check your enrollment and verification status'],
        ['icon' => 'clipboard-check', 'title' => 'Attendance History', 'desc' => 'Review recent class check-ins and outcomes'],
        ];

        $notificationsList = $notifications ?? [];
        @endphp

        <x-layouts.student
            title="Dashboard"
            :breadcrumbs="[['label' => 'Student'], ['label' => 'Dashboard']]">
            <div class="mx-auto max-w-7xl space-y-6">
                {{-- Welcome section --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                        <div>
                            <p class="text-sm font-medium text-emerald-600">{{ $greeting }},</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">{{ $profile['name'] }}</h2>
                            <p class="mt-2 max-w-xl text-sm text-gray-500">
                                Here's your attendance snapshot for {{ now()->format('l, F j, Y') }}.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:flex sm:items-center sm:gap-6">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-400">Course</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $profile['course'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-400">Year</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $profile['year'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-400">Section</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $profile['section'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-400">Student No.</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $profile['student_number'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Statistics cards --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($academicSummary as $i => $stat)
                    <x-stat-card
                        :icon="$stat['icon']"
                        :tone="$toneCycle[$i % count($toneCycle)]"
                        :label="$stat['label']"
                        :value="$stat['value']" />
                    @endforeach
                </div>

                {{-- QR Code + Face Verification status --}}
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">My QR Code</h3>
                            <x-status-badge :status="$qrTokenData['status']" />
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50">
                                <x-icon name="qr-code" class="h-8 w-8 text-gray-300" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">{{ $qrTokenData['expires'] }}</p>
                                <a href="{{ route('student.qr.show') }}" class="mt-1 inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                                    View full QR code
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">Face Verification</h3>
                            <x-status-badge :status="$faceProfileData['status']" />
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50">
                                <x-icon name="video-camera" class="h-8 w-8 text-gray-300" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Enrolled</p>
                                <p class="text-sm text-gray-600">{{ $faceProfileData['enrolled_at'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    {{-- Left: recent attendance --}}
                    <div class="xl:col-span-2">
                        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                            <div class="border-b border-gray-100 p-5">
                                <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
                                <p class="text-sm text-gray-500">Your last 5 recorded check-ins</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[720px] text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                            <th class="px-5 py-3 font-medium">Date</th>
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
                                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $row['date'] }}</td>
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
                    </div>
                </div>
            </div>
        </x-layouts.student>