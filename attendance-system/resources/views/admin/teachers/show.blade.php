@php
$profilePhotoUrl = $teacher->profile_photo
? Storage::disk('public')->url($teacher->profile_photo)
: 'https://ui-avatars.com/api/?name=' . urlencode($teacher->full_name) . '&background=2563eb&color=fff&size=128';

$recentAttendances = $teacher->attendances ?? collect();
$quickStats = [
['icon' => 'calendar-days', 'tone' => 'emerald', 'label' => 'Recent Records', 'value' => (string) $recentAttendances->count()],
['icon' => 'check-circle', 'tone' => 'blue', 'label' => 'Present', 'value' => (string) $recentAttendances->where('attendance_status', 'present')->count()],
['icon' => 'clock', 'tone' => 'indigo', 'label' => 'Late', 'value' => (string) $recentAttendances->where('attendance_status', 'late')->count()],
['icon' => 'exclamation-triangle', 'tone' => 'amber', 'label' => 'Absent', 'value' => (string) $recentAttendances->where('attendance_status', 'absent')->count()],
];
@endphp

<x-layouts.admin
    title="Teacher Profile"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Teacher Management'], ['label' => $teacher->full_name]]">
    <div class="mx-auto max-w-6xl space-y-6">

        <a href="{{ route('admin.teachers.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
            <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
            Back to Teacher Management
        </a>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-1">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-sm">
                    <img src="{{ $profilePhotoUrl }}"
                        alt="{{ $teacher->full_name }}"
                        class="mx-auto h-28 w-28 rounded-2xl object-cover shadow-sm">
                    <h2 class="mt-4 text-lg font-semibold text-gray-900">{{ $teacher->full_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $teacher->employee_id }}</p>

                    <div class="mt-3 flex items-center justify-center gap-2">
                        <x-status-badge :status="ucfirst($teacher->status ?? 'active')" />
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.teachers.edit', $teacher) }}"
                            class="flex flex-col items-center gap-1 rounded-xl border border-gray-100 py-3 text-gray-500 transition-colors duration-150 hover:bg-gray-50 hover:text-indigo-600">
                            <x-icon name="pencil" class="h-5 w-5" />
                            <span class="text-xs font-medium">Edit</span>
                        </a>
                        <form method="POST"
                            action="{{ route('admin.teachers.archive', $teacher) }}"
                            class="w-full"
                            onsubmit="return confirm('Archive {{ addslashes($teacher->full_name) }}?')">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                class="flex w-full flex-col items-center gap-1 rounded-xl border border-gray-100 py-3 text-gray-500 transition-colors duration-150 hover:bg-gray-50 hover:text-amber-600">
                                <x-icon name="archive-box" class="h-5 w-5" />
                                <span class="text-xs font-medium">Archive</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Quick Information</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-gray-500">
                                <x-icon name="identification" class="h-4 w-4 text-gray-400" /> Employee ID
                            </span>
                            <span class="font-medium text-gray-800">{{ $teacher->employee_id }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-gray-500">
                                <x-icon name="envelope" class="h-4 w-4 text-gray-400" /> Email
                            </span>
                            <span class="truncate font-medium text-gray-800">{{ $teacher->email ?? $teacher->user?->email ?? 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-gray-500">
                                <x-icon name="phone" class="h-4 w-4 text-gray-400" /> Phone
                            </span>
                            <span class="font-medium text-gray-800">{{ $teacher->phone ?? 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                            <span class="text-gray-500">Status</span>
                            <x-status-badge :status="ucfirst($teacher->status ?? 'active')" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($quickStats as $stat)
                    <x-stat-card :icon="$stat['icon']"
                        :tone="$stat['tone']"
                        :label="$stat['label']"
                        :value="$stat['value']" />
                    @endforeach
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Teacher Information</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs text-gray-400">Full Name</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $teacher->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Employee ID</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $teacher->employee_id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Email</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $teacher->email ?? $teacher->user?->email ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Phone</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800">{{ $teacher->phone ?? 'Not set' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Recent Attendance</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                    <th class="px-3 py-2 font-medium">Date</th>
                                    <th class="px-3 py-2 font-medium">Time</th>
                                    <th class="px-3 py-2 font-medium">QR</th>
                                    <th class="px-3 py-2 font-medium">Face</th>
                                    <th class="px-3 py-2 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($recentAttendances as $attendance)
                                <tr>
                                    <td class="px-3 py-2 text-gray-700">{{ $attendance->time_in?->format('M j, Y') }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $attendance->time_in?->format('g:i A') }}</td>
                                    <td class="px-3 py-2"><x-status-badge :status="ucfirst($attendance->qr_status ?? 'pending')" /></td>
                                    <td class="px-3 py-2"><x-status-badge :status="ucfirst($attendance->face_status ?? 'pending')" /></td>
                                    <td class="px-3 py-2"><x-status-badge :status="ucfirst($attendance->attendance_status ?? 'pending')" /></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-400">
                                        No attendance records yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>