{{--
    teacher/attendance/start.blade.php

    The core Dual Authentication workflow. Everything below the header is
    UI-only, per the brief: there is no real QR decoding or face
    recognition here. All of it is simulated through a single Alpine
    component (takeAttendanceSession(), defined in the <script> block at
    the bottom of this file) that cycles through a small pool of sample
    students so every QR/Face/Attendance badge state can be demonstrated
    by repeatedly clicking through the flow.
--}}
<x-layouts.teacher
    title="Take Attendance"
    :breadcrumbs="[['label' => 'Teacher'], ['label' => 'Take Attendance']]"
>
    <div x-data="takeAttendanceSession()" class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Take Attendance</h2>
            <p class="mt-1 text-sm text-gray-500">Record student attendance using QR Code and Face Recognition.</p>
        </div>

        {{-- Attendance Session card --}}
        @include('teacher.attendance.partials._session-card')

        {{-- Step indicator --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <ol class="flex items-center">
                @php
                    $steps = [
                        1 => ['label' => 'Scan QR Code', 'icon' => 'qr-code'],
                        2 => ['label' => 'Validate QR', 'icon' => 'shield-check'],
                        3 => ['label' => 'Face Recognition', 'icon' => 'video-camera'],
                        4 => ['label' => 'Attendance Result', 'icon' => 'clipboard-check'],
                    ];
                @endphp
                @foreach ($steps as $number => $step)
                    <li class="flex flex-1 items-center {{ $loop->last ? 'flex-none' : '' }}">
                        <div class="flex flex-col items-center gap-2 {{ $loop->last ? '' : 'flex-1' }}">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-semibold transition-colors duration-200"
                                x-bind:class="{
                                    'border-emerald-600 bg-emerald-600 text-white': currentStep > {{ $number }},
                                    'border-emerald-600 bg-white text-emerald-600': currentStep === {{ $number }},
                                    'border-gray-200 bg-white text-gray-400': currentStep < {{ $number }},
                                }"
                            >
                                <template x-if="currentStep > {{ $number }}">
                                    <x-icon name="check-circle" class="h-5 w-5" />
                                </template>
                                <template x-if="currentStep <= {{ $number }}">
                                    <span>{{ $number }}</span>
                                </template>
                            </div>
                            <span
                                class="hidden text-center text-xs font-medium sm:block"
                                x-bind:class="currentStep >= {{ $number }} ? 'text-gray-800' : 'text-gray-400'"
                            >{{ $step['label'] }}</span>
                        </div>
                        @if (!$loop->last)
                            <div class="mx-2 h-0.5 flex-1 rounded-full transition-colors duration-200 sm:mx-3" x-bind:class="currentStep > {{ $number }} ? 'bg-emerald-600' : 'bg-gray-200'"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- Main grid: QR + Face + Result (left/main) and Stats (right/sidebar) --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div
                    x-show="sessionOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    class="space-y-6"
                >
                    @include('teacher.attendance.partials._qr-scanner')
                    @include('teacher.attendance.partials._face-recognition')
                    @include('teacher.attendance.partials._result-card')
                </div>

                {{-- Session-closed placeholder --}}
                <div x-show="!sessionOpen" x-cloak class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                        <x-icon name="play" class="h-8 w-8 text-gray-300" />
                    </div>
                    <p class="mt-4 text-sm font-semibold text-gray-700">Attendance session is closed</p>
                    <p class="mt-1 text-sm text-gray-400">Click "Start Attendance" above to begin scanning students.</p>
                </div>

                @include('teacher.attendance.partials._recent-table')
            </div>

            <div class="xl:col-span-1">
                @include('teacher.attendance.partials._statistics')
            </div>
        </div>

        {{-- Start Attendance modal --}}
        <x-modal show="showStartModal" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <x-icon name="play" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">Start attendance session?</h3>
                <p class="mt-1.5 text-sm text-gray-500">
                    This opens QR and Face Recognition scanning for Database Systems, BSIT 3-A. Students can begin
                    checking in as soon as the session starts.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showStartModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="startSession()" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        <x-icon name="play" class="h-4 w-4" />
                        Start Attendance
                    </button>
                </div>
            </div>
        </x-modal>

        {{-- End Attendance modal --}}
        <x-modal show="showEndModal" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <x-icon name="stop-circle" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">End attendance session?</h3>
                <p class="mt-1.5 text-sm text-gray-500">
                    Students will no longer be able to check in for this session once it's closed. You've recorded
                    <span x-text="recentRecords.length"></span> attendance entr<span x-text="recentRecords.length === 1 ? 'y' : 'ies'"></span> so far.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showEndModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="endSession()" class="flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                        <x-icon name="stop-circle" class="h-4 w-4" />
                        End Attendance
                    </button>
                </div>
            </div>
        </x-modal>

        {{-- Attendance Success modal --}}
        <x-modal show="showSuccessModal" max-width="sm">
            <div class="p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <x-icon name="check-circle" class="h-9 w-9" />
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Attendance Successfully Recorded</h3>
                <p class="mt-1 text-sm font-medium text-gray-700" x-text="currentStudent?.name"></p>
                <p class="text-sm text-gray-500" x-text="'Student No. ' + (currentStudent?.number ?? '')"></p>
                <p class="mt-2 text-xs text-gray-400" x-text="'Recorded at ' + attendanceTime"></p>
                <button
                    type="button"
                    @click="scanNext()"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700"
                >
                    <x-icon name="arrow-right" class="h-4 w-4" />
                    Scan Next Student
                </button>
            </div>
        </x-modal>

        {{-- Attendance Failed modal --}}
        <x-modal show="showFailedModal" max-width="sm">
            <div class="p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <x-icon name="exclamation-triangle" class="h-9 w-9" />
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Attendance Failed</h3>
                <p class="mt-1 text-sm font-medium text-red-600" x-text="failureReason"></p>
                <p class="mt-1 text-sm text-gray-500" x-text="currentStudent?.name"></p>
                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="tryAgain()"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        <x-icon name="arrow-path" class="h-4 w-4" />
                        Try Again
                    </button>
                    <button
                        type="button"
                        @click="scanNext()"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700"
                    >
                        <x-icon name="arrow-right" class="h-4 w-4" />
                        Scan Another
                    </button>
                </div>
            </div>
        </x-modal>
    </div>

    {{-- Alpine component: simulates the Dual Authentication flow with sample data. --}}
    <script>
        function takeAttendanceSession() {
            return {
                sessionOpen: false,
                currentStep: 1,
                scanning: false,
                faceScanning: false,
                qrResult: null,
                faceResult: null,
                faceConfidence: null,
                currentStudent: null,
                attendanceOutcome: null,
                attendanceTime: null,
                recentRecords: [],
                cursor: 0,
                showStartModal: false,
                showEndModal: false,
                showSuccessModal: false,
                showFailedModal: false,

                // Demo pool cycled through on each scan so every QR/Face/
                // Attendance badge state gets exercised when testing the UI.
                studentPool: [
                    { name: 'Ana Reyes', number: '2023-01452', qr: 'Verified', face: 'Matched', confidence: 96 },
                    { name: 'Miguel Torres', number: '2023-01488', qr: 'Verified', face: 'Matched', confidence: 94 },
                    { name: 'Josef Aquino', number: '2023-01377', qr: 'Invalid', face: null, confidence: null },
                    { name: 'Kyla Mendoza', number: '2023-01503', qr: 'Expired', face: null, confidence: null },
                    { name: 'Bianca Ramos', number: '2023-01412', qr: 'Verified', face: 'Mismatch', confidence: 42 },
                    { name: 'Carlos Villanueva', number: '2022-00981', qr: 'Verified', face: 'Not Recognized', confidence: 21 },
                    { name: 'Trisha Dela Cruz', number: '2024-00089', qr: 'Already Used', face: null, confidence: null },
                    { name: 'Ryan Santos', number: '2024-00104', qr: 'Verified', face: 'Matched', confidence: 98 },
                ],

                get failureReason() {
                    if (!this.qrResult) return '';
                    if (this.qrResult !== 'Verified') {
                        return this.qrResult === 'Expired' ? 'QR Expired'
                            : this.qrResult === 'Already Used' ? 'QR Already Used'
                            : 'Invalid QR Code';
                    }
                    if (this.faceResult === 'Mismatch') return 'Face Mismatch';
                    if (this.faceResult === 'Not Recognized') return 'Face Not Recognized';
                    return 'Verification Failed';
                },

                startSession() {
                    this.sessionOpen = true;
                    this.showStartModal = false;
                },
                endSession() {
                    this.sessionOpen = false;
                    this.showEndModal = false;
                    this.resetFlow();
                },
                resetFlow() {
                    this.currentStep = 1;
                    this.scanning = false;
                    this.faceScanning = false;
                    this.qrResult = null;
                    this.faceResult = null;
                    this.faceConfidence = null;
                    this.currentStudent = null;
                    this.attendanceOutcome = null;
                },
                nextStudent() {
                    const student = this.studentPool[this.cursor % this.studentPool.length];
                    this.cursor++;
                    return student;
                },
                scanQr() {
                    this.scanning = true;
                    this.currentStudent = this.nextStudent();
                    setTimeout(() => {
                        this.scanning = false;
                        this.currentStep = 2;
                        this.qrResult = this.currentStudent.qr;
                        setTimeout(() => {
                            if (this.qrResult === 'Verified') {
                                this.currentStep = 3;
                            } else {
                                this.finishAttendance('Rejected');
                            }
                        }, 500);
                    }, 900);
                },
                stopScanner() {
                    this.scanning = false;
                    this.currentStudent = null;
                },
                rescan() {
                    this.qrResult = null;
                    this.currentStep = 1;
                    this.currentStudent = null;
                },
                captureFace() {
                    this.faceScanning = true;
                    setTimeout(() => {
                        this.faceScanning = false;
                        this.faceResult = this.currentStudent.face;
                        this.faceConfidence = this.currentStudent.confidence;
                        this.finishAttendance(this.faceResult === 'Matched' ? 'Present' : 'Rejected');
                    }, 1100);
                },
                retryFace() {
                    this.faceResult = null;
                    this.faceConfidence = null;
                    this.attendanceOutcome = null;
                },
                cancelFace() {
                    this.resetFlow();
                },
                finishAttendance(outcome) {
                    this.attendanceOutcome = outcome;
                    this.attendanceTime = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    this.currentStep = 4;
                    this.recentRecords.unshift({
                        id: Date.now() + '-' + this.cursor,
                        name: this.currentStudent.name,
                        number: this.currentStudent.number,
                        time: this.attendanceTime,
                        qr: this.qrResult,
                        face: this.faceResult,
                        status: outcome,
                    });
                    if (outcome === 'Present') {
                        this.showSuccessModal = true;
                    } else {
                        this.showFailedModal = true;
                    }
                },
                tryAgain() {
                    this.showFailedModal = false;
                    if (this.qrResult !== 'Verified') {
                        this.rescan();
                    } else {
                        this.currentStep = 3;
                        this.retryFace();
                    }
                },
                scanNext() {
                    this.showSuccessModal = false;
                    this.showFailedModal = false;
                    this.resetFlow();
                },
            };
        }
    </script>
</x-layouts.teacher>
