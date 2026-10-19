{{--
    admin/settings/index.blade.php

    Data from Admin\SettingController::index()
    $settings - flat ['key' => 'value'] array from SystemSetting::allKeyed()

    Design matches the admin dashboard exactly:
    - rounded-2xl border border-gray-100 bg-white shadow-sm cards
    - emerald primary, gray typography hierarchy
    - same input/label/button patterns as admin/students/create
    - Pure ASCII -- no em-dashes or smart quotes
--}}
<x-layouts.admin
    title="Settings"
    :breadcrumbs="[['label' => 'Admin'], ['label' => 'Settings']]">
    <div
        x-data="{ logoPreview: null }"
        class="mx-auto max-w-4xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-900">System Settings</h2>
            <p class="mt-1 text-sm text-gray-500">Control system-wide configuration for the Dual Authentication Attendance Checker.</p>
        </div>

        @if (session('status'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
            <x-icon name="check-circle" class="h-5 w-5 shrink-0 text-emerald-600" />
            <p class="text-sm font-medium text-emerald-700">{{ session('status') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- ============================================================
                 CARD 1 -- General
            ============================================================ --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50">
                            <x-icon name="cog" class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">General</h3>
                            <p class="text-xs text-gray-500">Basic identity settings shown across the system</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-5 px-6 py-6">

                    {{-- System Name --}}
                    <div>
                        <x-input-label for="system_name" value="System Name" />
                        <p class="mt-0.5 text-xs text-gray-400">Appears in the browser tab, header, and footer</p>
                        <x-text-input
                            id="system_name"
                            name="system_name"
                            class="mt-1.5"
                            :value="old('system_name', $settings['system_name'] ?? $systemSettings['system_name'])"
                            placeholder="e.g. Attendance Checker"
                            required />
                        <x-input-error :messages="$errors->get('system_name')" class="mt-1.5" />
                    </div>

                    {{-- School Name --}}
                    <div>
                        <x-input-label for="school_name" value="School Name" />
                        <p class="mt-0.5 text-xs text-gray-400">Displayed on the login page, reports, and QR code exports</p>
                        <x-text-input
                            id="school_name"
                            name="school_name"
                            class="mt-1.5"
                            :value="old('school_name', $settings['school_name'] ?? $systemSettings['school_name'])"
                            placeholder="e.g. National University Manila"
                            required />
                        <x-input-error :messages="$errors->get('school_name')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 CARD 2 -- School Logo
            ============================================================ --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50">
                            <x-icon name="photo" class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">School Logo</h3>
                            <p class="text-xs text-gray-500">Shown on the login page and in navigation. JPG or PNG -- max 2 MB</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-6">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">

                        {{-- Current / preview --}}
                        <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-gray-200 bg-gray-50">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="h-full w-full object-contain p-2" alt="New logo preview">
                            </template>
                            <template x-if="!logoPreview">
                                @if (!empty($settings['school_logo']))
                                <img src="{{ Storage::url($settings['school_logo']) }}" class="h-full w-full object-contain p-2" alt="Current school logo">
                                @else
                                <x-icon name="building-library" class="h-10 w-10 text-gray-300" />
                                @endif
                            </template>
                        </div>

                        <div class="flex-1 space-y-3">
                            {{-- Upload zone --}}
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/60 px-4 py-3.5 transition-colors duration-150 hover:border-emerald-300 hover:bg-emerald-50/40">
                                <x-icon name="arrow-down-tray" class="h-5 w-5 shrink-0 text-gray-400" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-700">Click to upload a new logo</p>
                                    <p class="text-xs text-gray-400">JPG or PNG -- max 2 MB</p>
                                </div>
                                <input
                                    type="file"
                                    name="school_logo"
                                    accept="image/jpeg,image/png"
                                    class="hidden"
                                    @change="logoPreview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null">
                            </label>
                            <x-input-error :messages="$errors->get('school_logo')" class="mt-1" />

                        </div>
                    </div>
                </div>
            </div>

            {{-- Remove existing logo --}}
            @if (!empty($settings['school_logo']))
            <div class="flex justify-start">
                <button
                    type="button"
                    form="remove-logo-form"
                    class="flex items-center gap-1.5 text-xs font-medium text-red-500 hover:text-red-700"
                    onclick="return confirm('Remove the current school logo?')">
                    <x-icon name="trash" class="h-3.5 w-3.5" />
                    Remove current logo
                </button>
            </div>
            @endif

            {{-- ============================================================
                 CARD 3 -- QR Code Settings
            ============================================================ --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50">
                            <x-icon name="qr-code" class="h-5 w-5 text-indigo-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">QR Code</h3>
                            <p class="text-xs text-gray-500">Controls how long student QR tokens remain valid after generation</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-6">
                    <div>
                        <x-input-label for="qr_expiration_days" value="QR Code Expiration (days)" />
                        <p class="mt-0.5 text-xs text-gray-400">
                            New QR tokens use this lifetime. Existing tokens keep their saved expiration; older tokens without one expire based on their generation date and this setting.
                        </p>

                        <div class="mt-1.5 flex items-center gap-3">
                            <x-text-input
                                id="qr_expiration_days"
                                name="qr_expiration_days"
                                type="number"
                                min="1"
                                max="3650"
                                class="w-36"
                                :value="old('qr_expiration_days', $settings['qr_expiration_days'] ?? $systemSettings['qr_expiration_days'])"
                                required />
                            <span class="text-sm text-gray-500">days</span>
                        </div>
                        <x-input-error :messages="$errors->get('qr_expiration_days')" class="mt-1.5" />

                        {{-- Preset quick picks --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ([
                            ['30', '30 days'],
                            ['90', '3 months'],
                            ['180', '6 months'],
                            ['365', '1 year'],
                            ['3650', 'Never'],
                            ] as [$val, $label])
                            <button
                                type="submit"
                                onclick="document.getElementById('qr_expiration_days').value = '{{ $val }}'"
                                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition-colors duration-150 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 CARD 4 -- Face Recognition Settings
            ============================================================ --}}
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50">
                            <x-icon name="video-camera" class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Face Recognition</h3>
                            <p class="text-xs text-gray-500">Controls how strict the face comparison must be to pass verification</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-6">
                    <div
                        x-data="{
                            threshold: {{ (float) ($settings['face_match_threshold'] ?? $systemSettings['face_match_threshold']) }},
                            get label() {
                                if (this.threshold >= 0.90) return 'Very Strict';
                                if (this.threshold >= 0.75) return 'Strict';
                                if (this.threshold >= 0.60) return 'Balanced';
                                return 'Lenient';
                            },
                            get color() {
                                if (this.threshold >= 0.90) return 'text-red-600';
                                if (this.threshold >= 0.75) return 'text-amber-600';
                                if (this.threshold >= 0.60) return 'text-blue-600';
                                return 'text-emerald-600';
                            },
                        }">
                        <div class="flex items-center justify-between">
                            <x-input-label for="face_match_threshold" value="Face Match Threshold" />
                            <span class="text-sm font-semibold" :class="color">
                                <span x-text="threshold.toFixed(2)"></span>
                                --
                                <span x-text="label"></span>
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-400">
                            How confident the face comparison must be (0.50 = most lenient, 0.99 = strictest).
                            A higher value means fewer false-positives but more false-negatives on bad lighting.
                            The recommended range is 0.55 to 0.70.
                        </p>

                        {{-- Slider --}}
                        <input
                            type="range"
                            id="face_match_threshold"
                            name="face_match_threshold"
                            min="0.50"
                            max="0.99"
                            step="0.01"
                            x-model="threshold"
                            class="mt-4 w-full cursor-pointer accent-emerald-600">

                        {{-- Scale labels --}}
                        <div class="mt-1 flex justify-between text-xs text-gray-400">
                            <span>0.50 (Lenient)</span>
                            <span>0.65 (Balanced)</span>
                            <span>0.80 (Strict)</span>
                            <span>0.99 (Very strict)</span>
                        </div>

                        {{-- Visual indicator row --}}
                        <div class="mt-4 grid grid-cols-4 gap-2">
                            @foreach ([
                            ['0.50 - 0.59', 'Lenient', 'bg-emerald-50 border-emerald-200 text-emerald-700', 'Few false-negatives, some risk of false-positives'],
                            ['0.60 - 0.74', 'Balanced', 'bg-blue-50 border-blue-200 text-blue-700', 'Recommended for most classroom environments'],
                            ['0.75 - 0.89', 'Strict', 'bg-amber-50 border-amber-200 text-amber-700', 'More false-negatives in poor lighting'],
                            ['0.90 - 0.99', 'Very Strict', 'bg-red-50 border-red-200 text-red-700', 'Studio-quality lighting required'],
                            ] as [$range, $label, $tone, $note])
                            <div class="rounded-xl border px-3 py-2.5 {{ $tone }}">
                                <p class="text-xs font-semibold">{{ $label }}</p>
                                <p class="text-xs font-mono">{{ $range }}</p>
                                <p class="mt-1 text-xs opacity-75">{{ $note }}</p>
                            </div>
                            @endforeach
                        </div>

                        <x-input-error :messages="$errors->get('face_match_threshold')" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 SAVE BUTTON
            ============================================================ --}}
            <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white px-6 py-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Save all settings</p>
                    <p class="text-xs text-gray-400">Changes take effect immediately after saving</p>
                </div>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <button
                        type="submit"
                        form="settings-reset-form"
                        onclick="return confirm('Reset all settings to their defaults? This will also remove the uploaded school logo.')"
                        class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                        <x-icon name="arrow-path" class="h-4 w-4" />
                        Reset to Defaults
                    </button>
                    <button
                        type="submit"
                        class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                        <x-icon name="check-circle" class="h-5 w-5" />
                        Save Settings
                    </button>
                </div>
            </div>
        </form>

        <form id="settings-reset-form" method="POST" action="{{ route('admin.settings.reset') }}" class="hidden">
            @csrf
        </form>

        @if (!empty($settings['school_logo']))
        <form id="remove-logo-form" method="POST" action="{{ route('admin.settings.remove-logo') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </div>
</x-layouts.admin>