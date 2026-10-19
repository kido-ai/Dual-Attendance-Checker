{{--
    components/profile-modal.blade.php

    Shared across all three roles' navbars. Opened globally via a
    window event (`open-profile-modal`) rather than a parent x-data flag,
    so this component stays fully self-contained -- any navbar anywhere
    can trigger it with `$dispatch('open-profile-modal')` without the
    layout needing to know this component exists or share state with it.

    Reuses three modals from <x-modal> (view / edit / change-password)
    and the auth module's form components (<x-input-label>,
    <x-text-input>, <x-input-error>) instead of re-implementing form
    styling from scratch.

    Props:
        name            - string
        email           - string
        phone           - string|null
        address         - string|null
        role            - display label, e.g. 'Administrator' | 'Teacher' | 'Student'
        roleTone        - 'emerald' | 'blue' | 'indigo' (badge color per role)
        avatarUrl       - string|null (falls back to a generated avatar)
        memberSince     - formatted date string
        lastUpdated     - formatted date string
        identifierLabel - optional, e.g. 'Student Number' | 'Employee ID' (display-only)
        identifierValue - optional, paired with identifierLabel
        updateAction    - route URL the edit-profile AND change-password forms both post to
        editableFields  - array subset of ['name','email','phone','address','photo'].
                           IMPORTANT: this must match what the role's real
                           ProfileController actually accepts. Student\ProfileController
                           only accepts photo + password today (by deliberate
                           design -- academic fields are admin-owned), so the
                           Student navbar should pass :editable-fields="['photo']",
                           NOT the full set, or the edit form will silently
                           submit fields the backend ignores.
--}}
@props([
'name',
'email',
'phone' => null,
'address' => null,
'role' => 'User',
'roleTone' => 'emerald',
'avatarUrl' => null,
'memberSince' => null,
'lastUpdated' => null,
'identifierLabel' => null,
'identifierValue' => null,
'updateAction' => '#',
'editableFields' => ['name', 'email', 'phone', 'address', 'photo'],
])

@php
$resolvedAvatar = $avatarUrl ?: 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=059669&color=fff&size=128';

$roleToneClass = [
'emerald' => 'bg-emerald-50 text-emerald-700',
'blue' => 'bg-blue-50 text-blue-700',
'indigo' => 'bg-indigo-50 text-indigo-700',
][$roleTone] ?? 'bg-emerald-50 text-emerald-700';
@endphp

