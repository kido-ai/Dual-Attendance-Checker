<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $student = $this->currentStudent();

        $query = $this->buildFilteredQuery($student, $request);

        $records = $query->latest('time_in')
            ->paginate(10)
            ->appends($request->query());

        $total = (clone $query)->count();
        $present = (clone $query)->where('attendance_status', 'present')->count();
        $rejected = (clone $query)->whereIn('attendance_status', ['rejected', 'absent'])->count();
        $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;

        $lastRecord = (clone $query)->latest('time_in')->first();

        return view('student.attendance.index', [
            'stats' => [
                ['icon' => 'clipboard-check', 'tone' => 'emerald', 'label' => 'Total Attendance', 'value' => number_format($total), 'trend' => 'up', 'trendValue' => 'All time'],
                ['icon' => 'check-circle', 'tone' => 'blue', 'label' => 'Present Days', 'value' => number_format($present), 'trend' => 'up', 'trendValue' => 'All time'],
                ['icon' => 'x-circle', 'tone' => 'amber', 'label' => 'Rejected Days', 'value' => number_format($rejected), 'trend' => 'down', 'trendValue' => 'All time'],
                ['icon' => 'chart-bar', 'tone' => 'indigo', 'label' => 'Attendance Rate', 'value' => "{$rate}%", 'trend' => $rate >= 90 ? 'up' : 'down', 'trendValue' => 'Overall'],
            ],
            'records' => $records->through(function (Attendance $attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->time_in?->format('M j, Y') ?? '-',
                    'time' => $attendance->time_in?->format('g:i A') ?? '-',
                    'teacher' => $attendance->teacher?->full_name ?? 'Unknown Teacher',
                    'subject' => $attendance->attendanceSession?->title ?? 'Attendance Session',
                    'qr_status' => ucfirst($attendance->qr_status ?? 'unknown'),
                    'face_status' => ucfirst($attendance->face_status ?? 'unknown'),
                    'status' => ucfirst($attendance->attendance_status ?? 'unknown'),
                    'remarks' => $attendance->remarks ?: '-',
                ];
            }),
            'attendanceStatuses' => ['Present', 'Absent', 'Rejected'],
            'qrStatuses' => ['Verified', 'Invalid', 'Expired'],
            'faceStatuses' => ['Matched', 'Mismatch', 'Not Verified'],
            'months' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            'summary' => [
                'attendance_rate' => "{$rate}%",
                'last_date' => $lastRecord?->time_in?->format('M j, Y') ?? 'No records yet',
                'last_verification' => $lastRecord
                    ? sprintf('QR %s + Face %s at %s', ucfirst($lastRecord->qr_status ?? 'unknown'), ucfirst($lastRecord->face_status ?? 'unknown'), $lastRecord->time_in?->format('g:i A') ?? '-')
                    : 'No verifications recorded yet',
            ],
        ]);
    }

    public function exportPdf(Request $request)
    {
        $student = $this->currentStudent();
        $records = $this->buildFilteredQuery($student, $request)
            ->with(['teacher', 'attendanceSession'])
            ->latest('time_in')
            ->get();

        $pdf = Pdf::loadView('student.attendance.export-pdf', [
            'student' => $student,
            'records' => $records,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'filters' => $this->filterSummary($request),
        ]);

        return $pdf->setPaper('A4', 'landscape')->download('attendance-history-' . $student->student_number . '-' . now()->format('YmdHis') . '.pdf');
    }

    /**
     * GET /student/attendance/{attendance} — route name: student.attendance.show
     */
    public function show(Attendance $attendance): View
    {
        $student = $this->currentStudent();

        // Route-model binding resolves ANY attendance ID — this ownership
        // check is the actual authorization boundary, matching "students
        // can only view their own records."
        abort_unless($attendance->student_id === $student->id, 403);

        $attendance->load('teacher');

        return view('student.attendance.show', [
            'record' => [
                'date' => $attendance->time_in?->format('F j, Y') ?? '-',
                'time' => $attendance->time_in?->format('g:i A') ?? '-',
                'teacher' => $attendance->teacher?->full_name ?? 'Unknown Teacher',
                'subject' => '-',
                'section' => $student->section,
                'qr_status' => ucfirst($attendance->qr_status ?? 'unknown'),
                'face_status' => ucfirst($attendance->face_status ?? 'unknown'),
                'status' => ucfirst($attendance->attendance_status ?? 'unknown'),
                'remarks' => $attendance->remarks ?: '-',
            ],
        ]);
    }

    private function currentStudent(): Student
    {
        $student = Auth::user()->student;

        abort_if($student === null, 403, 'No student profile is linked to this account yet. Contact an administrator.');

        return $student;
    }

    private function buildFilteredQuery(Student $student, Request $request)
    {
        return $student->attendances()
            ->with(['teacher', 'attendanceSession'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($where) use ($search) {
                    $where->whereHas('teacher', function ($teacherQuery) use ($search) {
                        $teacherQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    })
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('attendanceSession', function ($sessionQuery) use ($search) {
                            $sessionQuery->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('attendance_status'), fn($query) => $query->where('attendance_status', strtolower($request->attendance_status)))
            ->when($request->filled('qr_status'), fn($query) => $query->where('qr_status', strtolower($request->qr_status)))
            ->when($request->filled('face_status'), fn($query) => $query->where('face_status', strtolower($request->face_status)))
            ->when($request->filled('month'), fn($query) => $query->whereMonth('time_in', date('m', strtotime($request->month))))
            ->when($request->filled('date'), fn($query) => $query->whereDate('time_in', $request->date));
    }

    private function filterSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('attendance_status')) {
            $summary[] = 'Status: ' . ucfirst($request->attendance_status);
        }

        if ($request->filled('qr_status')) {
            $summary[] = 'QR: ' . ucfirst($request->qr_status);
        }

        if ($request->filled('face_status')) {
            $summary[] = 'Face: ' . ucfirst($request->face_status);
        }

        if ($request->filled('date')) {
            $summary[] = 'Date: ' . $request->date;
        }

        if ($request->filled('month')) {
            $summary[] = 'Month: ' . $request->month;
        }

        return $summary ?: ['All records'];
    }
}
