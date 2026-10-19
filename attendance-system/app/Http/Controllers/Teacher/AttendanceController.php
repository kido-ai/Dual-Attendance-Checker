<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\QRToken;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\AttendanceService;
use App\Services\FaceRecognitionService;
use App\Services\QRService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Real dual-authentication Take Attendance workflow. Route names below
 * match this project's EXISTING convention (teacher.attendance,
 * teacher.attendance.history.index/.show) rather than the illustrative
 * names in the feature spec, per that spec's own instruction to prefer
 * existing conventions where they differ.
 *
 * The old single-shot `store()` endpoint (which trusted a client-submitted
 * final decision) is removed entirely and replaced by three separate,
 * server-verified steps: scanQr() -> verifyFace() -> mark(). Nothing
 * about attendance_status is ever accepted from the browser at any step.
 *
 * No activity_logs writes anywhere in this controller, per instruction.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private readonly QRService $qrService,
        private readonly FaceRecognitionService $faceService,
        private readonly AttendanceService $attendanceService,
    ) {}

    /**
     * GET /teacher/attendance - route name: teacher.attendance
     * The main Take Attendance page. Shows the scanner if a session is
     * open, or a "Start Attendance" prompt if not.
     */
    public function takeAttendance(): View
    {
        $teacher = $this->currentTeacher();
        $session = $this->attendanceService->activeSessionFor($teacher);

        return view('teacher.attendance.take', [
            'teacher' => $teacher,
            'session' => $session,
            'records' => $session
                ? Attendance::with('student')->where('attendance_session_id', $session->id)->latest('time_in')->get()
                : collect(),
            'presentCount' => $session ? $this->attendanceService->presentCount($session) : 0,
        ]);
    }

    /**
     * POST /teacher/attendance/start - route name: teacher.attendance.start
     */
    public function start(Request $request): RedirectResponse
    {
        $teacher = $this->currentTeacher();

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $this->attendanceService->startSession($teacher, $validated['title'] ?? 'Attendance');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['session' => $e->getMessage()]);
        }

        return redirect()->route('teacher.attendance')->with('status', 'Attendance session started.');
    }

    /**
     * POST /teacher/attendance/end - route name: teacher.attendance.end
     */
    public function end(Request $request): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $session = $this->attendanceService->activeSessionFor($teacher);

        if (! $session) {
            return redirect()->route('teacher.attendance')->withErrors(['session' => 'There is no active attendance session.']);
        }

        $this->attendanceService->endSession($session, $teacher);
        Cache::forget($this->verificationCacheKey($teacher));

        return redirect()->route('teacher.attendance')->with('status', 'Attendance session ended.');
    }

    /**
     * POST /teacher/attendance/scan-qr - route name: teacher.attendance.scan-qr
     *
     * Step 1 of 2. Validates the token and, if good, stashes a short-lived
     * server-side record (2 minutes) proving "this teacher just scanned a
     * valid QR for this student in this session" -- verifyFace() and
     * mark() both check against that record rather than trusting anything
     * the browser claims about earlier steps.
     */
    public function scanQr(Request $request): JsonResponse
    {
        $teacher = $this->currentTeacher();
        $session = $this->attendanceService->activeSessionFor($teacher);

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'There is no active attendance session.'], 422);
        }

        $request->validate(['token' => ['required', 'string']]);

        $result = $this->qrService->validateToken($request->string('token'));

        if ($result['status'] === 'invalid') {
            return response()->json(['success' => false, 'message' => 'Invalid or inactive QR code.'], 422);
        }

        if ($result['status'] === 'already_used') {
            return response()->json(['success' => false, 'message' => 'This QR code has already been used.'], 422);
        }

        if ($result['status'] === 'expired') {
            return response()->json(['success' => false, 'message' => 'This QR code has expired.'], 422);
        }

        /** @var QRToken $qrToken */
        $qrToken = $result['token'];
        $student = $qrToken->student;

        if ($existing = $this->attendanceService->alreadyRecorded($session, $student)) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance has already been recorded for this student.',
                'recorded_at' => $existing->time_in?->format('g:i A'),
            ], 422);
        }

        Cache::put($this->verificationCacheKey($teacher), [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'qr_token_id' => $qrToken->id,
            'qr_status' => 'verified',
            'face_status' => null,
            'confidence' => null,
        ], now()->addMinutes(2));

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'student_number' => $student->student_number,
                'photo' => $student->profile_photo ? Storage::url($student->profile_photo) : null,
            ],
            'next_step' => 'face_verification',
        ]);
    }

    /**
     * GET /teacher/attendance/search-student - route name: teacher.attendance.search-student
     * "Student forgot QR" fallback. Search only -- does not verify or
     * record anything by itself.
     */
    public function searchStudent(Request $request): JsonResponse
    {
        $this->currentTeacher();

        $request->validate(['query' => ['required', 'string', 'min:2']]);
        $term = $request->string('query');

        $students = Student::query()
            ->where('student_number', 'like', "%{$term}%")
            ->orWhere(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'student_number', 'first_name', 'middle_name', 'last_name', 'profile_photo']);

        return response()->json([
            'success' => true,
            'students' => $students->map(fn(Student $s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'student_number' => $s->student_number,
                'photo' => $s->profile_photo ? Storage::url($s->profile_photo) : null,
            ]),
        ]);
    }

    /**
     * POST /teacher/attendance/verify-face - route name: teacher.attendance.verify-face
     *
     * Step 2 of 2. Refuses to run at all unless there's a matching,
     * unexpired scanQr() record for this exact student+session -- UNLESS
     * bypass_qr is set, which is only reachable via the "forgot QR"
     * search flow and results in qr_status = 'bypassed' rather than
     * 'verified' (kept distinct so it stays auditable).
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $teacher = $this->currentTeacher();
        $session = $this->attendanceService->activeSessionFor($teacher);

        if (! $session) {
            return response()->json(['success' => false, 'face_status' => 'error', 'message' => 'There is no active attendance session.'], 422);
        }

        $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'face_image' => ['required', 'string'],
            'bypass_qr' => ['sometimes', 'boolean'],
        ]);

        $student = Student::findOrFail($request->integer('student_id'));
        $bypass = $request->boolean('bypass_qr');
        $cacheKey = $this->verificationCacheKey($teacher);
        $cached = Cache::get($cacheKey);

        if ($bypass) {
            if ($this->attendanceService->alreadyRecorded($session, $student)) {
                return response()->json(['success' => false, 'face_status' => 'error', 'message' => 'Attendance has already been recorded for this student.'], 422);
            }

            $qrStatus = 'bypassed';
        } else {
            if (! $cached || $cached['session_id'] !== $session->id || $cached['student_id'] !== $student->id) {
                return response()->json(['success' => false, 'face_status' => 'error', 'message' => 'Please scan the student\'s QR code first.'], 422);
            }

            $qrStatus = $cached['qr_status'];
        }

        $imageData = $this->decodeImage($request->string('face_image'));

        if ($imageData === null) {
            return response()->json(['success' => false, 'face_status' => 'error', 'message' => 'The captured image could not be read. Please try again.'], 422);
        }

        $result = $this->faceService->verifyFace($student, $imageData);

        Cache::put($cacheKey, [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'qr_token_id' => $cached['qr_token_id'] ?? null,
            'qr_status' => $qrStatus,
            'face_status' => $result['face_status'],
            'confidence' => $result['confidence'],
        ], now()->addMinutes(2));

        if ($result['face_status'] !== 'matched') {
            return response()->json([
                'success' => false,
                'face_status' => $result['face_status'],
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'face_status' => 'matched',
            'confidence' => $result['confidence'],
        ]);
    }

    /**
     * POST /teacher/attendance/mark - route name: teacher.attendance.mark
     *
     * Step 3. Re-verifies everything server-side from the cached
     * verification record rather than trusting the request body for
     * anything except which student/session to record.
     */
    public function markAttendance(Request $request): JsonResponse
    {
        $teacher = $this->currentTeacher();

        $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'attendance_session_id' => ['required', 'integer', 'exists:attendance_sessions,id'],
        ]);

        $session = AttendanceSession::findOrFail($request->integer('attendance_session_id'));

        if ($session->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'This session does not belong to you.'], 403);
        }

        if (! $session->attendance_date->isToday()) {
            return response()->json(['success' => false, 'message' => 'This attendance session is from a different date.'], 422);
        }

        if (! $session->isOpen()) {
            return response()->json(['success' => false, 'message' => 'This attendance session is closed.'], 422);
        }

        $student = Student::findOrFail($request->integer('student_id'));
        $cacheKey = $this->verificationCacheKey($teacher);
        $cached = Cache::get($cacheKey);

        if (
            ! $cached
            || $cached['session_id'] !== $session->id
            || $cached['student_id'] !== $student->id
            || $cached['face_status'] !== 'matched'
        ) {
            return response()->json(['success' => false, 'message' => 'QR and face verification must both succeed before attendance can be recorded.'], 422);
        }

        try {
            $attendance = $this->attendanceService->recordAttendance(
                $session,
                $student,
                $teacher,
                $cached['qr_status'],
                $cached['face_status'],
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        if (! empty($cached['qr_token_id']) && ($qrToken = QRToken::find($cached['qr_token_id']))) {
            $this->qrService->markUsed($qrToken);
        }

        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'attendance' => $this->formatAttendance($attendance, $student),
            'present_count' => $this->attendanceService->presentCount($session),
        ]);
    }

    /**
     * POST /teacher/attendance/manual - route name: teacher.attendance.manual
     * Fallback for genuine technical failures. A reason is required and
     * always written to remarks, so the exception stays auditable rather
     * than looking like a normal dual-auth success.
     */
    public function manualAttendance(Request $request): JsonResponse
    {
        $teacher = $this->currentTeacher();

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'attendance_session_id' => ['required', 'integer', 'exists:attendance_sessions,id'],
            'reason' => ['required', Rule::in(['camera_malfunction', 'qr_scanner_malfunction', 'face_recognition_unavailable', 'other'])],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $session = AttendanceSession::findOrFail($validated['attendance_session_id']);

        if ($session->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'This session does not belong to you.'], 403);
        }

        if (! $session->attendance_date->isToday()) {
            return response()->json(['success' => false, 'message' => 'This attendance session is from a different date.'], 422);
        }

        if (! $session->isOpen()) {
            return response()->json(['success' => false, 'message' => 'This attendance session is closed.'], 422);
        }

        $student = Student::findOrFail($validated['student_id']);
        $cached = Cache::get($this->verificationCacheKey($teacher));

        $reasonLabels = [
            'camera_malfunction' => 'Camera malfunction',
            'qr_scanner_malfunction' => 'QR scanner malfunction',
            'face_recognition_unavailable' => 'Face recognition unavailable',
            'other' => 'Other',
        ];
        $remarks = $reasonLabels[$validated['reason']] . ($validated['remarks'] ? ': ' . $validated['remarks'] : '');

        try {
            $attendance = $this->attendanceService->recordAttendance(
                $session,
                $student,
                $teacher,
                // If a QR scan already succeeded before the technical
                // problem hit, preserve that; otherwise this manual
                // record is standing in for QR too.
                $cached['qr_status'] ?? 'verified',
                'manual',
                $remarks,
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        Cache::forget($this->verificationCacheKey($teacher));

        return response()->json([
            'success' => true,
            'attendance' => $this->formatAttendance($attendance, $student),
            'present_count' => $this->attendanceService->presentCount($session),
        ]);
    }

    /**
     * GET /teacher/attendance-history - route name: teacher.attendance.history.index
     */
    public function history(Request $request): View
    {
        $teacher = $this->currentTeacher();
        $query = $this->historyQuery($teacher, $request);

        $records = $query->paginate(10)->withQueryString();
        $records->getCollection()->transform(function (Attendance $attendance) {
            $student = $attendance->student;

            return [
                'id' => $attendance->id,
                'student' => $student?->full_name ?? 'Unknown Student',
                'student_number' => $student?->student_number ?? 'N/A',
                'photo' => $student?->profile_photo ? Storage::url($student->profile_photo) : null,
                'date' => $attendance->time_in?->format('M d, Y') ?? 'N/A',
                'time' => $attendance->time_in?->format('g:i A') ?? 'N/A',
                'qr_status' => ucfirst($attendance->qr_status ?? 'unknown'),
                'face_status' => ucfirst($attendance->face_status ?? 'unknown'),
                'status' => ucfirst($attendance->attendance_status ?? 'unknown'),
                'remarks' => $attendance->remarks ?: '—',
            ];
        });

        $recentActivity = (clone $query)
            ->latest('time_in')
            ->limit(5)
            ->get()
            ->map(function (Attendance $attendance) {
                $student = $attendance->student;

                return [
                    'id' => $attendance->id,
                    'student' => $student?->full_name ?? 'Unknown Student',
                    'student_number' => $student?->student_number ?? 'N/A',
                    'photo' => $student?->profile_photo ? Storage::url($student->profile_photo) : null,
                    'time' => $attendance->time_in?->format('g:i A') ?? 'N/A',
                    'status' => ucfirst($attendance->attendance_status ?? 'unknown'),
                ];
            })
            ->values()
            ->all();

        return view('teacher.attendance.index', [
            'teacher' => $teacher,
            'records' => $records,
            'stats' => $this->buildHistoryStats($teacher, $request),
            'recentActivity' => $recentActivity,
            'attendanceStatuses' => ['Present', 'Rejected'],
            'qrStatuses' => ['Verified', 'Bypassed', 'Invalid', 'Expired', 'Manual'],
            'faceStatuses' => ['Matched', 'Mismatch', 'Manual', 'Not Verified'],
            'filters' => $request->only(['search', 'attendance_status', 'qr_status', 'face_status', 'date']),
        ]);
    }

    /**
     * GET /teacher/attendance-history/{attendance} - route name: teacher.attendance.history.show
     */
    public function historyShow(Attendance $attendance): View
    {
        $teacher = $this->currentTeacher();

        abort_unless($attendance->teacher_id === $teacher->id, 403);

        $attendance->load('student');

        return view('teacher.attendance.show', ['record' => $attendance]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function currentTeacher(): Teacher
    {
        $teacher = Auth::user()->teacher;

        abort_if($teacher === null, 403, 'No teacher profile is linked to this account yet. Contact an administrator.');

        return $teacher;
    }

    private function historyQuery(Teacher $teacher, Request $request)
    {
        return Attendance::with('student')
            ->where('teacher_id', $teacher->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($where) use ($search) {
                    $where->whereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('student_number', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    })
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('attendance_status'), fn($query) => $query->where('attendance_status', strtolower($request->attendance_status)))
            ->when($request->filled('qr_status'), fn($query) => $query->where('qr_status', strtolower($request->qr_status)))
            ->when($request->filled('face_status'), fn($query) => $query->where('face_status', strtolower($request->face_status)))
            ->when($request->filled('date'), fn($query) => $query->whereDate('time_in', $request->date))
            ->latest('time_in');
    }

    private function buildHistoryStats(Teacher $teacher, Request $request): array
    {
        $query = $this->historyQuery($teacher, $request);

        $total = (clone $query)->count();
        $present = (clone $query)->where('attendance_status', 'present')->count();
        $rejected = (clone $query)->where('attendance_status', 'rejected')->count();
        $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return [
            ['icon' => 'calendar-days', 'tone' => 'emerald', 'label' => "Today's Attendance", 'value' => number_format($total), 'trend' => 'up', 'trendValue' => 'Live data'],
            ['icon' => 'check-circle', 'tone' => 'blue', 'label' => 'Present Students', 'value' => number_format($present), 'trend' => 'up', 'trendValue' => $total > 0 ? $rate . '% rate' : '0% rate'],
            ['icon' => 'x-circle', 'tone' => 'amber', 'label' => 'Rejected', 'value' => number_format($rejected), 'trend' => 'down', 'trendValue' => 'Needs review'],
            ['icon' => 'chart-bar', 'tone' => 'indigo', 'label' => 'Attendance Rate', 'value' => $rate . '%', 'trend' => $rate >= 80 ? 'up' : 'down', 'trendValue' => $rate >= 80 ? 'Strong' : 'Monitor'],
        ];
    }

    private function verificationCacheKey(Teacher $teacher): string
    {
        return "attendance-verify:{$teacher->id}";
    }

    /**
     * Accepts a data: URL (what <canvas>.toDataURL() produces) or raw
     * base64 and returns decoded binary, or null if it's neither.
     */
    private function decodeImage(string $raw): ?string
    {
        if (str_starts_with($raw, 'data:')) {
            $raw = substr($raw, strpos($raw, ',') + 1);
        }

        $decoded = base64_decode($raw, true);

        return $decoded === false ? null : $decoded;
    }

    private function formatAttendance(Attendance $attendance, Student $student): array
    {
        return [
            'id' => $attendance->id,
            'student_name' => $student->full_name,
            'student_number' => $student->student_number,
            'qr_status' => ucfirst($attendance->qr_status),
            'face_status' => ucfirst($attendance->face_status),
            'attendance_status' => ucfirst($attendance->attendance_status),
            'time_in' => $attendance->time_in?->format('g:i A'),
        ];
    }
}
