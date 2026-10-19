<x-layouts.student
    title="Attendance History"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Attendance History']]">
    <div
        x-data="{ showDetailsModal: false, targetRecord: null }"
        class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Attendance History</h2>
            <p class="mt-1 text-sm text-gray-500">View your attendance records verified through QR Code and Face Recognition.</p>
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

        {{-- Top action bar: search, filters, export/print --}}
        @include('student.attendance.partials._filters', [
        'attendanceStatuses' => $attendanceStatuses,
        'qrStatuses' => $qrStatuses,
        'faceStatuses' => $faceStatuses,
        'months' => $months,
        ])

        {{-- Main table + Recent Attendance summary card --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('student.attendance.partials._table', ['records' => $records])
            </div>

            {{-- Recent Attendance summary card --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm xl:col-span-1">
                <h3 class="text-sm font-semibold text-gray-900">Recent Attendance</h3>
                <p class="text-xs text-gray-500">A quick snapshot of your latest activity</p>

                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                        <span class="flex items-center gap-2 text-sm text-gray-600">
                            <x-icon name="calendar-days" class="h-4 w-4 text-gray-400" /> Last Attendance Date
                        </span>
                        <span class="text-sm font-semibold text-gray-900">{{ $summary['last_date'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                        <span class="flex items-center gap-2 text-sm text-gray-600">
                            <x-icon name="shield-check" class="h-4 w-4 text-gray-400" /> Last Successful Verification
                        </span>
                    </div>
                    <p class="-mt-1 px-1 text-xs text-gray-500">{{ $summary['last_verification'] }}</p>

                    <div class="rounded-xl border border-gray-100 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sm text-gray-600">
                                <x-icon name="chart-bar" class="h-4 w-4 text-gray-400" /> Attendance Rate
                            </span>
                            <span class="text-sm font-semibold text-emerald-600">{{ $summary['attendance_rate'] }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $summary['attendance_rate'] }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance details modal --}}
        @include('student.attendance.partials._details-modal')
    </div>
</x-layouts.student>