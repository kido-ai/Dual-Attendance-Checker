{{--
    teacher/attendance/partials/_statistics.blade.php

    Included from start.blade.php. All values are computed live from
    `recentRecords` via inline Alpine expressions — no Blade data behind
    this at all, same reasoning as _recent-table.blade.php.
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-gray-900">Session Statistics</h3>
    <p class="text-xs text-gray-500">Live totals for this attendance session</p>

    <div class="mt-5 space-y-3">
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-gray-600">
                <x-icon name="users" class="h-4 w-4 text-gray-400" /> Students Processed
            </span>
            <span class="text-sm font-semibold text-gray-900" x-text="recentRecords.length"></span>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-emerald-700">
                <x-icon name="check-circle" class="h-4 w-4" /> Present
            </span>
            <span class="text-sm font-semibold text-emerald-700" x-text="recentRecords.filter(r => r.status === 'Present').length"></span>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-red-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-red-700">
                <x-icon name="x-circle" class="h-4 w-4" /> Rejected
            </span>
            <span class="text-sm font-semibold text-red-700" x-text="recentRecords.filter(r => r.status === 'Rejected').length"></span>
        </div>
        <div class="rounded-xl border border-gray-100 px-4 py-3">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-sm text-gray-600">
                    <x-icon name="chart-bar" class="h-4 w-4 text-gray-400" /> Success Rate
                </span>
                <span
                    class="text-sm font-semibold text-gray-900"
                    x-text="recentRecords.length ? Math.round((recentRecords.filter(r => r.status === 'Present').length / recentRecords.length) * 100) + '%' : '—'"
                ></span>
            </div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100">
                <div
                    class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                    x-bind:style="'width: ' + (recentRecords.length ? Math.round((recentRecords.filter(r => r.status === 'Present').length / recentRecords.length) * 100) : 0) + '%'"
                ></div>
            </div>
        </div>
    </div>
</div>
