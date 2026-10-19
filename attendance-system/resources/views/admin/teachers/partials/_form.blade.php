{{--
    admin/teachers/partials/_form.blade.php

    Shared by create.blade.php, edit.blade.php, and the "Add Teacher"
    modal on index.blade.php. Expects (all optional):

        $teacher    - array of existing values for prefill (null = create mode)
        $isModal    - bool, true when rendered inside the modal (default false)
        $cancelUrl  - URL for the Cancel button when NOT in modal mode
        $formAction - route the form posts to
        $formMethod - 'POST' or 'PUT'
--}}
@php
$teacher = $teacher ?? null;
$isModal = $isModal ?? false;
$cancelUrl = $cancelUrl ?? '#';
$formMethod = $formMethod ?? 'POST';
$val = fn (string $key, $default = '') => old($key, $teacher[$key] ?? $default);
$subjectOptions = ['Database Systems', 'Data Structures', 'Systems Analysis', 'Advanced SQL Lab', 'Software Engineering', 'Intro to Computing'];
$classOptions = ['BSIT 3-A', 'BSIT 3-B', 'BSCS 2-B', 'BSIT 1-C', 'BSIT 4-A'];
$selectedSubjects = old('subjects', $teacher['subjects'] ?? []);
$selectedClasses = old('classes', $teacher['classes'] ?? []);
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
        <p class="text-xs text-gray-500">The teacher's legal name and identifying details</p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-input-label for="employee_id" value="Employee ID" />
                <x-text-input id="employee_id" name="employee_id" :value="$val('employee_id')" required placeholder="EMP-2026-014" />
                <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="first_name" value="First Name" />
                <x-text-input id="first_name" name="first_name" :value="$val('first_name')" required placeholder="Elena" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="middle_name" value="Middle Name" />
                <x-text-input id="middle_name" name="middle_name" :value="$val('middle_name')" placeholder="Marasigan" />
                <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="last_name" value="Last Name" />
                <x-text-input id="last_name" name="last_name" :value="$val('last_name')" required placeholder="Villanueva" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="gender" value="Gender" />
                <select id="gender" name="gender" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select gender</option>
                    @foreach (['Female', 'Male', 'Prefer not to say'] as $option)
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
            <div>
                <x-input-label for="nationality" value="Nationality" />
                <x-text-input id="nationality" name="nationality" :value="$val('nationality', 'Filipino')" placeholder="Filipino" />
                <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Employment Information --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Employment Information</h3>
        <p class="text-xs text-gray-500">Department, role, and employment terms</p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-input-label for="department" value="Department" />
                <select id="department" name="department" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select department</option>
                    @foreach (['College of Computer Studies', 'College of Engineering', 'College of Business', 'General Education'] as $option)
                    <option value="{{ $option }}" @selected($val('department')===$option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('department')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="position" value="Position" />
                <x-text-input id="position" name="position" :value="$val('position')" placeholder="Associate Professor" />
                <x-input-error :messages="$errors->get('position')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="employment_type" value="Employment Type" />
                <select id="employment_type" name="employment_type" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select type</option>
                    @foreach (['Full-Time', 'Part-Time', 'Contractual', 'Visiting'] as $option)
                    <option value="{{ $option }}" @selected($val('employment_type')===$option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('employment_type')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="hire_date" value="Hire Date" />
                <x-text-input id="hire_date" type="date" name="hire_date" :value="$val('hire_date')" />
                <x-input-error :messages="$errors->get('hire_date')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="employment_status" value="Employment Status" />
                <select id="employment_status" name="employment_status" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select status</option>
                    @foreach (['Full-Time', 'Part-Time', 'Contractual', 'Visiting'] as $option)
                    <option value="{{ $option }}" @selected($val('employment_status')===$option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('employment_status')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Contact Information --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Contact Information</h3>
        <p class="text-xs text-gray-500">How the teacher and their emergency contact can be reached</p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" type="email" name="email" :value="$val('email')" required placeholder="elena.villanueva@northgate.edu" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" value="Phone Number" />
                <x-text-input id="phone" type="tel" name="phone" :value="$val('phone')" placeholder="+63 917 654 3210" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="active" @selected(($teacher['status'] ?? old('status', 'active' ))==='active' )>Active</option>
                    <option value="inactive" @selected(($teacher['status'] ?? old('status', 'active' ))==='inactive' )>Inactive</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="address" value="Address" />
                <x-text-input id="address" name="address" :value="$val('address')" placeholder="45 Magsaysay Avenue, Davao City" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="emergency_contact" value="Emergency Contact" />
                <x-text-input id="emergency_contact" name="emergency_contact" :value="$val('emergency_contact')" placeholder="Maria Villanueva &middot; +63 917 000 1122" />
                <x-input-error :messages="$errors->get('emergency_contact')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Account --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Account</h3>
        <p class="text-xs text-gray-500">
            {{ $teacher ? 'Leave password fields blank to keep the current password.' : 'Credentials the teacher will use to sign in.' }}
        </p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-input-label for="username" value="Username" />
                <x-text-input id="username" name="username" :value="$val('username')" required placeholder="elena.villanueva" />
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" type="password" name="password" autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Confirm Password" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
            <div>
                <x-input-label value="Role" />
                <div class="flex items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5">
                    <x-icon name="shield-check" class="h-4 w-4 text-emerald-600" />
                    <span class="text-sm font-medium text-gray-600">Teacher</span>
                </div>
                <input type="hidden" name="role" value="teacher">
            </div>
        </div>
    </div>

    {{-- Profile Photo --}}
    <div
        class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}"
        x-data="{ preview: {{ $teacher['photo_url'] ?? null ? "'" . $teacher['photo_url'] . "'" : 'null' }} }">
        <h3 class="text-sm font-semibold text-gray-900">Profile Picture</h3>
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
                <p class="mt-2 text-sm font-medium text-gray-600">Click to upload a photo</p>
                <p class="mt-0.5 text-xs text-gray-400">JPG, PNG, or JPEG &middot; up to 5MB</p>
                <input
                    type="file"
                    name="profile_photo"
                    accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="hidden"
                    @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
            </label>
        </div>
        <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
    </div>

    {{-- Teaching Information --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Teaching Information</h3>
        <p class="text-xs text-gray-500">Subjects, classes, and load — can also be managed later via Assign Subjects / Assign Classes</p>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="subjects" value="Subjects Assigned" />
                <select id="subjects" name="subjects[]" multiple size="4" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @foreach ($subjectOptions as $option)
                    <option value="{{ $option }}" @selected(in_array($option, $selectedSubjects))>{{ $option }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-400">Hold Ctrl / Cmd to select multiple</p>
                <x-input-error :messages="$errors->get('subjects')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="classes" value="Classes Assigned" />
                <select id="classes" name="classes[]" multiple size="4" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @foreach ($classOptions as $option)
                    <option value="{{ $option }}" @selected(in_array($option, $selectedClasses))>{{ $option }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-400">Hold Ctrl / Cmd to select multiple</p>
                <x-input-error :messages="$errors->get('classes')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="max_load" value="Maximum Teaching Load" />
                <x-text-input id="max_load" type="number" name="max_load" :value="$val('max_load', 24)" placeholder="24 units" />
                <x-input-error :messages="$errors->get('max_load')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="office" value="Office" />
                <x-text-input id="office" name="office" :value="$val('office')" placeholder="Faculty Room, 2nd Floor" />
                <x-input-error :messages="$errors->get('office')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Status --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 {{ $isModal ? 'shadow-none' : 'shadow-sm' }}">
        <h3 class="text-sm font-semibold text-gray-900">Status</h3>
        <p class="text-xs text-gray-500">Controls whether the teacher can log in and open attendance sessions</p>

        <div class="mt-4 flex gap-3">
            <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 px-4 py-3 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                <input type="radio" name="account_status" value="Active" class="h-4 w-4 border-gray-300 text-emerald-600 focus:ring-2 focus:ring-emerald-200" @checked($val('account_status', 'Active' )==='Active' )>
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
            <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 px-4 py-3 has-[:checked]:border-amber-300 has-[:checked]:bg-amber-50">
                <input type="radio" name="account_status" value="Inactive" class="h-4 w-4 border-gray-300 text-amber-600 focus:ring-2 focus:ring-amber-200" @checked($val('account_status')==='Inactive' )>
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
            Save Teacher
        </button>
    </div>
</form>