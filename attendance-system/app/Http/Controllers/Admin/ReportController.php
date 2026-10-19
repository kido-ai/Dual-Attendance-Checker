<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function attendance()
    {
        $attendances = Attendance::with(['student', 'teacher'])
            ->latest('time_in')
            ->paginate(15);

        return view('admin.reports.attendance', compact('attendances'));
    }

    public function student()
    {
        $students = Student::latest()->paginate(15);

        return view('admin.reports.students', compact('students'));
    }

    public function teacher()
    {
        $teachers = Teacher::latest()->paginate(15);

        return view('admin.reports.teachers', compact('teachers'));
    }

    public function exportPdf(Request $request)
    {
        $records = $this->buildFilteredQuery($request)
            ->with(['student', 'teacher'])
            ->latest('time_in')
            ->get();

        $html = view('admin.reports.export-pdf', [
            'records' => $records,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'filters' => $this->filterSummary($request),
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('A4', 'landscape')
            ->download('attendance-report-' . now()->format('YmdHis') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $records = $this->buildFilteredQuery($request)
            ->with(['student', 'teacher'])
            ->latest('time_in')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Report');

        $headers = ['ID', 'Student', 'Student No.', 'Teacher', 'Date', 'Time In', 'QR Status', 'Face Status', 'Attendance Status', 'Remarks'];
        $sheet->fromArray([$headers], NULL, 'A1');

        $row = 2;
        foreach ($records as $record) {
            $sheet->fromArray([
                $record->id,
                $record->student?->full_name ?? 'Unknown Student',
                $record->student?->student_number ?? 'N/A',
                $record->teacher?->full_name ?? 'Unknown Teacher',
                $record->time_in?->format('Y-m-d') ?? 'N/A',
                $record->time_in?->format('H:i:s') ?? 'N/A',
                ucfirst($record->qr_status ?? 'unknown'),
                ucfirst($record->face_status ?? 'unknown'),
                ucfirst($record->attendance_status ?? 'unknown'),
                $record->remarks ?: '—',
            ], NULL, 'A' . $row);

            $row++;
        }

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(32);

        $writer = new Xlsx($spreadsheet);
        $filename = 'attendance-report-' . now()->format('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function buildFilteredQuery(Request $request)
    {
        return Attendance::query()
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

    private function filterSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('teacher_id')) {
            $teacher = Teacher::find($request->teacher_id);
            $summary[] = 'Teacher: ' . ($teacher?->full_name ?? 'Selected teacher');
        }

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

        return $summary ?: ['All records'];
    }
}
