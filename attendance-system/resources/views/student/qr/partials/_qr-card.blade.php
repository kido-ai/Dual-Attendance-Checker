{{--
    student/qr/partials/_qr-card.blade.php

    Included from index.blade.php. Expects $student (array) and $qr
    (array with status/expiration_status) in scope. UI only — no QR
    generation; the code is created and managed entirely by the admin.
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-10">
    <div class="mx-auto flex max-w-sm flex-col items-center text-center">

        @if (!empty($qr['image_url']))
        <div class="flex h-56 w-56 items-center justify-center rounded-2xl bg-white p-2 shadow-inner ring-1 ring-gray-100 sm:h-64 sm:w-64">
            <img src="{{ $qr['image_url'] }}" alt="Student QR Code" class="h-full w-full rounded-xl">
        </div>
        @else
        <div id="student-qr-inline"
            data-token="{{ $qr['token'] }}"
            class="flex h-56 w-56 items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/60 sm:h-64 sm:w-64">
            <span class="text-xs text-gray-400">Rendering QR…</span>
        </div>
        @endif

        {{-- Student profile --}}
        <div class="mt-6 flex flex-col items-center">
            <img
                src="{{ $student['photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($student['name']) . '&background=059669&color=fff&size=96' }}"
                alt="{{ $student['name'] }}"
                class="h-16 w-16 rounded-2xl object-cover shadow-sm">
            <h3 class="mt-3 text-lg font-semibold text-gray-900">{{ $student['name'] }}</h3>
            <p class="text-sm text-gray-500">{{ $student['student_number'] }}</p>
        </div>

        {{-- Academic info --}}
        <div class="mt-5 grid w-full grid-cols-3 gap-2 rounded-xl bg-gray-50 p-3">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-gray-400">Course</p>
                <p class="mt-0.5 text-xs font-semibold text-gray-800">{{ $student['course'] }}</p>
            </div>
            <div class="border-x border-gray-200">
                <p class="text-[11px] uppercase tracking-wide text-gray-400">Year Level</p>
                <p class="mt-0.5 text-xs font-semibold text-gray-800">{{ $student['year'] }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wide text-gray-400">Section</p>
                <p class="mt-0.5 text-xs font-semibold text-gray-800">{{ $student['section'] }}</p>
            </div>
        </div>

        {{-- Status badges --}}
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            <span class="text-xs text-gray-400">QR Code Status</span>
            <x-status-badge :status="$qr['status']" />
            <span class="mx-1 text-gray-200">&middot;</span>
            <span class="text-xs text-gray-400">Expiration</span>
            <x-status-badge :status="$qr['expiration_status']" />
        </div>

        {{-- Action buttons --}}
        <div class="mt-6 flex w-full flex-col gap-3 sm:flex-row">
            <a
                href="{{ route('student.qr.download') }}"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                <x-icon name="arrow-down-tray" class="h-5 w-5" />
                Download QR Code
            </a>
            <button
                type="button"
                @click="fullscreenOpen = true"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition-colors duration-150 hover:bg-gray-50">
                <x-icon name="arrows-pointing-out" class="h-5 w-5" />
                View Full Screen
            </button>
        </div>
    </div>
</div>