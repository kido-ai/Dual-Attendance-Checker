{{--
    teacher/attendance/partials/_qr-scanner.blade.php

    Included from start.blade.php. UI only — no real camera or QR
    decoding. Reads/writes the parent page's Alpine scope (scanning,
    qrResult, currentStep) and calls its scanQr()/rescan() methods.
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Step 1 &middot; QR Scanner</h3>
            <p class="text-sm text-gray-500">Ask the student to present their QR Code.</p>
        </div>
        <span
            class="rounded-full px-2.5 py-1 text-xs font-semibold"
            x-bind:class="scanning ? 'bg-blue-50 text-blue-700' : (qrResult ? 'bg-gray-100 text-gray-500' : 'bg-gray-100 text-gray-500')"
        >
            <span x-text="scanning ? 'Scanning...' : (qrResult ? 'Scan complete' : 'Idle')"></span>
        </span>
    </div>

    {{-- Camera preview placeholder --}}
    <div class="relative mt-5 flex aspect-video items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60">
        <div class="text-center" x-show="!scanning">
            <x-icon name="qr-code" class="mx-auto h-12 w-12 text-gray-300" />
            <p class="mt-2 text-sm text-gray-400">Camera preview placeholder</p>
        </div>
        <div class="text-center" x-show="scanning" x-cloak>
            <x-icon name="qr-code" class="mx-auto h-12 w-12 animate-pulse text-blue-400" />
            <p class="mt-2 text-sm font-medium text-blue-500">Scanning for QR code&hellip;</p>
        </div>

        {{-- Scanning frame corners, shown only while actively scanning --}}
        <div x-show="scanning" x-cloak class="pointer-events-none absolute inset-8 rounded-lg border-2 border-blue-300/70"></div>
    </div>

    {{-- QR validation result --}}
    <div x-show="qrResult" x-cloak class="mt-4 flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
        <span class="text-sm font-medium text-gray-600">QR Validation Result</span>
        <span
            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
            x-bind:class="{
                'bg-emerald-50 text-emerald-700': qrResult === 'Verified',
                'bg-amber-50 text-amber-700': qrResult === 'Expired',
                'bg-red-50 text-red-700': qrResult === 'Invalid' || qrResult === 'Already Used',
            }"
        >
            <x-icon name="qr-code" class="h-3.5 w-3.5" />
            <span x-text="qrResult"></span>
        </span>
    </div>

    {{-- Controls --}}
    <div class="mt-5 flex flex-wrap gap-3">
        <button
            type="button"
            x-show="!scanning && !qrResult"
            @click="scanQr()"
            class="flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-blue-700"
        >
            <x-icon name="qr-code" class="h-4 w-4" />
            Start Scanner
        </button>
        <button
            type="button"
            x-show="scanning"
            x-cloak
            @click="stopScanner()"
            class="flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50"
        >
            <x-icon name="stop-circle" class="h-4 w-4" />
            Stop Scanner
        </button>
        <button
            type="button"
            x-show="qrResult && !scanning"
            x-cloak
            @click="rescan()"
            class="flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50"
        >
            <x-icon name="arrow-path" class="h-4 w-4" />
            Rescan
        </button>
    </div>
</div>
