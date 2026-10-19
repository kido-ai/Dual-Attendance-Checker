<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * FIXED: previously imported App\Models\AttendanceSession and called
 * buildSessions(), which queried AttendanceSession::where('teacher_id', ...)
 * — a model/table that doesn't exist in this codebase. That call would
 * throw "Class AttendanceSession not found" on every load of this
 * dashboard. buildSessions() and the `mySessions` view variable are
 * removed; buildNotifications() no longer references
 * $record->attendanceSession->title either.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var Teacher $teacher */
        $teacher = $this->currentTeacher();

        $todaysAttendance = Attendance::with('student')
            ->where('teacher_id', $teacher->id)
            ->whereDate('time_in', today())
            ->latest('time_in')
            ->get();

        return view('teacher.dashboard', [
            'teacher' => $teacher,
            'stats' => $this->buildStats($teacher),
            'recentAttendance' => $this->recentAttendance($teacher),
            'attendanceSummary' => $this->buildAttendanceSummary($todaysAttendance),
            'notifications' => $this->buildNotifications($todaysAttendance),
            'studentList' => $this->buildStudentList($todaysAttendance),
            'todaysSessions' => [],
        ]);
    }

    private function currentTeacher(): Teacher
    {
        $teacher = Auth::user()->teacher;

        abort_if($teacher === null, 403, 'No teacher profile is linked to this account yet. Contact an administrator.');

        return $teacher;
    }

    /**
     * @return array<int, array{icon:string,tone:string,label:string,value:string,trend:string,trendValue:string}>
     */
    private function buildStats(Teacher $teacher): array
    {
        $today = Carbon::today();

        $todaysAttendance = Attendance::where('teacher_id', $teacher->id)
            ->whereDate('time_in', $today)
            ->get();

        $studentsToday = $todaysAttendance->pluck('student_id')->unique()->count();
        $presentToday = $todaysAttendance->where('attendance_status', 'present')->count();
        $submittedToday = $todaysAttendance->count();
        $rate = $submittedToday > 0 ? round(($presentToday / $submittedToday) * 100, 1) : 0.0;

        return [
            [
                'icon' => 'users',
                'tone' => 'blue',
                'label' => 'Students Today',
                'value' => number_format($studentsToday),
                'trend' => 'up',
                'trendValue' => 'Unique check-ins',
            ],
            [
                'icon' => 'clipboard-check',
                'tone' => 'indigo',
                'label' => 'Attendance Submitted',
                'value' => number_format($submittedToday),
                'trend' => 'up',
                'trendValue' => "{$presentToday} present",
            ],
            [
                'icon' => 'chart-bar',
                'tone' => 'amber',
                'label' => 'Attendance Rate',
                'value' => "{$rate}%",
                'trend' => $rate >= 90 ? 'up' : 'down',
                'trendValue' => 'Today',
            ],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Attendance>
     */
    private function recentAttendance(Teacher $teacher)
    {
        return Attendance::with('student')
            ->where('teacher_id', $teacher->id)
            ->latest('time_in')
            ->limit(5)
            ->get();
    }


    private function buildAttendanceSummary($attendance): array
    {
        return [
            'present' => $attendance->where('attendance_status', 'present')->count(),
            'rejected' => $attendance->where('attendance_status', 'rejected')->count(),
        ];
    }

    private function buildNotifications($attendance): array
    {
        return $attendance->take(4)
            ->map(function (Attendance $record) {
                $status = $record->attendance_status;

                return [
                    'icon' => $status === 'present' ? 'check-circle' : 'x-circle',
                    'color' => $status === 'present' ? 'text-emerald-500' : 'text-red-500',
                    'text' => sprintf(
                        '%s %s (QR: %s, Face: %s)',
                        $record->student?->full_name ?? 'A student',
                        $status === 'present' ? 'marked present' : 'was rejected',
                        ucfirst($record->qr_status ?? 'unknown'),
                        ucfirst($record->face_status ?? 'unknown')
                    ),
                    'time' => $record->time_in?->diffForHumans() ?? 'Just now',
                ];
            })
            ->toArray();
    }

    private function buildStudentList($attendance): array
    {
        return $attendance->groupBy('student_id')
            ->map(function ($records) {
                $student = $records->first()->student;
                $presentCount = $records->where('attendance_status', 'present')->count();
                $rate = $records->count() > 0 ? round(($presentCount / $records->count()) * 100) : 0;

                return [
                    'name' => $student?->full_name ?? 'Unknown Student',
                    'number' => $student?->student_number ?? 'Unknown',
                    'course' => $student?->course ?? 'N/A',
                    'section' => $student?->section ?? 'N/A',
                    'rate' => $rate,
                ];
            })
            ->values()
            ->toArray();
    }
}
