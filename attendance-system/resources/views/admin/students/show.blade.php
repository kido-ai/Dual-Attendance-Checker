{{--
    admin/students/show.blade.php
    Data from Admin\StudentController::show(Student $student)
    $student     - Student model (user, qrToken, faceProfile, attendances)
    $qrImageSrc  - optional base64 data-URI. If null but a token exists,
                   the QR is rendered client-side from the token string.
--}}
@php
$qrImageSrc = $qrImageSrc ?? null;

$hasToken = (bool) $student->qrToken;
$tokenExpired = $hasToken && method_exists($student->qrToken, 'isExpired')
? $student->qrToken->isExpired()
: false;
$canRenderQr = $hasToken && ! $tokenExpired;

// Profile photo — PUBLIC disk explicitly (Storage::url() uses default disk).
$profilePhotoUrl = null;
if ($student->profile_photo
&& Storage::disk('public')->exists($student->profile_photo)) {
$profilePhotoUrl = Storage::disk('public')->url($student->profile_photo);
}
$profilePhotoUrl ??= 'https://ui-avatars.com/api/?name='
. urlencode($student->full_name)
. '&background=059669&color=fff&size=128';
@endphp

<x-layouts.admin
    title="Student Profile"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Student Management'], ['label' => $student->full_name]]">
    <div class="mx-auto max-w-6xl space-y-6">

        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
            <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
            Back to Student Management
        </a>

        @if (session('status'))
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
        @endif

        @if (session('success'))
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            {{ session('error') }}
        </div>
        @endif

        {{-- Header profile card --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <img src="{{ $profilePhotoUrl }}"
                    alt="{{ $student->full_name }}"
                    class="h-20 w-20 rounded-2xl object-cover shadow-sm">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $student->full_name }}</h2>
                        <x-status-badge :status="ucfirst($student->status)" />
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $student->student_number }} &middot; {{ $student->course }} &middot;
                        {{ $student->year_level }} {{ $student->section }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.students.edit', $student) }}"
                        class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left column --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Personal Information --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Personal Information</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ([
                        ['icon' => 'envelope', 'label' => 'Email', 'value' => $student->user?->email],
                        ['icon' => 'phone', 'label' => 'Phone', 'value' => $student->phone ?: 'Not set'],
                        ['icon' => 'map-pin', 'label' => 'Address', 'value' => $student->address ?: 'Not set'],
                        ['icon' => 'identification', 'label' => 'Student Number', 'value' => $student->student_number],
                        ['icon' => 'academic-cap', 'label' => 'Course', 'value' => $student->course ?: 'Not set'],
                        ['icon' => 'calendar-days', 'label' => 'Member Since', 'value' => $student->created_at?->format('M j, Y')],
                        ] as $field)
                        <div class="flex items-center gap-2.5 rounded-xl bg-gray-50 px-4 py-3">
                            <x-icon :name="$field['icon']" class="h-4 w-4 shrink-0 text-gray-400" />
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">{{ $field['label'] }}</p>
                                <p class="truncate text-sm font-medium text-gray-800">{{ $field['value'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Recent attendance --}}
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-5">
                        <h3 class="text-sm font-semibold text-gray-900">Recent Attendance</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                    <th class="px-5 py-3 font-medium">Date</th>
                                    <th class="px-5 py-3 font-medium">Time</th>
                                    <th class="px-5 py-3 font-medium">QR</th>
                                    <th class="px-5 py-3 font-medium">Face</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($student->attendances as $attendance)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 text-gray-700">{{ $attendance->time_in?->format('M j, Y') }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $attendance->time_in?->format('g:i A') }}</td>
                                    <td class="px-5 py-3"><x-status-badge :status="ucfirst($attendance->qr_status)" /></td>
                                    <td class="px-5 py-3"><x-status-badge :status="ucfirst($attendance->face_status)" /></td>
                                    <td class="px-5 py-3"><x-status-badge :status="ucfirst($attendance->attendance_status)" /></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">
                                        No attendance records yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Face enrollment --}}
                @include('admin.students.partials._face-enrollment', ['student' => $student])
            </div>

            {{-- Right column --}}
            <div class="space-y-6">

                {{-- QR code card --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">QR Code</h3>
                        @if ($hasToken)
                        <x-status-badge :status="$tokenExpired ? 'Expired' : ucfirst($student->qrToken->status)" />
                        @else
                        <x-status-badge status="Not Assigned" />
                        @endif
                    </div>

                    <div class="mt-4 flex flex-col items-center">
                        @if ($qrImageSrc)
                        {{-- Preferred: server-side PNG exists --}}
                        <div class="flex h-48 w-48 items-center justify-center rounded-2xl bg-white p-2 shadow-inner ring-1 ring-gray-100">
                            <img src="{{ $qrImageSrc }}" alt="Student QR Code" class="h-full w-full">
                        </div>
                        <p class="mt-2 text-xs text-gray-400">
                            Token: {{ Str::limit($student->qrToken->token, 20) }}
                        </p>
                        @elseif ($canRenderQr)
                        {{-- Fallback: token exists, PNG missing → render client-side --}}
                        <div id="qr-inline"
                            data-token="{{ $student->qrToken->token }}"
                            class="flex h-48 w-48 items-center justify-center rounded-2xl bg-white p-2 shadow-inner ring-1 ring-gray-100">
                            <span class="text-xs text-gray-400">Rendering QR…</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-400">
                            Token: {{ Str::limit($student->qrToken->token, 20) }}
                        </p>
                        @else
                        {{-- No token (or expired): show CTA --}}
                        <div class="flex h-48 w-48 items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50">
                            <x-icon name="qr-code" class="h-20 w-20 text-gray-200" />
                        </div>
                        @endif

                        {{-- Regenerate / Generate button --}}
                        <form method="POST"
                            action="{{ route('admin.students.regenerate-qr', $student) }}"
                            class="mt-3">
                            @csrf
                            <button type="submit"
                                class="{{ $hasToken
                                        ? 'flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50'
                                        : 'flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700' }}"
                                onclick="return confirm('{{ $hasToken ? 'Issue a new QR token? The old QR code will stop working.' : 'Generate a QR code for this student?' }}')">
                                <x-icon :name="$hasToken ? 'arrow-path' : 'plus'" class="h-3.5 w-3.5" />
                                {{ $hasToken ? 'Regenerate QR' : 'Generate QR Code' }}
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Attendance Summary --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Attendance Summary</h3>
                    @php
                    $total = $student->attendances->count();
                    $present = $student->attendances->where('attendance_status', 'present')->count();
                    $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                    @endphp
                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="text-sm text-gray-500">Total Records</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $total }}</span>
                        </div>
                        <div class="flex justify-between rounded-xl bg-emerald-50 px-4 py-3">
                            <span class="text-sm text-emerald-600">Present</span>
                            <span class="text-sm font-semibold text-emerald-700">{{ $present }}</span>
                        </div>
                        <div class="flex justify-between rounded-xl bg-gray-50 px-4 py-3">
                            <span class="text-sm text-gray-500">Attendance Rate</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $rate }}%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $rate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Client-side QR renderer (only runs when qr-inline exists). --}}
    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('qr-inline');
            if (!el || !window.QRCode) return;

            var token = el.dataset.token;
            el.innerHTML = ''; // clear the "Rendering QR…" placeholder

            new QRCode(el, {
                text: token,
                width: 176,
                height: 176,
                colorDark: '#111827',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    </script>
</x-layouts.admin>