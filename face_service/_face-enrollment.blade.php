{{--
    admin/students/partials/_face-enrollment.blade.php

    Include this inside admin/students/show.blade.php. It gives the admin
    a simple photo-upload form to enroll or re-enroll a student's
    reference face. No camera needed on the admin side -- a clear frontal
    photo from any source works fine.

    Usage in show.blade.php:
        @include('admin.students.partials._face-enrollment', ['student' => $student])
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Face Enrollment</h3>
            <p class="text-sm text-gray-500">Upload a clear, frontal photo to enroll this student's face for dual-authentication attendance.</p>
        </div>
        @if ($student->faceProfile)
            <x-status-badge :status="$student->faceProfile->status === 'enrolled' ? 'Enrolled' : 'Not Enrolled'" />
        @else
            <x-status-badge status="Not Enrolled" />
        @endif
    </div>

    @if ($student->faceProfile && $student->faceProfile->status === 'enrolled')
        <div class="mt-4 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3">
            <x-icon name="shield-check" class="h-5 w-5 text-emerald-600" />
            <div>
                <p class="text-sm font-medium text-emerald-700">Face enrolled</p>
                <p class="text-xs text-emerald-600">Enrolled on {{ $student->faceProfile->enrolled_at?->format('M j, Y g:i A') ?? 'unknown date' }}</p>
            </div>
        </div>
    @endif

    {{-- Upload form --}}
    <form
        method="POST"
        action="{{ route('admin.students.enroll-face', $student) }}"
        enctype="multipart/form-data"
        class="mt-5"
        x-data="{ preview: null, fileName: '' }"
    >
        @csrf

        <div class="space-y-4">
            <div>
                <x-input-label value="Reference Photo" />
                <p class="mt-0.5 text-xs text-gray-400">JPG or PNG, max 5MB. The face must be clearly visible, facing forward, and well-lit.</p>

                {{-- Drop zone --}}
                <label class="mt-2 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60 px-6 py-8 text-center transition-colors duration-150 hover:border-emerald-300 hover:bg-emerald-50/40">
                    <template x-if="!preview">
                        <div class="flex flex-col items-center gap-2">
                            <x-icon name="photo" class="h-8 w-8 text-gray-300" />
                            <span class="text-sm font-medium text-gray-600">Click to upload a photo</span>
                            <span class="text-xs text-gray-400">or drag and drop</span>
                        </div>
                    </template>
                    <template x-if="preview">
                        <div class="flex flex-col items-center gap-2">
                            <img :src="preview" class="h-32 w-32 rounded-full object-cover shadow-sm" alt="Preview">
                            <span class="text-xs font-medium text-emerald-600" x-text="fileName"></span>
                        </div>
                    </template>
                    <input
                        type="file"
                        name="photo"
                        accept="image/jpeg,image/png"
                        class="hidden"
                        @change="
                            if ($event.target.files.length) {
                                preview = URL.createObjectURL($event.target.files[0]);
                                fileName = $event.target.files[0].name;
                            }
                        "
                    >
                </label>
                <x-input-error :messages="$errors->get('photo')" class="mt-2" />
            </div>

            {{-- Tips --}}
            <div class="rounded-xl bg-blue-50/60 px-4 py-3">
                <p class="text-xs font-semibold text-blue-700">Tips for a good reference photo</p>
                <ul class="mt-1.5 space-y-1 text-xs text-blue-600">
                    <li>Face the camera directly -- no side angles</li>
                    <li>Use good, even lighting -- avoid shadows across the face</li>
                    <li>No sunglasses, hats, or masks</li>
                    <li>Neutral expression works best</li>
                    <li>Minimum 200x200 pixels; larger is better</li>
                </ul>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                @if ($student->faceProfile && $student->faceProfile->status === 'enrolled')
                    Uploading a new photo will replace the current enrollment.
                @else
                    This student cannot be marked present through dual authentication until enrolled.
                @endif
            </p>
            <button
                type="submit"
                class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700"
            >
                <x-icon name="shield-check" class="h-4 w-4" />
                {{ $student->faceProfile?->status === 'enrolled' ? 'Re-enroll Face' : 'Enroll Face' }}
            </button>
        </div>
    </form>
</div>
