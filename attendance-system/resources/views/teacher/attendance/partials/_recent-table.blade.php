{{--
    teacher/attendance/partials/_recent-table.blade.php

    Included from start.blade.php. Unlike the tables in the Admin
    monitoring modules, this one has no Blade/PHP data behind it at
    all — `recentRecords` only exists in the current browser session's
    Alpine state (records added as the teacher processes students), so
    the whole table is built with <template x-for> instead of @foreach.
--}}
<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="border-b border-gray-100 p-5">
        <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
        <p class="text-sm text-gray-500">Latest records from this session</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                    <th class="px-5 py-3 font-medium">Student</th>
                    <th class="px-5 py-3 font-medium">Student No.</th>
                    <th class="px-5 py-3 font-medium">Time</th>
                    <th class="px-5 py-3 font-medium">QR Status</th>
                    <th class="px-5 py-3 font-medium">Face Status</th>
                    <th class="px-5 py-3 font-medium">Attendance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <template x-for="record in recentRecords" :key="record.id">
                    <tr class="transition-colors duration-150 hover:bg-gray-50">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img
                                    x-bind:src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(record.name) + '&background=E2E8F0&color=334155'"
                                    class="h-8 w-8 rounded-full"
                                    alt=""
                                >
                                <span class="font-medium text-gray-900" x-text="record.name"></span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600" x-text="record.number"></td>
                        <td class="px-5 py-3.5 text-gray-600" x-text="record.time"></td>
                        <td class="px-5 py-3.5">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                x-bind:class="{
                                    'bg-emerald-50 text-emerald-700': record.qr === 'Verified',
                                    'bg-amber-50 text-amber-700': record.qr === 'Expired',
                                    'bg-red-50 text-red-700': record.qr === 'Invalid' || record.qr === 'Already Used',
                                }"
                                x-text="record.qr"
                            ></span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                x-bind:class="{
                                    'bg-emerald-50 text-emerald-700': record.face === 'Matched',
                                    'bg-amber-50 text-amber-700': record.face === 'Mismatch',
                                    'bg-red-50 text-red-700': record.face === 'Not Recognized',
                                    'bg-gray-100 text-gray-500': !record.face,
                                }"
                                x-text="record.face || '—'"
                            ></span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                x-bind:class="record.status === 'Present' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                                x-text="record.status"
                            ></span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- Empty state --}}
        <div x-show="recentRecords.length === 0" class="flex flex-col items-center justify-center px-5 py-16 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                <x-icon name="inbox" class="h-8 w-8 text-gray-300" />
            </div>
            <p class="mt-4 text-sm font-semibold text-gray-700">No attendance has been recorded yet.</p>
            <p class="mt-1 text-sm text-gray-400">Start the session and scan a student's QR code to begin.</p>
        </div>
    </div>
</div>
