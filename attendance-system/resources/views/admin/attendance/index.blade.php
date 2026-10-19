<x-layouts.admin
    title="Attendance Records"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Attendance Records']]">
    <div
        x-data="{ showDetailsModal: false, targetRecord: null }"
        class="mx-auto max-w-7xl space-y-6">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Attendance Records</h2>
            <p class="mt-1 text-sm text-gray-500">Monitor and review all attendance records verified through QR Code and Face Recognition.</p>
        </div>

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

        @include('admin.attendance.partials._filters', [
        'teachers' => $teachers,
        'attendanceStatuses' => $attendanceStatuses,
        'qrStatuses' => $qrStatuses,
        'faceStatuses' => $faceStatuses,
        ])

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('admin.attendance.partials._table', ['records' => $records])
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm xl:col-span-1">
                <h3 class="text-sm font-semibold text-gray-900">Recent Activity</h3>
                <p class="text-xs text-gray-500">Latest attendance records as they come in</p>
                <ul class="mt-4 space-y-4">
                    @foreach ($records->take(5) as $activity)
                    <li class="flex items-center justify-between gap-3 border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <img
                                src="https://ui-avatars.com/api/?name={{ urlencode($activity['student']) }}&background=E2E8F0&color=334155"
                                alt="{{ $activity['student'] }}"
                                class="h-8 w-8 shrink-0 rounded-full">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $activity['student'] }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $activity['teacher'] }} &middot; {{ $activity['time'] }}</p>
                            </div>
                        </div>
                        <x-status-badge :status="$activity['status']" class="shrink-0" />
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @include('admin.attendance.partials._details-modal')
    </div>
</x-layouts.admin>