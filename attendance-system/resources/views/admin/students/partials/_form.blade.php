{{--
    admin/students/partials/_form.blade.php

    Shared by create.blade.php, edit.blade.php, and the "Add Student"
    modal on index.blade.php. Expects (all optional):

        $student    - Eloquent model or null (create mode)
        $isModal    - bool, true when rendered inside the modal (default false)
        $cancelUrl  - URL for the Cancel button when NOT in modal mode
        $formAction - route the form posts to
        $formMethod - 'POST' or 'PUT'
--}}
@php
$isModal = $isModal ?? false;
$cancelUrl = $cancelUrl ?? '#';
$formMethod = $formMethod ?? 'POST';

$val = function (string $key, $default = '') use ($student) {
// 1) Validated input always wins (so a failed PUT keeps user's edits).
$old = old($key);
if ($old !== null) {
return $old;
}

if ($student === null) {
return $default;
}

// 2) Columns that live on the related User model, not on Student.
if (in_array($key, ['email', 'user_email'], true)) {
return $student->user?->email ?? $default;
}

if (! is_object($student)) {
return $student[$key] ?? $default;
}

// 3) Aliases / formatting tweaks.
if ($key === 'year_level' || $key === 'year') {
return $student->year_level ?? $default;
}

if ($key === 'status') {
return ucfirst($student->status ?? 'active');
}

return $student->$key ?? $default;
};

// Profile photo URL on the PUBLIC disk (Storage::url() would use the default disk).
$profilePhotoUrl = null;
if ($student && $student->profile_photo
&& \Illuminate\Support\Facades\Storage::disk('public')->exists($student->profile_photo)) {
$profilePhotoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($student->profile_photo);
}
@endphp

<form
    method="POST"
    action="{{ $formAction ?? '#' }}"
    enctype="multipart/form-data"
    class="space-y-6">
    @csrf
    @if (strtoupper($formMethod) !== 'POST')
    @method($formMethod)
    @endif

    {{-- Personal Information --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Personal Information</h3>
        <p class="text-xs text-gray-500">The student's legal name and identifying details</p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-input-label for="student_number" value="Student Number" />
                <x-text-input id="student_number" name="student_number" :value="$val('student_number')" required placeholder="2026-00123" />
                <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="first_name" value="First Name" />
                <x-text-input id="first_name" name="first_name" :value="$val('first_name')" required placeholder="Ana" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="middle_name" value="Middle Name" />
                <x-text-input id="middle_name" name="middle_name" :value="$val('middle_name')" placeholder="Santos" />
                <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="last_name" value="Last Name" />
                <x-text-input id="last_name" name="last_name" :value="$val('last_name')" required placeholder="Reyes" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="gender" value="Gender" />
                <select id="gender" name="gender" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select gender</option>
                    @foreach (['Male', 'Female', 'Prefer not to say'] as $option)
                    <option value="{{ $option }}" @selected($val('gender')===$option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="birth_date" value="Birth Date" />
                <x-text-input id="birth_date" type="date" name="birth_date" :value="$val('birth_date')" />
                <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Academic Information --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Academic Information</h3>
        <p class="text-xs text-gray-500">Course, year level, and section placement</p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <x-input-label for="course" value="Course" />
                <select id="course" name="course" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select course</option>
                    @foreach (['BS Information Technology', 'BS Computer Science', 'BS Information Systems'] as $option)
                    <option value="{{ $option }}" @selected($val('course')===$option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('course')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="year_level" value="Year Level" />
                <select id="year_level" name="year_level" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select year</option>
                    @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $option)
                    <option value="{{ $option }}" @selected($val('year_level')===$option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="section" value="Section" />
                <select id="section" name="section" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select section</option>
                    @foreach (['A', 'B', 'C', 'D'] as $option)
                    <option value="{{ $option }}" @selected($val('section')===$option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('section')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Account credentials --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Account</h3>
        <p class="text-xs text-gray-500">
            {{ $student ? 'Leave password fields blank to keep the current password.' : 'Credentials the student will use to sign in.' }}
        </p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="email" value="Email" />
                {{-- $val('email') now falls through to $student->user->email --}}
                <x-text-input id="email" type="email" name="email" :value="$val('email')" required placeholder="ana.reyes@northgate.edu" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" type="password" name="password" autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="password_confirmation" value="Confirm Password" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Profile Photo --}}
    <div
        class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}"
        x-data="{ preview: @js($profilePhotoUrl) }">
        <h3 class="text-sm font-semibold text-gray-900">Profile Photo</h3>
        <p class="text-xs text-gray-500">Used across the dashboard and ID verification screens</p>

        <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row">
            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gray-200 bg-gray-50">
                <template x-if="preview">
                    <img :src="preview" alt="Photo preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <x-icon name="photo" class="h-8 w-8 text-gray-300" />
                </template>
            </div>

            <label class="flex w-full flex-1 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/60 px-6 py-6 text-center transition-colors duration-150 hover:border-emerald-300 hover:bg-emerald-50/40">
                <x-icon name="arrow-down-tray" class="h-6 w-6 text-gray-400" />
                <p class="mt-2 text-sm font-medium text-gray-600">
                    {{ $student?->profile_photo ? 'Click to replace the photo' : 'Click to upload a photo' }}
                </p>
                <p class="mt-0.5 text-xs text-gray-400">JPG, PNG, or JPEG &middot; up to 5MB</p>
                <input
                    type="file"
                    name="photo"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="hidden"
                    @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
            </label>
        </div>
        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
    </div>

    {{-- Status --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Status</h3>
        <p class="text-xs text-gray-500">Controls whether the student can check in via QR or Face ID</p>

        <div class="mt-4 flex gap-3">
            @php $currentStatus = $val('status', 'Active'); @endphp
            <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 px-4 py-3 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                <input type="radio" name="status" value="Active" class="h-4 w-4 border-gray-300 text-emerald-600 focus:ring-2 focus:ring-emerald-200" @checked($currentStatus==='Active' )>
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
            <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 px-4 py-3 has-[:checked]:border-amber-300 has-[:checked]:bg-amber-50">
                <input type="radio" name="status" value="Inactive" class="h-4 w-4 border-gray-300 text-amber-600 focus:ring-2 focus:ring-amber-200" @checked($currentStatus==='Inactive' )>
                <span class="text-sm font-medium text-gray-700">Inactive</span>
            </label>
        </div>
    </div>

    {{-- Form actions --}}
    <div class="flex items-center justify-end gap-3 {{ $isModal ? '' : 'border-t border-gray-100 pt-6' }}">
        @if ($isModal)
        <button type="button" @click="showAddModal = false" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50">
            Cancel
        </button>
        @else
        <a href="{{ $cancelUrl }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50">
            Cancel
        </a>
        @endif
        <button type="submit" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
            <x-icon name="check-circle" class="h-5 w-5" />
            Save Student
        </button>
    </div>
</form>