<div
    x-data="{
        show: false,
        editOpen: false,
        passwordOpen: false,
        photoPreview: null,
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        newPassword: '',
        editErrors: {},
        passwordErrors: {},
        editSubmitting: false,
        passwordSubmitting: false,
        get passwordScore() {
            const val = this.newPassword;
            if (!val) return 0;
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            return score;
        },
        get passwordLabel() {
            return ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'][this.passwordScore];
        },
        get passwordColor() {
            return ['bg-gray-200', 'bg-red-400', 'bg-amber-400', 'bg-blue-400', 'bg-emerald-500'][this.passwordScore];
        },
    }"
    @open-profile-modal.window="show = true">
    {{-- ============================== MAIN PROFILE MODAL ============================== --}}
    <x-modal show="show" close="show = false" max-width="3xl">
        <div aria-labelledby="profile-modal-heading">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-6 sm:px-8">
                <div class="flex items-center gap-4">
                    <img src="{{ $resolvedAvatar }}" alt="{{ $name }}" class="h-16 w-16 rounded-full shadow-sm sm:h-20 sm:w-20">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 id="profile-modal-heading" class="truncate text-lg font-semibold text-gray-900 sm:text-xl">{{ $name }}</h2>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleToneClass }}">{{ $role }}</span>
                        </div>
                        <p class="mt-1 flex items-center gap-1.5 truncate text-sm text-gray-500">
                            <x-icon name="envelope" class="h-4 w-4 shrink-0 text-gray-400" />
                            {{ $email }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="show = false"
                    aria-label="Close profile"
                    class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            {{-- Body: two columns --}}
            <div class="grid max-h-[65vh] grid-cols-1 gap-6 overflow-y-auto px-6 py-6 sm:grid-cols-5 sm:px-8">

                {{-- Left column: profile picture --}}
                <div class="sm:col-span-2">
                    <div class="flex flex-col items-center rounded-2xl border border-gray-100 bg-gray-50/60 p-6 text-center">
                        <img src="{{ $resolvedAvatar }}" alt="{{ $name }}" class="h-28 w-28 rounded-full shadow-sm">
                        <button
                            type="button"
                            @click="editOpen = true"
                            class="mt-4 flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors duration-150 hover:bg-gray-50">
                            <x-icon name="camera" class="h-4 w-4" />
                            Change Photo
                        </button>
                        <p class="mt-2 text-xs text-gray-400">JPG, PNG, or JPEG &middot; up to 2MB</p>
                    </div>
                </div>

                {{-- Right column: personal information --}}
                <div class="sm:col-span-3">
                    <h3 class="text-sm font-semibold text-gray-900">Personal Information</h3>
                    <div class="mt-3 space-y-2.5">
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="user-circle" class="h-4 w-4 text-gray-400" /> Full Name</span>
                            <span class="truncate text-sm font-medium text-gray-800">{{ $name }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="envelope" class="h-4 w-4 text-gray-400" /> Email</span>
                            <span class="truncate text-sm font-medium text-gray-800">{{ $email }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="phone" class="h-4 w-4 text-gray-400" /> Phone</span>
                            <span class="truncate text-sm font-medium text-gray-800">{{ $phone ?: 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="map-pin" class="h-4 w-4 text-gray-400" /> Address</span>
                            <span class="truncate text-sm font-medium text-gray-800">{{ $address ?: 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="shield-check" class="h-4 w-4 text-gray-400" /> Role</span>
                            <span class="text-sm font-medium text-gray-800">{{ $role }}</span>
                        </div>
                        @if ($identifierLabel)
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="identification" class="h-4 w-4 text-gray-400" /> {{ $identifierLabel }}</span>
                            <span class="text-sm font-medium text-gray-800">{{ $identifierValue }}</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="calendar-days" class="h-4 w-4 text-gray-400" /> Member Since</span>
                            <span class="text-sm font-medium text-gray-800">{{ $memberSince ?: 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="flex items-center gap-2 text-sm text-gray-500"><x-icon name="clock" class="h-4 w-4 text-gray-400" /> Last Updated</span>
                            <span class="text-sm font-medium text-gray-800">{{ $lastUpdated ?: 'Never' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="flex flex-col gap-3 border-t border-gray-100 px-6 py-5 sm:flex-row sm:justify-end sm:px-8">
                <button
                    type="button"
                    @click="show = false"
                    class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50">
                    Close
                </button>
                <button
                    type="button"
                    @click="passwordOpen = true"
                    class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 transition-colors duration-150 hover:bg-gray-50">
                    <x-icon name="key" class="h-4 w-4" />
                    Change Password
                </button>
                <button
                    type="button"
                    @click="editOpen = true"
                    class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                    <x-icon name="pencil" class="h-4 w-4" />
                    Edit Profile
                </button>
            </div>
        </div>
    </x-modal>

    {{-- ============================== EDIT PROFILE MODAL ============================== --}}
    <x-modal show="editOpen" close="editOpen = false" max-width="lg">
        <form
            method="POST"
            action="{{ $updateAction }}"
            enctype="multipart/form-data"
            aria-labelledby="edit-profile-heading"
            @submit.prevent="
                editSubmitting = true;
                editErrors = {};
                let form = $el;
                let formData = new FormData(form);
                formData.append('_method', 'PUT');
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                })
                .then(response => response.json())
                .then(data => {
                    editSubmitting = false;
                    if (data.errors) {
                        editErrors = data.errors;
                    } else {
                        editErrors = {};
                        editOpen = false;
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    }
                })
                .catch(() => { editSubmitting = false; });
            ">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <h3 id="edit-profile-heading" class="text-base font-semibold text-gray-900">Edit Profile</h3>
                <button type="button" @click="editOpen = false" aria-label="Close" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="max-h-[65vh] space-y-5 overflow-y-auto px-6 py-6">

                @if (in_array('photo', $editableFields))
                {{-- Profile photo upload --}}
                <div>
                    <x-input-label value="Profile Picture" />
                    <div class="mt-1.5 flex items-center gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-dashed border-gray-200 bg-gray-50">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="Preview" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                <img src="{{ $resolvedAvatar }}" alt="{{ $name }}" class="h-full w-full object-cover">
                            </template>
                        </div>
                        <label class="flex-1 cursor-pointer rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60 px-4 py-3 text-center text-sm font-medium text-gray-600 transition-colors duration-150 hover:border-emerald-300 hover:bg-emerald-50/40">
                            Click to upload a new photo
                            <input
                                type="file"
                                name="photo"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                class="hidden"
                                @change="photoPreview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : photoPreview">
                        </label>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, or JPEG &middot; up to 2MB</p>
                    <template x-for="(error, key) in editErrors.photo">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>
                @endif

                @if (in_array('name', $editableFields))
                <div>
                    <x-input-label for="profile_name" value="Full Name" />
                    <x-text-input id="profile_name" name="name" :value="old('name', $name)" required />
                    <template x-for="(error, key) in editErrors.name">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>
                @endif

                @if (in_array('email', $editableFields))
                <div>
                    <x-input-label for="profile_email" value="Email" />
                    <x-text-input id="profile_email" type="email" name="email" :value="old('email', $email)" required />
                    <template x-for="(error, key) in editErrors.email">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>
                @endif

                @if (in_array('phone', $editableFields))
                <div>
                    <x-input-label for="profile_phone" value="Phone Number" />
                    <x-text-input id="profile_phone" type="tel" name="phone" :value="old('phone', $phone)" />
                    <template x-for="(error, key) in editErrors.phone">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>
                @endif

                @if (in_array('address', $editableFields))
                <div>
                    <x-input-label for="profile_address" value="Address" />
                    <x-text-input id="profile_address" name="address" :value="old('address', $address)" />
                    <template x-for="(error, key) in editErrors.address">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>
                @endif

                @if (empty($editableFields))
                <p class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500">
                    There is nothing editable for this account beyond your profile photo yet.
                </p>
                @endif

                {{-- Always locked, never rendered as inputs: role, student number / employee ID, username --}}
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-5">
                <button type="button" @click="editOpen = false" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" :disabled="editSubmitting" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50">
                    <x-icon name="check-circle" class="h-4 w-4" />
                    <span x-show="!editSubmitting">Save Changes</span>
                    <span x-show="editSubmitting">Saving...</span>
                </button>
            </div>
        </form>
    </x-modal>

    {{-- ============================== CHANGE PASSWORD MODAL ============================== --}}
    <x-modal show="passwordOpen" close="passwordOpen = false" max-width="md">
        <form
            method="POST"
            action="{{ $updateAction }}"
            aria-labelledby="change-password-heading"
            @submit.prevent="
            passwordSubmitting = true;
            passwordErrors = {};
            let form = $el;
            let formData = new FormData(form);
            formData.append('_method', 'PUT');
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(response => response.json())
            .then(data => {
                passwordSubmitting = false;
                if (data.errors) {
                    passwordErrors = data.errors;
                } else {
                    passwordErrors = {};
                    passwordOpen = false;
                    // Force reload after a tiny delay to let the modal close
                    setTimeout(() => window.location.reload(), 200);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                passwordSubmitting = false;
                alert('An error occurred. Check the console for details.');
            });
        ">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <h3 id="change-password-heading" class="text-base font-semibold text-gray-900">Change Password</h3>
                <button type="button" @click="passwordOpen = false" aria-label="Close" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="space-y-5 px-6 py-6">
                {{-- Current password --}}
                <div>
                    <x-input-label for="current_password" value="Current Password" />
                    <div class="relative mt-1.5">
                        <input
                            :type="showCurrent ? 'text' : 'password'"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 pr-11 text-sm text-gray-900 transition-colors duration-150 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" :aria-label="showCurrent ? 'Hide password' : 'Show password'">
                            <x-icon x-show="!showCurrent" name="eye" class="h-5 w-5" />
                            <x-icon x-show="showCurrent" name="eye-slash" class="h-5 w-5" x-cloak />
                        </button>
                    </div>
                    <template x-for="(error, key) in passwordErrors.current_password">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>

                {{-- New password --}}
                <div>
                    <x-input-label for="new_password" value="New Password" />
                    <div class="relative mt-1.5">
                        <input
                            :type="showNew ? 'text' : 'password'"
                            id="new_password"
                            name="password"
                            autocomplete="new-password"
                            x-model="newPassword"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 pr-11 text-sm text-gray-900 transition-colors duration-150 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <button type="button" @click="showNew = !showNew" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" :aria-label="showNew ? 'Hide password' : 'Show password'">
                            <x-icon x-show="!showNew" name="eye" class="h-5 w-5" />
                            <x-icon x-show="showNew" name="eye-slash" class="h-5 w-5" x-cloak />
                        </button>
                    </div>

                    {{-- Strength indicator --}}
                    <div x-show="newPassword.length > 0" x-cloak class="mt-2">
                        <div class="flex gap-1">
                            <template x-for="i in 4" :key="i">
                                <div class="h-1.5 flex-1 rounded-full" :class="i <= passwordScore ? passwordColor : 'bg-gray-100'"></div>
                            </template>
                        </div>
                        <p class="mt-1 text-xs font-medium text-gray-500" x-text="passwordLabel"></p>
                    </div>

                    <template x-for="(error, key) in passwordErrors.password">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>

                {{-- Confirm password --}}
                <div>
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <div class="relative mt-1.5">
                        <input
                            :type="showConfirm ? 'text' : 'password'"
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 pr-11 text-sm text-gray-900 transition-colors duration-150 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" :aria-label="showConfirm ? 'Hide password' : 'Show password'">
                            <x-icon x-show="!showConfirm" name="eye" class="h-5 w-5" />
                            <x-icon x-show="showConfirm" name="eye-slash" class="h-5 w-5" x-cloak />
                        </button>
                    </div>
                    <template x-for="(error, key) in passwordErrors.password_confirmation">
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                    </template>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-5">
                <button type="button" @click="passwordOpen = false" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" :disabled="passwordSubmitting" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50">
                    <x-icon name="key" class="h-4 w-4" />
                    <span x-show="!passwordSubmitting">Save Password</span>
                    <span x-show="passwordSubmitting">Saving...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>