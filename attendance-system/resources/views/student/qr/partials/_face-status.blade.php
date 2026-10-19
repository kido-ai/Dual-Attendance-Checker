{{--
    student/qr/partials/_face-status.blade.php

    Included from index.blade.php. Expects $faceEnrollment (array with
    status/last_updated) in scope.
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Face Enrollment Status</h3>
        <x-status-badge :status="$faceEnrollment['status']" />
    </div>
    <p class="mt-1 text-xs text-gray-500">Used together with your QR Code for Dual Authentication</p>

    <div class="mt-4 flex items-center gap-4 rounded-xl bg-gray-50 p-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm">
            <x-icon name="video-camera" class="h-6 w-6 text-gray-400" />
        </div>
        <div class="min-w-0">
            <p class="text-sm font-medium text-gray-800">{{ $faceEnrollment['message'] }}</p>
            <p class="mt-0.5 text-xs text-gray-400">Last updated {{ $faceEnrollment['last_updated'] }}</p>
        </div>
    </div>
</div>
