@php
$hasQrCode = !empty($qr);

$instructions = [
['icon' => 'qr-code', 'text' => 'Present your QR Code to the teacher.'],
['icon' => 'shield-check', 'text' => 'Wait for QR verification.'],
['icon' => 'video-camera', 'text' => 'Look directly at the camera for face verification.'],
['icon' => 'check-circle', 'text' => 'Attendance is recorded only after both QR Code and Face Recognition are successfully verified.'],
];
@endphp

<x-layouts.student
    title="My QR Code"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'My QR Code']]">
    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-900">My QR Code</h2>
            <p class="mt-1 text-sm text-gray-500">Access your personal QR Code for attendance authentication.</p>
        </div>

        @if ($hasQrCode)
        <div x-data="{ fullscreenOpen: false }" class="space-y-6">

            {{-- Main QR card + sidebar info --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    @include('student.qr.partials._qr-card')
                </div>
                <div class="space-y-6 lg:col-span-1">
                    @include('student.qr.partials._qr-information')
                    @include('student.qr.partials._face-status')
                </div>
            </div>

            {{-- Quick Instructions --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900">How to Use Your QR Code</h3>
                <p class="text-xs text-gray-500">The Dual Authentication process, step by step</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($instructions as $i => $step)
                    <div class="flex gap-3 rounded-xl bg-gray-50 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <x-icon :name="$step['icon']" class="h-5 w-5" />
                        </div>
                        <p class="text-sm text-gray-600">{{ $step['text'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Security notice --}}
            @include('student.qr.partials._security-notice')

            {{-- Fullscreen QR modal --}}
            <x-modal show="fullscreenOpen" max-width="md">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $student['name'] }}'s QR Code</h3>
                    <button type="button" @click="fullscreenOpen = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>
                <div class="flex flex-col items-center p-8">
                    <div id="student-qr-fullscreen"
                        data-token="{{ $qr['token'] }}"
                        class="flex h-72 w-72 items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/60">
                        <span class="text-xs text-gray-400">Rendering QR…</span>
                    </div>
                    <p class="mt-4 text-sm font-medium text-gray-700">{{ $qr['student_number'] }}</p>
                    <p class="text-xs text-gray-400">Present this screen to your teacher for scanning</p>
                </div>
            </x-modal>
        </div>
        <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const qrContainers = [
                    document.getElementById('student-qr-inline'),
                    document.getElementById('student-qr-fullscreen')
                ];

                qrContainers.forEach(function(el) {
                    if (!el || !window.QRCode) return;

                    const token = el.dataset.token;
                    if (!token) return;

                    el.innerHTML = '';
                    new QRCode(el, {
                        text: token,
                        width: el.classList.contains('h-72') ? 220 : 176,
                        height: el.classList.contains('h-72') ? 220 : 176,
                        colorDark: '#111827',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                });
            });
        </script>
    </div>
    @else
    {{-- Empty state --}}
    <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-16 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
            <x-icon name="qr-code" class="h-8 w-8 text-gray-300" />
        </div>
        <p class="mt-4 text-sm font-semibold text-gray-700">No QR Code Assigned</p>
        <p class="mt-1 text-sm text-gray-400">Please contact your administrator to generate your QR Code.</p>
        <a
            href="{{ route('student.dashboard') }}"
            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
            <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
            Back to Dashboard
        </a>
    </div>
    @endif
    </div>
</x-layouts.student>