{{--
    student/qr/partials/_qr-information.blade.php

    Included from index.blade.php. Expects $qr (array) in scope.
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-gray-900">QR Information</h3>
    <p class="text-xs text-gray-500">Metadata for your assigned QR Code</p>

    <div class="mt-4 space-y-3">
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-gray-600">
                <x-icon name="identification" class="h-4 w-4 text-gray-400" /> Student Number
            </span>
            <span class="text-sm font-medium text-gray-800">{{ $qr['student_number'] }}</span>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-gray-600">
                <x-icon name="key" class="h-4 w-4 text-gray-400" /> QR Token ID
            </span>
            <span class="font-mono text-sm font-medium text-gray-800">{{ $qr['token_id'] }}</span>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-gray-600">
                <x-icon name="calendar-days" class="h-4 w-4 text-gray-400" /> Date Generated
            </span>
            <span class="text-sm font-medium text-gray-800">{{ $qr['generated_at'] }}</span>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-gray-600">
                <x-icon name="clock" class="h-4 w-4 text-gray-400" /> Expiration Date
            </span>
            <span class="text-sm font-medium text-gray-800">{{ $qr['expires_at'] }}</span>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
            <span class="flex items-center gap-2 text-sm text-gray-600">
                <x-icon name="arrow-path" class="h-4 w-4 text-gray-400" /> Last Updated
            </span>
            <span class="text-sm font-medium text-gray-800">{{ $qr['updated_at'] }}</span>
        </div>
        <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
            <span class="text-sm font-medium text-gray-700">Status</span>
            <x-status-badge :status="$qr['status']" />
        </div>
    </div>
</div>
