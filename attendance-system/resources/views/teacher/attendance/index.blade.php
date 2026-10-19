<x-layouts.teacher
    title="Attendance History"
    :breadcrumbs="[['label' => 'Teacher'], ['label' => 'Attendance History']]">
    <div
        x-data="{ showDetailsModal: false, targetRecord: null }"
        x-init="setInterval(() => { if (document.visibilityState === 'visible') window.location.reload(); }, 30000)"
        class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Attendance History</h2>
            <p class="mt-1 text-sm text-gray-500">Review attendance records verified through QR Code and Face Recognition.</p>
        </div>

        {{-- Statistics cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
            <x-stat-card
                :icon="$stat['icon']"
                :tone="$stat['tone']"
                :label="$stat['label']"
                :value="$stat['value']"
                :trend="$stat['trend']"
                :trend-value="$stat['trendValue']" />
            @endforeach
        </div>

        {{-- Top action bar: search, filters, refresh --}}
        @include('teacher.attendance.partials._filters', [
        'attendanceStatuses' => $attendanceStatuses,
        'qrStatuses' => $qrStatuses,
        'faceStatuses' => $faceStatuses,
        'filters' => $filters,
        ])

        {{-- Main table + Recent Activity side panel --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('teacher.attendance.partials._table', ['records' => $records])
            </div>

            {{-- Recent Activity side card --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm xl:col-span-1">
                <h3 class="text-sm font-semibold text-gray-900">Recent Attendance</h3>
                <p class="text-xs text-gray-500">Your latest recorded sessions</p>
                <ul class="mt-4 space-y-4">
                    @forelse ($recentActivity as $activity)
                    <li class="flex items-center justify-between gap-3 border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-2.5">
                            @php
                            $activityPhoto = $activity['photo'] ?? null;
                            @endphp
                            <img
                                src="{{ $activityPhoto ?: 'https://ui-avatars.com/api/?name=' . urlencode($activity['student']) . '&background=E2E8F0&color=334155' }}"
                                alt="{{ $activity['student'] }}"
                                class="h-8 w-8 shrink-0 rounded-full object-cover">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $activity['student'] }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                        <x-status-badge :status="$activity['status']" class="shrink-0" />
                    </li>
                    @empty
                    <li class="py-4 text-sm text-gray-400">No recent attendance records yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Attendance details modal --}}
        @include('teacher.attendance.partials._details-modal')
    </div>
</x-layouts.teacher>