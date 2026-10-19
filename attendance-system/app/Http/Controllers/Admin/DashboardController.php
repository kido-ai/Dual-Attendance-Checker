<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'admin' => Auth::user(),
            'stats' => $this->buildStats(),
            'attendanceRows' => $this->buildRecentAttendanceRows(),
            'todaysSessions' => $this->buildTodaysSessions(),
            'activities' => $this->buildRecentActivities(),
            'quickActions' => $this->buildQuickActions(),
        ]);
    }

    private function buildStats(): array
    {
        $today = Carbon::today();
        $lastMonth = Carbon::now()->subMonth();

        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $attendanceToday = Attendance::whereDate('time_in', $today)->count();
        $presentToday = Attendance::whereDate('time_in', $today)
            ->where('attendance_status', 'present')
            ->count();

        $attendanceRate = $attendanceToday > 0
            ? round(($presentToday / $attendanceToday) * 100, 1)
            : 0.0;

        $newStudentsThisMonth = Student::where('created_at', '>=', $lastMonth)->count();
        $openSessions = AttendanceSession::open()->count();

        return [
            [
                'icon' => 'academic-cap',
                'tone' => 'emerald',
                'label' => 'Total Students',
                'value' => number_format($totalStudents),
                'trend' => $newStudentsThisMonth > 0 ? 'up' : 'down',
                'trendValue' => "+{$newStudentsThisMonth} this month",
            ],
            [
                'icon' => 'users',
                'tone' => 'blue',
                'label' => 'Total Teachers',
                'value' => number_format($totalTeachers),
                'trend' => 'up',
                'trendValue' => 'Active faculty',
            ],
            [
                'icon' => 'calendar-days',
                'tone' => 'indigo',
                'label' => 'Attendance Today',
                'value' => number_format($attendanceToday),
                'trend' => 'up',
                'trendValue' => "{$presentToday} present",
            ],
            [
                'icon' => 'bolt',
                'tone' => 'amber',
                'label' => 'Open Sessions',
                'value' => number_format($openSessions),
                'trend' => 'up',
                'trendValue' => 'Right now',
            ],
        ];
    }

    /**
     * Real Attendance columns only — no 'class'/session title, since
     * Attendance has no relationship to AttendanceSession to pull one from.
     */
    private function buildRecentAttendanceRows(): array
    {
        return Attendance::with(['student', 'teacher'])
            ->latest('time_in')
            ->limit(5)
            ->get()
            ->map(fn(Attendance $attendance) => [
                'student' => $attendance->student?->full_name ?? 'Unknown Student',
                'teacher' => $attendance->teacher?->full_name ?? 'Unknown Teacher',
                'time' => $attendance->time_in?->format('g:i A') ?? '-',
                'status' => ucfirst($attendance->attendance_status ?? 'unknown'),
                'qr_status' => ucfirst($attendance->qr_status ?? 'unknown'),
                'face_status' => ucfirst($attendance->face_status ?? 'unknown'),
            ])
            ->toArray();
    }

    /**
     * Today's attendance sessions across all teachers — queried directly
     * from AttendanceSession (real table, real columns), not through
     * Attendance.
     */
    private function buildTodaysSessions(): array
    {
        return AttendanceSession::with('teacher')
            ->today()
            ->orderBy('started_at')
            ->get()
            ->map(fn(AttendanceSession $session) => [
                'title' => $session->title,
                'teacher' => $session->teacher?->full_name ?? 'Unknown Teacher',
                'time' => $session->started_at?->format('g:i A') ?? 'TBD',
                'status' => ucfirst($session->status),
            ])
            ->toArray();
    }

    private function buildRecentActivities(): array
    {
        return Attendance::with('student')
            ->latest('time_in')
            ->limit(5)
            ->get()
            ->map(function (Attendance $attendance) {
                $status = $attendance->attendance_status;

                return [
                    'icon' => $status === 'present' ? 'check-circle' : ($status === 'rejected' ? 'x-circle' : 'exclamation-triangle'),
                    'color' => $status === 'present' ? 'text-emerald-500' : ($status === 'rejected' ? 'text-red-500' : 'text-amber-500'),
                    'text' => sprintf(
                        '%s %s (QR: %s, Face: %s)',
                        $attendance->student?->full_name ?? 'A student',
                        $status === 'present' ? 'marked present' : ($status === 'rejected' ? 'was rejected' : 'recorded as absent'),
                        ucfirst($attendance->qr_status ?? 'unknown'),
                        ucfirst($attendance->face_status ?? 'unknown')
                    ),
                    'time' => $attendance->time_in?->diffForHumans() ?? 'Just now',
                ];
            })
            ->toArray();
    }

    private function buildQuickActions(): array
    {
        return [
            ['icon' => 'academic-cap', 'title' => 'Register Student', 'desc' => 'Add a new student profile and face ID', 'tone' => 'blue'],
            ['icon' => 'document-chart-bar', 'title' => 'Generate Report', 'desc' => 'Export attendance summaries as PDF or CSV', 'tone' => 'amber'],
        ];
    }
}
