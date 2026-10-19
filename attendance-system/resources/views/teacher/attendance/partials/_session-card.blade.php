{{--
    teacher/attendance/partials/_session-card.blade.php

    Included from start.blade.php. Reads/writes the parent page's Alpine
    scope (sessionOpen, showStartModal, showEndModal) — a plain @include
    partial so Alpine state cascades through the DOM.

    The clock is a small self-contained Alpine widget (x-init + setInterval)
    since it doesn't need to talk to the rest of the page.
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
    <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center">

        <div class="flex items-center gap-4">
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
                x-bind:class="sessionOpen ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400'"
            >
                <x-icon name="qr-code" class="h-7 w-7" />
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-lg font-semibold text-gray-900">Attendance Session</h3>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                        x-bind:class="sessionOpen ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                    >
                        <span class="h-1.5 w-1.5 rounded-full" x-bind:class="sessionOpen ? 'bg-emerald-600 animate-pulse' : 'bg-gray-400'"></span>
                        <span x-text="sessionOpen ? 'Open' : 'Closed'"></span>
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Prof. Elena Villanueva &middot; Database Systems, BSIT 3-A</p>
            </div>
        </div>

        <div
            class="flex items-center gap-6 border-y border-gray-100 py-3 sm:border-0 sm:py-0"
            x-data="{ now: new Date() }"
            x-init="setInterval(() => now = new Date(), 1000)"
        >
            <div>
                <p class="text-xs text-gray-400">Current Date</p>
                <p class="text-sm font-semibold text-gray-800" x-text="now.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })"></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Current Time</p>
                <p class="text-sm font-semibold text-gray-800" x-text="now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })"></p>
            </div>
        </div>

        <div class="flex gap-3">
            <button
                type="button"
                x-show="!sessionOpen"
                @click="showStartModal = true"
                class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700"
            >
                <x-icon name="play" class="h-4 w-4" />
                Start Attendance
            </button>
            <button
                type="button"
                x-show="sessionOpen"
                x-cloak
                @click="showEndModal = true"
                class="flex items-center gap-2 rounded-xl border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition-colors duration-150 hover:bg-red-50"
            >
                <x-icon name="stop-circle" class="h-4 w-4" />
                End Attendance
            </button>
        </div>
    </div>
</div>
