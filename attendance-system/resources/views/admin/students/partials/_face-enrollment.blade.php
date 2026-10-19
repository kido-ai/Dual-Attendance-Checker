{{--
    admin/students/partials/_face-enrollment.blade.php

    $student->faceProfile is a FaceProfile model (or null).

    IMPORTANT: adjust the column name below if your migration uses
    something other than `photo_path` (common alternatives:
    `image_path`, `photo`, `face_path`).
--}}
@php
$faceProfile = $student->faceProfile;
$isEnrolled = $faceProfile && $faceProfile->status === 'enrolled';

$storedPath = $faceProfile?->face_embedding ?? $faceProfile?->photo_path ?? null;
$facePhotoUrl = null;

if ($storedPath) {
foreach (['public', 'local'] as $disk) {
if (Storage::disk($disk)->exists($storedPath)) {
$facePhotoUrl = Storage::disk($disk)->url($storedPath);
break;
}
}
}
@endphp

<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Face Enrollment</h3>
            <p class="mt-1 text-xs text-gray-500">
                Upload a clear front-facing photo for dual-auth attendance.
            </p>
        </div>
        <div class="rounded-full px-2.5 py-1 text-[11px] font-semibold
                    {{ $isEnrolled ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
            {{ $isEnrolled ? 'Enrolled' : 'Pending' }}
        </div>
    </div>

    {{-- Enrolled photo preview --}}
    @if ($isEnrolled && $facePhotoUrl)
    <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="h-32 w-32 shrink-0 overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 shadow-inner">
            <img src="{{ $facePhotoUrl }}"
                alt="{{ $student->full_name }} face enrollment"
                class="h-full w-full object-cover">
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-800">Face enrolled</p>
            <p class="mt-0.5 text-xs text-gray-500">
                Enrolled on
                {{ $faceProfile->enrolled_at?->format('M j, Y g:i A') ?? 'unknown date' }}
            </p>
            <p class="mt-1 truncate text-[11px] text-gray-400">
                Stored at: {{ $storedPath }}
            </p>
        </div>
    </div>
    @elseif ($isEnrolled)
    {{-- Record says enrolled, but the file is missing on disk --}}
    <div class="mt-5 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs text-amber-700">
        A face enrollment record exists, but the image file was not found on the
        <code class="rounded bg-amber-100 px-1">public</code> disk.
        Try re-enrolling below, or verify <code class="rounded bg-amber-100 px-1">php artisan storage:link</code>
        has been run and that <code class="rounded bg-amber-100 px-1">{{ $storedPath }}</code> exists.
    </div>
    @endif

    {{-- Upload / re-enroll form --}}
    <div class="mt-5 rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-4">
        <form method="POST"
            action="{{ route('admin.students.enroll-face', $student) }}"
            enctype="multipart/form-data"
            class="space-y-4">
            @csrf

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label class="flex w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-white px-4 py-6 text-center transition hover:border-indigo-300 hover:bg-indigo-50/40">
                    <x-icon name="photo" class="h-6 w-6 text-gray-400" />
                    <span class="mt-2 text-sm font-medium text-gray-600">
                        {{ $isEnrolled ? 'Choose a replacement photo' : 'Choose face photo' }}
                    </span>
                    <span class="text-xs text-gray-400">PNG or JPG up to 5MB</span>
                    <input type="file"
                        name="photo"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        class="hidden"
                        required>
                </label>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <x-icon name="camera" class="mr-2 h-4 w-4" />
                    {{ $isEnrolled ? 'Re-enroll Face' : 'Enroll Face' }}
                </button>
            </div>

            @error('photo')
            <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>
    </div>

    @if ($isEnrolled)
    <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
        Enrolled on {{ $faceProfile->enrolled_at?->format('M j, Y g:i A') ?? 'unknown date' }}
    </div>
    @endif
</div>