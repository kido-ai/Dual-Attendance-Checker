{{--
    teacher/attendance/partials/_details-modal.blade.php (Attendance History)

    Included from index.blade.php. Reads `targetRecord` from the parent
    page's Alpine scope (set by _table.blade.php's "View Details" button
    via @js($record)) — same pattern as the Admin Attendance Records
    module's details modal. No Teacher field here since the record is
    already scoped to the logged-in teacher.
--}}
<x-modal show="showDetailsModal" max-width="lg">
    <template x-if="targetRecord">
        <div>
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Attendance Details</h3>
                    <p class="text-sm text-gray-500">Full verification breakdown for this session</p>
                </div>
                <button type="button" @click="showDetailsModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="max-h-[70vh] space-y-5 overflow-y-auto px-6 py-6">

                {{-- Student profile --}}
                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-4">
                    <img
                        x-bind:src="targetRecord.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(targetRecord.student) + '&background=059669&color=fff'"
                        class="h-12 w-12 rounded-full object-cover"
                        alt="Student">
                    <div>
                        <p class="text-sm font-semibold text-gray-900" x-text="targetRecord.student"></p>
                        <p class="text-xs text-gray-500" x-text="'Student No. ' + targetRecord.student_number"></p>
                    </div>
                </div>

                {{-- Session info --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-400">Attendance Date</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="targetRecord.date"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Attendance Time</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="targetRecord.time"></p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400">Remarks</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="targetRecord.remarks"></p>
                    </div>
                </div>

                {{-- Verification results --}}
                <div class="space-y-3 rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-sm text-gray-600">
                            <x-icon name="qr-code" class="h-4 w-4 text-gray-400" /> QR Verification Result
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                            x-bind:class="{
                                'bg-emerald-50 text-emerald-700': targetRecord.qr_status === 'Verified',
                                'bg-amber-50 text-amber-700': targetRecord.qr_status === 'Expired',
                                'bg-red-50 text-red-700': targetRecord.qr_status === 'Invalid',
                            }"
                            x-text="targetRecord.qr_status"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-sm text-gray-600">
                            <x-icon name="video-camera" class="h-4 w-4 text-gray-400" /> Face Verification Result
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                            x-bind:class="{
                                'bg-emerald-50 text-emerald-700': targetRecord.face_status === 'Matched',
                                'bg-red-50 text-red-700': targetRecord.face_status === 'Mismatch',
                                'bg-gray-100 text-gray-500': targetRecord.face_status === 'Not Verified',
                            }"
                            x-text="targetRecord.face_status"></span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <x-icon name="clipboard-check" class="h-4 w-4 text-gray-400" /> Final Attendance Status
                        </span>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                            x-bind:class="{
                                'bg-emerald-50 text-emerald-700': targetRecord.status === 'Present',
                                'bg-red-50 text-red-700': targetRecord.status === 'Absent' || targetRecord.status === 'Rejected',
                            }"
                            x-text="targetRecord.status"></span>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end border-t border-gray-100 px-6 py-4">
                <button type="button" @click="showDetailsModal = false" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </template>
</x-modal>