<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\QueryException;

/**
 * NOTE: no activity_logs writes anywhere in this class (per your
 * instruction not to introduce that table). If you want an audit trail
 * later, the natural place to add it is right where recordAttendance()
 * and startSession()/endSession() currently just return -- nothing else
 * about this service's structure would need to change.
 */
class AttendanceService
{
    /**
     * The teacher's currently open session for today, if any. Callers
     * use this to decide whether scanning is even allowed right now.
     */
    public function activeSessionFor(Teacher $teacher): ?AttendanceSession
    {
        return AttendanceSession::where('teacher_id', $teacher->id)
            ->open()
            ->today()
            ->latest('started_at')
            ->first();
    }

    /**
     * Start a new session for this teacher. Enforces one active session
     * per teacher per day and closes any sessions left open from earlier
     * dates.
     *
     * @throws \RuntimeException if a session is already open
     */
    public function startSession(Teacher $teacher, string $title = 'Attendance'): AttendanceSession
    {
        $now = now();

        AttendanceSession::where('teacher_id', $teacher->id)
            ->open()
            ->whereDate('attendance_date', '<', $now->toDateString())
            ->update([
                'status' => 'closed',
                'ended_at' => $now,
            ]);

        if ($this->activeSessionFor($teacher)) {
            throw new \RuntimeException('You already have an open attendance session.');
        }

        return AttendanceSession::create([
            'teacher_id' => $teacher->id,
            'title' => $title,
            'attendance_date' => $now->toDateString(),
            'started_at' => $now,
            'status' => 'open',
        ]);
    }

    public function endSession(AttendanceSession $session, Teacher $teacher): void
    {
        $session->close();
    }

    public function alreadyRecorded(AttendanceSession $session, Student $student): ?Attendance
    {
        return Attendance::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();
    }

    /**
     * The single place an Attendance row gets created. Every caller
     * (normal dual-auth flow, manual verification, forgot-QR flow) goes
     * through here so the duplicate check and the attendance_status
     * derivation logic exist in exactly one place.
     *
     * attendance_status is ALWAYS derived here from qr_status +
     * face_status -- callers cannot pass it in. This is the actual
     * enforcement of "never trust the frontend": even if every other
     * check somehow got bypassed upstream, this method itself refuses to
     * record 'present' unless the two inputs it received say so.
     *
     * @throws \RuntimeException if already recorded for this session
     */
    public function recordAttendance(
        AttendanceSession $session,
        Student $student,
        Teacher $teacher,
        string $qrStatus,
        string $faceStatus,
        ?string $remarks = null
    ): Attendance {
        if ($this->alreadyRecorded($session, $student)) {
            throw new \RuntimeException('Attendance has already been recorded for this student.');
        }


        $presentCombinations = [
            'verified:matched',
            'verified:manual',
            'bypassed:matched',
        ];
        $isPresent = in_array("{$qrStatus}:{$faceStatus}", $presentCombinations, true);

        try {
            return Attendance::create([
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'time_in' => now(),
                'qr_status' => $qrStatus,
                'face_status' => $faceStatus,
                'attendance_status' => $isPresent ? 'present' : 'rejected',
                'remarks' => $remarks,
            ]);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'unique_student_session')) {
                throw new \RuntimeException('Attendance has already been recorded for this student.');
            }

            throw $e;
        }
    }

    public function presentCount(AttendanceSession $session): int
    {
        return Attendance::where('attendance_session_id', $session->id)
            ->where('attendance_status', 'present')
            ->count();
    }
}
