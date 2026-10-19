<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Teacher;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'teacher', 'attendanceSession.teacher'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($where) use ($search) {
                    $where->whereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('student_number', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    })
                        ->orWhereHas('teacher', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('employee_id', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        })
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('teacher_id'), fn($query) => $query->where('teacher_id', (int) $request->teacher_id))
            ->when($request->filled('attendance_status'), fn($query) => $query->where('attendance_status', strtolower($request->attendance_status)))
            ->when($request->filled('qr_status'), fn($query) => $query->where('qr_status', strtolower($request->qr_status)))
            ->when($request->filled('face_status'), fn($query) => $query->where('face_status', strtolower($request->face_status)))
            ->when($request->filled('date'), fn($query) => $query->whereDate('time_in', $request->date));

        $records = $query->latest('time_in')
            ->paginate(15)
            ->appends($request->query());

        $records->getCollection()->transform(function (Attendance $attendance) {
            return [
                'id' => $attendance->id,
                'student' => $attendance->student?->full_name ?? 'Unknown Student',
                'student_number' => $attendance->student?->student_number ?? 'N/A',
                'teacher' => $attendance->teacher?->full_name ?? 'Unknown Teacher',
                'date' => $attendance->time_in?->format('M d, Y') ?? 'N/A',
                'time' => $attendance->time_in?->format('g:i A') ?? 'N/A',
                'qr_status' => ucfirst($attendance->qr_status ?? 'unknown'),
                'face_status' => ucfirst($attendance->face_status ?? 'unknown'),
                'status' => ucfirst($attendance->attendance_status ?? 'unknown'),
                'remarks' => $attendance->remarks ?: '—',
            ];
        });

        $teachers = Teacher::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn(Teacher $teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
            ])
            ->values()
            ->all();

        $stats = $this->buildStats($request);

        return view('admin.attendance.index', [
            'records' => $records,
            'stats' => $stats,
            'teachers' => $teachers,
            'attendanceStatuses' => ['Present', 'Absent', 'Rejected'],
            'qrStatuses' => ['Verified', 'Invalid', 'Expired', 'Bypassed'],
            'faceStatuses' => ['Matched', 'Mismatch', 'Not Verified', 'Manual'],
        ]);
    }

    public function show(Attendance $attendance)
    {
        return view('admin.attendance.show', compact('attendance'));
    }

    public function export(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $filename = 'attendance-export-' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ID', 'Student', 'Student No.', 'Teacher', 'Attendance Date', 'Time In', 'QR Status', 'Face Status', 'Attendance Status', 'Remarks']);

            foreach ($query->with(['student', 'teacher'])->latest('time_in')->cursor() as $attendance) {
                fputcsv($handle, [
                    $attendance->id,
                    $attendance->student?->full_name ?? 'Unknown Student',
                    $attendance->student?->student_number ?? 'N/A',
                    $attendance->teacher?->full_name ?? 'Unknown Teacher',
                    $attendance->time_in?->format('Y-m-d') ?? 'N/A',
                    $attendance->time_in?->format('H:i:s') ?? 'N/A',
                    ucfirst($attendance->qr_status ?? 'unknown'),
                    ucfirst($attendance->face_status ?? 'unknown'),
                    ucfirst($attendance->attendance_status ?? 'unknown'),
                    $attendance->remarks ?: '—',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildFilteredQuery(Request $request)
    {
        return Attendance::with(['student', 'teacher'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($where) use ($search) {
                    $where->whereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('student_number', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    })
                        ->orWhereHas('teacher', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('employee_id', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        })
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('teacher_id'), fn($query) => $query->where('teacher_id', (int) $request->teacher_id))
            ->when($request->filled('attendance_status'), fn($query) => $query->where('attendance_status', strtolower($request->attendance_status)))
            ->when($request->filled('qr_status'), fn($query) => $query->where('qr_status', strtolower($request->qr_status)))
            ->when($request->filled('face_status'), fn($query) => $query->where('face_status', strtolower($request->face_status)))
            ->when($request->filled('date'), fn($query) => $query->whereDate('time_in', $request->date));
    }

    private function buildStats(Request $request): array
    {
        $query = $this->buildFilteredQuery($request);

        $total = (clone $query)->count();
        $present = (clone $query)->where('attendance_status', 'present')->count();
        $rejected = (clone $query)->where('attendance_status', 'rejected')->count();
        $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return [
            [
                'icon' => 'calendar-days',
                'tone' => 'emerald',
                'label' => 'Total Records',
                'value' => number_format($total),
                'trend' => 'up',
                'trendValue' => 'Live data',
            ],
            [
                'icon' => 'check-circle',
                'tone' => 'blue',
                'label' => 'Present',
                'value' => number_format($present),
                'trend' => 'up',
                'trendValue' => $total > 0 ? $rate . '% rate' : '0% rate',
            ],
            [
                'icon' => 'x-circle',
                'tone' => 'amber',
                'label' => 'Rejected',
                'value' => number_format($rejected),
                'trend' => 'down',
                'trendValue' => 'Needs review',
            ],
            [
                'icon' => 'chart-bar',
                'tone' => 'indigo',
                'label' => 'Attendance Rate',
                'value' => $rate . '%',
                'trend' => $rate >= 80 ? 'up' : 'down',
                'trendValue' => $rate >= 80 ? 'Strong' : 'Monitor',
            ],
        ];
    }
}
