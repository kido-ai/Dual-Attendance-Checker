{{--
    teacher/attendance/take.blade.php

    Real implementation. The camera feeds are genuine getUserMedia()
    streams: the QR step decodes frames with jsQR (CDN, no build step)
    against the live <video>, and the face step captures a still frame
    to a <canvas> and POSTs it as a data URL. Every state transition
    (validating / verifying / recording / error) is driven by actual
    fetch() responses from Teacher\AttendanceController -- there is no
    setTimeout()-simulated outcome anywhere in this file.
--}}
<x-layouts.teacher
    title="Take Attendance"
    :breadcrumbs="[['label' => 'Teacher'], ['label' => 'Take Attendance']]">
    <div
        x-data="takeAttendance({
            hasSession: {{ $session ? 'true' : 'false' }},
            sessionId: {{ $session?->id ?? 'null' }},
            sessionTitle: @js($session?->title),
            sessionStatus: @js($session?->status),
            startedAt: @js($session?->started_at?->format('g:i A')),
            presentCount: {{ $presentCount }},
            initialRecords: @js($records->map(fn ($r) => [
                'id' => $r->id,
                'student_number' => $r->student?->student_number,
                'name' => $r->student?->full_name,
                'time' => $r->time_in?->format('g:i A'),
                'qr_status' => $r->qr_status,
                'face_status' => $r->face_status,
                'status' => $r->attendance_status,
            ])),
            endpoints: {
                start: @js(route('teacher.attendance.start')),
                end: @js(route('teacher.attendance.end')),
                scanQr: @js(route('teacher.attendance.scan-qr')),
                verifyFace: @js(route('teacher.attendance.verify-face')),
                mark: @js(route('teacher.attendance.mark')),
                manual: @js(route('teacher.attendance.manual')),
                searchStudent: @js(route('teacher.attendance.search-student')),
            },
        })"
        x-init="init()"
        class="mx-auto max-w-7xl space-y-6">
        {{-- Header --}}
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Take Attendance</h2>
            <p class="mt-1 text-sm text-gray-500">Record student attendance using QR Code and Face Recognition.</p>
        </div>

        @if (session('status'))
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
        @endif
        @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Session card --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl" :class="hasSession ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400'">
                        <x-icon name="qr-code" class="h-7 w-7" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-lg font-semibold text-gray-900" x-text="hasSession ? sessionTitle : 'No Active Attendance'"></h3>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" :class="hasSession ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'">
                                <span class="h-1.5 w-1.5 rounded-full" :class="hasSession ? 'bg-emerald-600 animate-pulse' : 'bg-gray-400'"></span>
                                <span x-text="hasSession ? 'Open' : 'Closed'"></span>
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500" x-show="hasSession">Started <span x-text="startedAt"></span></p>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div x-show="hasSession" class="text-center">
                        <p class="text-2xl font-semibold text-emerald-600" x-text="presentCount"></p>
                        <p class="text-xs text-gray-500">Students Present</p>
                    </div>

                    <form :action="endpoints.start" method="POST" x-show="!hasSession">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                            <x-icon name="play" class="h-4 w-4" />
                            Start Attendance
                        </button>
                    </form>

                    <button type="button" x-show="hasSession" @click="showEndModal = true" class="flex items-center gap-2 rounded-xl border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition-colors duration-150 hover:bg-red-50">
                        <x-icon name="stop-circle" class="h-4 w-4" />
                        End Attendance
                    </button>
                </div>
            </div>
        </div>

        <div x-show="hasSession" class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">

                {{-- ============ QR SCANNER ============ --}}
                <div x-show="stage === 'qr'" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">QR Scanner</h3>
                            <p class="text-sm text-gray-500">Point the student's QR code at the camera.</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="qrBusy ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500'" x-text="qrBusy ? 'Validating QR...' : 'Scanning'"></span>
                    </div>

                    <div class="relative mt-5 aspect-video overflow-hidden rounded-xl bg-gray-900">
                        <video x-ref="qrVideo" class="h-full w-full object-cover" autoplay muted playsinline></video>
                        <canvas x-ref="qrCanvas" class="hidden"></canvas>
                        <div x-show="!cameraReady" class="absolute inset-0 flex items-center justify-center bg-gray-900 text-sm text-gray-400">
                            Starting camera...
                        </div>
                        <div class="pointer-events-none absolute inset-10 rounded-lg border-2 border-emerald-400/70"></div>
                    </div>

                    <p x-show="qrError" x-cloak class="mt-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700" x-text="qrError"></p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <button type="button" @click="openForgotQr()" class="flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            <x-icon name="search" class="h-4 w-4" />
                            Student Forgot QR
                        </button>
                        <button type="button" x-show="cameraError" x-cloak @click="startQrScanner()" class="flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            <x-icon name="arrow-path" class="h-4 w-4" />
                            Retry Camera
                        </button>
                    </div>

                    {{-- Camera error --}}
                    <div x-show="cameraError" x-cloak class="mt-4 rounded-xl border border-red-100 bg-red-50 p-4">
                        <p class="text-sm font-semibold text-red-700">Camera Error</p>
                        <p class="mt-1 text-sm text-red-600">Unable to access the camera. Please check camera permissions.</p>
                        <button type="button" @click="openManual(null)" class="mt-3 text-sm font-semibold text-red-700 underline">Use Manual Verification instead</button>
                    </div>
                </div>

                {{-- ============ STUDENT FOUND + FACE VERIFICATION ============ --}}
                <div x-show="stage === 'face'" x-cloak class="space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <img :src="currentStudent?.photo || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(currentStudent?.name || '') + '&background=E2E8F0&color=334155')" class="h-16 w-16 rounded-2xl object-cover" alt="">
                            <div>
                                <p class="text-base font-semibold text-gray-900" x-text="currentStudent?.name"></p>
                                <p class="text-sm text-gray-500" x-text="currentStudent?.student_number"></p>
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                    <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                    QR Verified
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Face Verification</h3>
                                <p class="text-sm text-gray-500">Position your face in the frame.</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="faceBusy ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500'" x-text="faceBusy ? 'Verifying face...' : 'Ready'"></span>
                        </div>

                        <div class="relative mt-5 aspect-video overflow-hidden rounded-xl bg-gray-900">
                            <video x-ref="faceVideo" class="h-full w-full object-cover" autoplay muted playsinline></video>
                            <canvas x-ref="faceCanvas" class="hidden"></canvas>
                            <div class="pointer-events-none absolute inset-10 rounded-full border-2 border-indigo-400/70"></div>
                        </div>

                        <p x-show="faceError" x-cloak class="mt-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700" x-text="faceError"></p>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <button type="button" :disabled="faceBusy" @click="captureFace()" class="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
                                <x-icon name="video-camera" class="h-4 w-4" />
                                Verify Face
                            </button>
                            <button type="button" @click="openManual(currentStudent)" class="flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                                Manual Verification
                            </button>
                            <button type="button" @click="cancelToScanning()" class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">
                                <x-icon name="x-mark" class="h-4 w-4" />
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ============ RESULT (success / rejected) ============ --}}
                <div
                    x-show="stage === 'result'"
                    x-cloak
                    class="rounded-2xl border p-6 shadow-sm sm:p-8"
                    :class="lastResult?.attendance_status === 'Present' ? 'border-emerald-100 bg-emerald-50/40' : 'border-red-100 bg-red-50/40'">
                    <div class="flex flex-col items-center gap-6 text-center sm:flex-row sm:text-left">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl" :class="lastResult?.attendance_status === 'Present' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                            <x-icon name="check-circle" class="h-11 w-11" x-show="lastResult?.attendance_status === 'Present'" />
                            <x-icon name="x-circle" class="h-11 w-11" x-show="lastResult?.attendance_status !== 'Present'" x-cloak />
                        </div>
                        <div class="flex-1">
                            <p class="text-lg font-semibold" :class="lastResult?.attendance_status === 'Present' ? 'text-emerald-700' : 'text-red-700'" x-text="lastResult?.attendance_status === 'Present' ? 'Both Factors Confirmed' : 'Verification Failed'"></p>
                            <p class="mt-1 text-sm text-gray-600" x-text="lastResult?.student_name"></p>
                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                                    <p class="text-xs text-gray-400">QR</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-800" x-text="lastResult?.qr_status"></p>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                                    <p class="text-xs text-gray-400">Face</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-800" x-text="lastResult?.face_status"></p>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                                    <p class="text-xs text-gray-400">Attendance</p>
                                    <p class="mt-0.5 text-sm font-semibold" :class="lastResult?.attendance_status === 'Present' ? 'text-emerald-600' : 'text-red-600'" x-text="lastResult?.attendance_status"></p>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-2.5 text-center shadow-sm">
                                    <p class="text-xs text-gray-400">Time</p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-800" x-text="lastResult?.time_in"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-center text-xs text-gray-400 sm:text-left">Preparing scanner for the next student&hellip;</p>
                </div>

                {{-- Real-time attendance list --}}
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-5">
                        <h3 class="text-base font-semibold text-gray-900">Recorded This Session</h3>
                        <p class="text-sm text-gray-500">Updates automatically after every student</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                                    <th class="px-5 py-3 font-medium">Student No.</th>
                                    <th class="px-5 py-3 font-medium">Name</th>
                                    <th class="px-5 py-3 font-medium">Time In</th>
                                    <th class="px-5 py-3 font-medium">QR</th>
                                    <th class="px-5 py-3 font-medium">Face</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="record in records" :key="record.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 text-gray-600" x-text="record.student_number"></td>
                                        <td class="px-5 py-3 font-medium text-gray-900" x-text="record.name"></td>
                                        <td class="px-5 py-3 text-gray-600" x-text="record.time"></td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="record.qr_status === 'verified' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'" x-text="record.qr_status"></span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="record.face_status === 'matched' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'" x-text="record.face_status"></span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="record.status === 'present' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'" x-text="record.status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="records.length === 0">
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">No students recorded yet this session.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Students Present</h3>
                    <p class="mt-2 text-4xl font-semibold text-emerald-600" x-text="presentCount"></p>
                    <p class="mt-1 text-xs text-gray-400">Live count for this session</p>
                </div>
            </div>
        </div>

        {{-- No active session placeholder --}}
        <div x-show="!hasSession" class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                <x-icon name="play" class="h-8 w-8 text-gray-300" />
            </div>
            <p class="mt-4 text-sm font-semibold text-gray-700">No Active Attendance</p>
            <p class="mt-1 text-sm text-gray-400">Click "Start Attendance" above to open the scanner.</p>
        </div>

        {{-- ============ End Attendance modal ============ --}}
        <x-modal show="showEndModal" close="showEndModal = false" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <x-icon name="stop-circle" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">End Attendance?</h3>
                <p class="mt-1.5 text-sm text-gray-500">Students can no longer be marked present after the session is closed.</p>
                <form :action="endpoints.end" method="POST" class="mt-6 flex justify-end gap-3">
                    @csrf
                    <button type="button" @click="showEndModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                        <x-icon name="stop-circle" class="h-4 w-4" />
                        End Attendance
                    </button>
                </form>
            </div>
        </x-modal>

        {{-- ============ Forgot QR modal ============ --}}
        <x-modal show="showForgotQrModal" close="showForgotQrModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900">Student Forgot QR</h3>
                <p class="mt-1 text-sm text-gray-500">Search by student number or name.</p>
                <div class="relative mt-4">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input type="text" x-model="searchQuery" @input.debounce.400ms="searchStudent()" placeholder="Search student..." class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-sm focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="mt-3 max-h-64 space-y-1.5 overflow-y-auto">
                    <template x-for="s in searchResults" :key="s.id">
                        <button type="button" @click="selectForgotQrStudent(s)" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left hover:bg-gray-50">
                            <img :src="s.photo || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(s.name))" class="h-9 w-9 rounded-full object-cover" alt="">
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="s.name"></p>
                                <p class="text-xs text-gray-500" x-text="s.student_number"></p>
                            </div>
                        </button>
                    </template>
                    <p x-show="searchQuery.length >= 2 && searchResults.length === 0" class="px-3 py-2 text-sm text-gray-400">No students found.</p>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="button" @click="showForgotQrModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</button>
                </div>
            </div>
        </x-modal>

        {{-- ============ Manual Verification modal ============ --}}
        <x-modal show="showManualModal" close="showManualModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-base font-semibold text-gray-900">Manual Attendance Verification</h3>
                <p class="mt-1 text-sm text-gray-500" x-show="manualStudent">
                    Student: <span class="font-medium text-gray-700" x-text="manualStudent?.name"></span>
                </p>
                <p class="mt-1 text-sm text-amber-600" x-show="!manualStudent">Select a student from the scanner or search first.</p>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Reason</label>
                    <div class="space-y-2">
                        <template x-for="opt in manualReasons" :key="opt.value">
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 px-4 py-2.5 has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50">
                                <input type="radio" name="manual_reason" :value="opt.value" x-model="manualReason" class="h-4 w-4 border-gray-300 text-emerald-600">
                                <span class="text-sm text-gray-700" x-text="opt.label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Additional Remarks</label>
                    <textarea x-model="manualRemarks" rows="2" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>

                <p x-show="manualError" x-cloak class="mt-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700" x-text="manualError"></p>

                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="showManualModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button type="button" :disabled="!manualStudent || !manualReason" @click="submitManual()" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Confirm Manual Verification
                    </button>
                </div>
            </div>
        </x-modal>
    </div>

    {{-- jsQR: pure-JS QR decoder, no build step, reads raw pixel data from a canvas frame --}}
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        function takeAttendance(config) {
            return {
                hasSession: config.hasSession,
                sessionId: config.sessionId,
                sessionTitle: config.sessionTitle,
                startedAt: config.startedAt,
                presentCount: config.presentCount,
                records: config.initialRecords,
                endpoints: config.endpoints,

                stage: 'qr', // 'qr' | 'face' | 'result'
                cameraReady: false,
                cameraError: false,
                qrBusy: false,
                qrError: null,
                faceBusy: false,
                faceError: null,
                currentStudent: null,
                bypassQr: false,
                lastResult: null,

                showEndModal: false,
                showForgotQrModal: false,
                showManualModal: false,
                searchQuery: '',
                searchResults: [],
                manualStudent: null,
                manualReason: '',
                manualRemarks: '',
                manualError: null,
                manualReasons: [{
                        value: 'camera_malfunction',
                        label: 'Camera malfunction'
                    },
                    {
                        value: 'qr_scanner_malfunction',
                        label: 'QR scanner malfunction'
                    },
                    {
                        value: 'face_recognition_unavailable',
                        label: 'Face recognition unavailable'
                    },
                    {
                        value: 'other',
                        label: 'Other'
                    },
                ],

                qrStream: null,
                faceStream: null,
                qrScanLoop: null,

                init() {
                    if (this.hasSession) {
                        this.startQrScanner();
                    }
                },

                csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                },

                async postJson(url, body) {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(body),
                    });
                    const data = await response.json().catch(() => ({}));
                    return {
                        ok: response.ok,
                        data
                    };
                },

                // ---------------- QR camera + decode loop ----------------
                async startQrScanner() {
                    this.cameraError = false;
                    this.cameraReady = false;
                    this.qrError = null;

                    try {
                        const constraints = {
                            video: {
                                facingMode: {
                                    ideal: 'environment'
                                },
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 720
                                },
                            },
                            audio: false,
                        };

                        this.qrStream = await navigator.mediaDevices.getUserMedia(constraints).catch(() => {
                            return navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: {
                                        ideal: 'user'
                                    },
                                    width: {
                                        ideal: 1280
                                    },
                                    height: {
                                        ideal: 720
                                    }
                                },
                                audio: false,
                            });
                        });

                        this.$refs.qrVideo.srcObject = this.qrStream;
                        this.$refs.qrVideo.setAttribute('playsinline', 'true');
                        await this.$refs.qrVideo.play();
                        this.cameraReady = true;
                        this.scanQrFrame();
                    } catch (e) {
                        this.cameraError = true;
                    }
                },

                stopQrScanner() {
                    if (this.qrScanLoop) cancelAnimationFrame(this.qrScanLoop);
                    if (this.qrStream) {
                        this.qrStream.getTracks().forEach(t => t.stop());
                        this.qrStream = null;
                    }
                },

                scanQrFrame() {
                    const video = this.$refs.qrVideo;
                    const canvas = this.$refs.qrCanvas;

                    if (!video || video.readyState !== video.HAVE_ENOUGH_DATA || this.qrBusy || this.stage !== 'qr') {
                        this.qrScanLoop = requestAnimationFrame(() => this.scanQrFrame());
                        return;
                    }

                    const maxWidth = 1200;
                    const maxHeight = 900;
                    const scale = Math.min(1, maxWidth / video.videoWidth, maxHeight / video.videoHeight);

                    canvas.width = Math.max(320, Math.round(video.videoWidth * scale));
                    canvas.height = Math.max(240, Math.round(video.videoHeight * scale));

                    const ctx = canvas.getContext('2d', {
                        willReadFrequently: true
                    });
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert',
                    });

                    if (code && code.data) {
                        this.handleQrDecoded(code.data);
                        return;
                    }

                    this.qrScanLoop = requestAnimationFrame(() => this.scanQrFrame());
                },

                async handleQrDecoded(token) {
                    this.qrBusy = true;
                    this.qrError = null;

                    const {
                        ok,
                        data
                    } = await this.postJson(this.endpoints.scanQr, {
                        token
                    });

                    this.qrBusy = false;

                    if (!ok || !data.success) {
                        this.qrError = data.message || 'Invalid or inactive QR code.';
                        // Resume scanning after a short pause so the same
                        // rejected code doesn't instantly re-trigger.
                        setTimeout(() => {
                            this.qrError = null;
                            this.scanQrFrame();
                        }, 1500);
                        return;
                    }

                    this.currentStudent = data.student;
                    this.bypassQr = false;
                    this.stopQrScanner();
                    this.stage = 'face';
                    this.$nextTick(() => this.startFaceCamera());
                },

                // ---------------- Face camera + capture ----------------
                async startFaceCamera() {
                    try {
                        this.faceStream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: 'user'
                            }
                        });
                        this.$refs.faceVideo.srcObject = this.faceStream;
                        await this.$refs.faceVideo.play();
                    } catch (e) {
                        this.faceError = 'Unable to access the camera. Please check camera permissions.';
                    }
                },

                stopFaceCamera() {
                    if (this.faceStream) {
                        this.faceStream.getTracks().forEach(t => t.stop());
                        this.faceStream = null;
                    }
                },

                captureFrame(videoRef, canvasRef) {
                    const video = this.$refs[videoRef];
                    const canvas = this.$refs[canvasRef];
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                    return canvas.toDataURL('image/jpeg', 0.85);
                },

                async captureFace() {
                    if (!this.currentStudent) return;

                    this.faceBusy = true;
                    this.faceError = null;
                    const image = this.captureFrame('faceVideo', 'faceCanvas');

                    const {
                        ok,
                        data
                    } = await this.postJson(this.endpoints.verifyFace, {
                        student_id: this.currentStudent.id,
                        face_image: image,
                        bypass_qr: this.bypassQr,
                    });

                    this.faceBusy = false;

                    if (!ok || !data.success) {
                        this.faceError = data.message || 'Face verification failed.';

                        if (data.face_status === 'error') {
                            // Technical failure -> surface Manual Verification.
                            this.faceError += ' You may use Manual Verification below.';
                        }
                        return;
                    }

                    await this.finalizeAttendance();
                },

                async finalizeAttendance() {
                    const {
                        ok,
                        data
                    } = await this.postJson(this.endpoints.mark, {
                        student_id: this.currentStudent.id,
                        attendance_session_id: this.sessionId,
                    });

                    this.stopFaceCamera();

                    if (!ok || !data.success) {
                        this.faceError = data.message || 'Attendance could not be recorded.';
                        return;
                    }

                    this.applyResult(data);
                },

                applyResult(data) {
                    this.lastResult = data.attendance;
                    this.presentCount = data.present_count;
                    this.records.unshift({
                        id: data.attendance.id,
                        student_number: data.attendance.student_number,
                        name: data.attendance.student_name,
                        time: data.attendance.time_in,
                        qr_status: data.attendance.qr_status.toLowerCase(),
                        face_status: data.attendance.face_status.toLowerCase(),
                        status: data.attendance.attendance_status.toLowerCase(),
                    });
                    this.stage = 'result';

                    setTimeout(() => this.resetToScanning(), 2500);
                },

                resetToScanning() {
                    this.currentStudent = null;
                    this.lastResult = null;
                    this.bypassQr = false;
                    this.stage = 'qr';
                    this.$nextTick(() => this.startQrScanner());
                },

                cancelToScanning() {
                    this.stopFaceCamera();
                    this.resetToScanning();
                },

                // ---------------- Forgot QR ----------------
                openForgotQr() {
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.showForgotQrModal = true;
                },

                async searchStudent() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    const response = await fetch(`${this.endpoints.searchStudent}?query=${encodeURIComponent(this.searchQuery)}`, {
                        headers: {
                            'Accept': 'application/json'
                        },
                    });
                    const data = await response.json();
                    this.searchResults = data.students || [];
                },

                selectForgotQrStudent(student) {
                    this.currentStudent = student;
                    this.bypassQr = true;
                    this.showForgotQrModal = false;
                    this.stopQrScanner();
                    this.stage = 'face';
                    this.$nextTick(() => this.startFaceCamera());
                },

                // ---------------- Manual verification ----------------
                openManual(student) {
                    this.manualStudent = student || this.currentStudent;
                    this.manualReason = '';
                    this.manualRemarks = '';
                    this.manualError = null;
                    this.showManualModal = true;
                },

                async submitManual() {
                    const {
                        ok,
                        data
                    } = await this.postJson(this.endpoints.manual, {
                        student_id: this.manualStudent.id,
                        attendance_session_id: this.sessionId,
                        reason: this.manualReason,
                        remarks: this.manualRemarks,
                    });

                    if (!ok || !data.success) {
                        this.manualError = data.message || 'Manual verification failed.';
                        return;
                    }

                    this.showManualModal = false;
                    this.stopFaceCamera();
                    this.applyResult(data);
                },
            };
        }
    </script>
</x-layouts.teacher>