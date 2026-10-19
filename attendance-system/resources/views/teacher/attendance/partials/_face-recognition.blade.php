{{--
    teacher/attendance/partials/_face-recognition.blade.php

    Included from start.blade.php. UI only — no real camera or face
    recognition. Reads/writes the parent page's Alpine scope
    (faceScanning, faceResult, faceConfidence, currentStep) and calls its
    captureFace()/retryFace()/cancelFace() methods. Only shown once the
    QR step has succeeded (currentStep >= 3).
--}}
<div
    x-show="currentStep >= 3"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
>
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Step 3 &middot; Face Recognition</h3>
            <p class="text-sm text-gray-500">Ask the student to look directly at the camera.</p>
        </div>
        <span
            class="rounded-full px-2.5 py-1 text-xs font-semibold"
            x-bind:class="faceScanning ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500'"
        >
            <span x-text="faceScanning ? 'Analyzing...' : (faceResult ? 'Capture complete' : 'Waiting')"></span>
        </span>
    </div>

    {{-- Camera preview placeholder --}}
    <div class="relative mt-5 flex aspect-video items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60">
        <div class="text-center" x-show="!faceScanning">
            <x-icon name="video-camera" class="mx-auto h-12 w-12 text-gray-300" />
            <p class="mt-2 text-sm text-gray-400">Camera preview placeholder</p>
        </div>
        <div class="text-center" x-show="faceScanning" x-cloak>
            <x-icon name="video-camera" class="mx-auto h-12 w-12 animate-pulse text-indigo-400" />
            <p class="mt-2 text-sm font-medium text-indigo-500">Analyzing face&hellip;</p>
        </div>
        <div x-show="faceScanning" x-cloak class="pointer-events-none absolute inset-10 rounded-full border-2 border-indigo-300/70"></div>
    </div>

    {{-- Face match result --}}
    <div x-show="faceResult" x-cloak class="mt-4 space-y-2">
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="text-sm font-medium text-gray-600">Recognition Status</span>
            <span
                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                x-bind:class="{
                    'bg-emerald-50 text-emerald-700': faceResult === 'Matched',
                    'bg-amber-50 text-amber-700': faceResult === 'Mismatch',
                    'bg-red-50 text-red-700': faceResult === 'Not Recognized',
                }"
            >
                <x-icon name="video-camera" class="h-3.5 w-3.5" />
                <span x-text="faceResult"></span>
            </span>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="text-sm font-medium text-gray-600">Confidence</span>
            <span class="text-sm font-semibold text-gray-800" x-text="faceConfidence ? faceConfidence + '%' : '—'"></span>
        </div>
    </div>

    {{-- Controls --}}
    <div class="mt-5 flex flex-wrap gap-3">
        <button
            type="button"
            x-show="!faceScanning && !faceResult"
            @click="captureFace()"
            class="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-indigo-700"
        >
            <x-icon name="video-camera" class="h-4 w-4" />
            Capture Face
        </button>
        <button
            type="button"
            x-show="faceResult && faceResult !== 'Matched' && !faceScanning"
            x-cloak
            @click="retryFace()"
            class="flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50"
        >
            <x-icon name="arrow-path" class="h-4 w-4" />
            Retry
        </button>
        <button
            type="button"
            x-show="!faceScanning"
            @click="cancelFace()"
            class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-gray-500 transition-colors duration-150 hover:bg-gray-50"
        >
            <x-icon name="x-mark" class="h-4 w-4" />
            Cancel
        </button>
    </div>
</div>
