{{--
    teacher/attendance/partials/_result-card.blade.php

    Included from start.blade.php. Shown once currentStep === 4. Reads
    currentStudent / qrResult / faceResult / attendanceOutcome from the
    parent page's Alpine scope — entirely Alpine-driven since the
    "student" being displayed only exists in client-side demo state.
--}}
<div
    x-show="currentStep === 4 && attendanceOutcome"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="rounded-2xl border p-6 shadow-sm sm:p-8"
    x-bind:class="attendanceOutcome === 'Present' ? 'border-emerald-100 bg-emerald-50/40' : 'border-red-100 bg-red-50/40'"
>
    <div class="flex flex-col items-center gap-6 text-center sm:flex-row sm:text-left">

        {{-- Large result icon --}}
        <div
            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl"
            x-bind:class="attendanceOutcome === 'Present' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'"
        >
            <template x-if="attendanceOutcome === 'Present'">
                <x-icon name="check-circle" class="h-11 w-11" />
            </template>
            <template x-if="attendanceOutcome === 'Rejected'">
                <x-icon name="x-circle" class="h-11 w-11" />
            </template>
        </div>

        <div class="flex-1">
            <p
                class="text-lg font-semibold"
                x-bind:class="attendanceOutcome === 'Present' ? 'text-emerald-700' : 'text-red-700'"
                x-text="attendanceOutcome === 'Present' ? 'Attendance Recorded' : 'Attendance Rejected'"
            ></p>

            <div class="mt-3 flex flex-col items-center gap-4 sm:flex-row sm:items-center">
                <img
                    x-bind:src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(currentStudent?.name ?? '') + '&background=E2E8F0&color=334155'"
                    class="h-14 w-14 rounded-full"
                    alt="Student"
                >
                <div>
                    <p class="text-base font-semibold text-gray-900" x-text="currentStudent?.name"></p>
                    <p class="text-sm text-gray-500" x-text="'Student No. ' + (currentStudent?.number ?? '')"></p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                    <p class="text-xs text-gray-400">Time</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800" x-text="attendanceTime"></p>
                </div>
                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                    <p class="text-xs text-gray-400">QR Status</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800" x-text="qrResult ?? '—'"></p>
                </div>
                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                    <p class="text-xs text-gray-400">Face Status</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800" x-text="faceResult ?? '—'"></p>
                </div>
                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                    <p class="text-xs text-gray-400">Attendance</p>
                    <p
                        class="mt-0.5 text-sm font-semibold"
                        x-bind:class="attendanceOutcome === 'Present' ? 'text-emerald-600' : 'text-red-600'"
                        x-text="attendanceOutcome"
                    ></p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-center sm:justify-start">
        <button
            type="button"
            @click="scanNext()"
            class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700"
        >
            <x-icon name="arrow-right" class="h-4 w-4" />
            Scan Next Student
        </button>
    </div>
</div>
