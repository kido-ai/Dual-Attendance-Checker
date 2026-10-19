<?php 
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()?->student;
        abort_if($student === null, 403, 'No student profile is linked to this account yet. Contact an administrator.');

        $student->load(['attendances.teacher', 'qrToken', 'faceProfile']);

        $attendanceRecords = $student->attendances()
            ->latest('time_in')
            ->get();

        $recentAttendance = $attendanceRecords
            ->take(5)
            ->map(function (Attendance $attendance): array {
                return [
                    'date' => $attendance->time_in?->format('M j, Y') ?? '-',
                    'time_in' => $attendance->time_in?->format('g:i A') ?? '-',
                    'teacher' => $attendance->teacher?->full_name ?? 'Unknown Teacher',
                    'qr_status' => ucfirst($attendance->qr_status ?? 'unknown'),
                    'face_status' => ucfirst($attendance->face_status ?? 'unknown'),
                    'attendance_status' => ucfirst($attendance->attendance_status ?? 'unknown'),
                ];
            })
            ->values()
            ->all();

        $totalAttendance = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('attendance_status', 'present')->count();
        $lateCount = $attendanceRecords->whereIn('attendance_status', ['late', 'late_arrival'])->count();
        $attendancePercentage = $totalAttendance > 0
            ? round(($presentCount / $totalAttendance) * 100, 1)
            : 0;

        $qrToken = $student->qrToken;
        $faceProfile = $student->faceProfile;

        $notifications = collect($recentAttendance)
            ->map(function (array $attendance): array {
                $status = strtolower($attendance['attendance_status'] ?? 'unknown');

                return [
                    'icon' => $status === 'present' ? 'check-circle' : 'x-circle',
                    'color' => $status === 'present' ? 'text-emerald-500' : 'text-red-500',
                    'text' => sprintf(
                        'Attendance %s (QR: %s, Face: %s)',
                        $status === 'present' ? 'recorded' : 'rejected',
                        $attendance['qr_status'],
                        $attendance['face_status']
                    ),
                    'time' => $attendance['date'] ?? 'Just now',
                ];
            })
            ->values()
            ->all();

        return view('student.dashboard', [
            'student' => [
                'name' => $student->full_name,
                'student_number' => $student->student_number,
                'course' => $student->course,
                'year_level' => $student->year_level,
                'section' => $student->section,
                'photo' => $student->profile_photo,
            ],
            'attendanceStats' => [
                'total_attendance' => $totalAttendance,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'attendance_percentage' => $attendancePercentage,
            ],
            'recentAttendance' => $recentAttendance,
            'qrToken' => [
                'token' => $qrToken?->token,
                'status' => $qrToken?->status ? ucfirst($qrToken->status) : null,
                'expires_at' => $qrToken?->expires_at?->toDateTimeString() ?? null,
            ],
            'faceProfile' => [
                'status' => $faceProfile?->status ? ucfirst($faceProfile->status) : 'Not Enrolled',
                'last_updated' => $faceProfile?->updated_at?->format('M j, Y g:i A') ?? null,
            ],
            'notifications' => $notifications,
        ]);
    }
}
