<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\QRToken;
use App\Services\QRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'qrToken'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($where) use ($search) {
                    $where->where('student_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->when($request->filled('course'),     fn($q) => $q->where('course', $request->course))
            ->when($request->filled('year_level'), fn($q) => $q->where('year_level', $request->year_level))
            ->when($request->filled('section'),    fn($q) => $q->where('section', $request->section))
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $this->normalizeStatus($request->status));
            })
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        $students->getCollection()->transform(function ($student) {
            return [
                'id'             => $student->id,
                'student_number' => $student->student_number,
                'full_name'      => $student->first_name . ' '
                    . ($student->middle_name ? $student->middle_name . ' ' : '')
                    . $student->last_name,
                'course'         => $student->course,
                'year'           => $student->year_level,
                'section'        => $student->section,
                'email'          => $student->user->email ?? 'N/A',
                'status'         => ucfirst($student->status ?? 'active'),
                'profile_photo'  => $student->profile_photo ? Storage::url($student->profile_photo) : null,
                'created_at'     => $student->created_at?->format('M d, Y'),
                'qr_token'       => $student->qrToken,
            ];
        });

        $stats      = $this->getStatistics();
        $courses    = Student::whereNotNull('course')->distinct()->pluck('course')->toArray();
        $yearLevels = Student::whereNotNull('year_level')->distinct()->pluck('year_level')->toArray();
        $sections   = Student::whereNotNull('section')->distinct()->pluck('section')->toArray();
        $statuses   = ['Active', 'Inactive'];

        return view(
            'admin.students.index',
            compact('students', 'stats', 'courses', 'yearLevels', 'sections', 'statuses')
        );
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'student_number' => 'required|string|max:50|unique:students,student_number',
            'course' => 'nullable|string|max:100',
            'year' => 'nullable|string|max:20',
            'year_level' => 'nullable|string|max:20',
            'section' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female,Prefer not to say',
            'birth_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,Active,Inactive',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $yearLevel = $validated['year_level'] ?? $validated['year'] ?? null;
        $status = $this->normalizeStatus($validated['status'] ?? 'active');

        $user = \App\Models\User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'student',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => $validated['student_number'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'course' => $validated['course'] ?? null,
            'year_level' => $yearLevel,
            'section' => $validated['section'] ?? null,
            'status' => in_array($status, ['active', 'inactive', 'archived', 'graduated'], true) ? $status : 'active',
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('students/photos', 'public');
            $student->update(['profile_photo' => $photoPath]);
        }

        $qrService = new QRService();
        $qrService->generateForStudent($student);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created. QR code generated.');
    }

    public function show(Student $student)
    {
        // Eager-load everything the view touches so we don't N+1.
        $student->load(['user', 'qrToken', 'faceProfile', 'attendances.session']);

        $quickStats = $this->getQuickStats($student);
        $activities = $this->getStudentActivities($student);
        $qrImageSrc = $this->qrImageSource($student);

        return view('admin.students.show', compact('student', 'quickStats', 'activities', 'qrImageSrc'));
    }
    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255|unique:users,email,' . ($student->user_id ?? 'NULL'),
            'student_number' => 'required|string|max:50|unique:students,student_number,' . $student->id,
            'course'         => 'nullable|string|max:100',
            'year'           => 'nullable|string|max:20',
            'year_level'     => 'nullable|string|max:20',
            'section'        => 'nullable|string|max:50',
            'gender'         => 'nullable|in:Male,Female,Prefer not to say',
            'birth_date'     => 'nullable|date',
            'status'         => 'nullable|in:active,inactive,Active,Inactive',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $yearLevel = $validated['year_level'] ?? $validated['year'] ?? null;
        $status    = $this->normalizeStatus($validated['status'] ?? $student->status ?? 'active');

        // Build a single update payload.
        $payload = [
            'student_number' => $validated['student_number'],
            'first_name'     => $validated['first_name'],
            'middle_name'    => $validated['middle_name'] ?? null,
            'last_name'      => $validated['last_name'],
            'gender'         => $validated['gender'] ?? null,
            'birth_date'     => $validated['birth_date'] ?? null,
            'course'         => $validated['course'] ?? null,
            'year_level'     => $yearLevel,
            'section'        => $validated['section'] ?? null,
            'status'         => in_array($status, ['active', 'inactive', 'archived', 'graduated'], true) ? $status : 'active',
        ];

        if ($request->hasFile('photo')) {
            if ($student->profile_photo) {
                Storage::disk('public')->delete($student->profile_photo);
            }
            $payload['profile_photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update($payload);

        if ($student->user && $student->user->email !== $validated['email']) {
            $student->user->update(['email' => $validated['email']]);
        }

        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', 'Student updated.');
    }

    public function destroy(Student $student)
    {
        $qrToken = $student->qrToken;
        if ($qrToken) {
            $qrService = new QRService();
            $qrService->deleteQRImage($qrToken->token);
            $qrToken->delete();
        }

        $user = $student->user;
        $student->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted.');
    }

    public function regenerateQr(Student $student)
    {
        return $this->generateQR($student);
    }

    public function generateQR(Student $student)
    {
        $qrService = new QRService();

        if ($student->qrToken) {
            $qrService->regenerateForStudent($student);
            $message = 'QR regenerated.';
        } else {
            $qrService->generateForStudent($student);
            $message = 'QR generated.';
        }

        return redirect()->route('admin.students.show', $student)
            ->with('success', $message);
    }

    // ---- Helpers ----
    protected function normalizeStatus(?string $status, string $fallback = 'active'): string
    {
        $normalized = strtolower((string) ($status ?? $fallback));

        if (in_array($normalized, ['active', 'inactive'], true)) {
            return $normalized;
        }

        return in_array($normalized, ['archived', 'graduated'], true) ? 'inactive' : 'active';
    }

    protected function qrImageSource(Student $student): ?string
    {
        $qrToken = $student->qrToken;

        if (! $qrToken) {
            return null;
        }

        if (method_exists($qrToken, 'isExpired') && $qrToken->isExpired()) {
            return null;
        }

        $path = "qr-codes/{$qrToken->token}.png";

        // If the PNG is missing, we do NOT try to regenerate it here — the QRService
        // may not actually be writing files. The Blade view will fall back to a
        // client-side renderer using $qrToken->token as the data source.
        if (! Storage::disk('public')->exists($path)) {
            \Log::info('QR PNG missing, will render client-side', [
                'student_id' => $student->id,
                'token'      => $qrToken->token,
                'path'       => $path,
            ]);
            return null;
        }

        return 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($path));
    }
    protected function getStatistics(): array
    {
        $total = Student::count();
        $active = Student::where('status', 'active')->count();
        $inactive = Student::where('status', 'inactive')->count();
        $newThisMonth = Student::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            ['icon' => 'academic-cap', 'tone' => 'emerald', 'label' => 'Total Students', 'value' => number_format($total), 'trend' => 'up', 'trendValue' => '+3.1%'],
            ['icon' => 'check-circle', 'tone' => 'blue', 'label' => 'Active Students', 'value' => number_format($active), 'trend' => 'up', 'trendValue' => '+2.4%'],
            ['icon' => 'no-symbol', 'tone' => 'amber', 'label' => 'Inactive Students', 'value' => number_format($inactive), 'trend' => 'down', 'trendValue' => '-1.2%'],
            ['icon' => 'user-plus', 'tone' => 'indigo', 'label' => 'New This Month', 'value' => number_format($newThisMonth), 'trend' => 'up', 'trendValue' => '+12%'],
        ];
    }

    protected function getQuickStats(Student $student): array
    {
        $qrToken = $student->qrToken;
        $faceProfile = $student->faceProfile;

        return [
            ['icon' => 'chart-bar', 'tone' => 'emerald', 'label' => 'Attendance Rate', 'value' => $this->calculateAttendanceRate($student)],
            ['icon' => 'qr-code', 'tone' => 'blue', 'label' => 'QR Code', 'value' => $qrToken ? 'Active' : 'Not Generated'],
            ['icon' => 'video-camera', 'tone' => 'indigo', 'label' => 'Face Recognition', 'value' => $faceProfile?->status ? ucfirst($faceProfile->status) : 'Pending'],
        ];
    }

    protected function getStudentActivities(Student $student): array
    {
        $activities = [];

        $attendances = \App\Models\Attendance::with('attendanceSession')
            ->where('student_id', $student->id)
            ->latest('time_in')
            ->take(3)
            ->get();

        foreach ($attendances as $attendance) {
            $status = $attendance->attendance_status;
            $icon = $status === 'present' ? 'check-circle' : 'x-circle';
            $color = $status === 'present' ? 'text-emerald-500' : 'text-red-500';
            $sessionTitle = $attendance->attendanceSession?->title ?? 'Attendance';
            $activities[] = [
                'icon' => $icon,
                'color' => $color,
                'text' => "Checked in for {$sessionTitle} via QR + Face",
                'time' => $attendance->time_in?->format('M d, Y g:i A') ?? 'N/A',
            ];
        }

        $qrToken = $student->qrToken;
        if ($qrToken) {
            $activities[] = [
                'icon' => 'qr-code',
                'color' => 'text-emerald-500',
                'text' => 'QR code generated',
                'time' => $qrToken->created_at?->format('M d, Y') ?? 'N/A',
            ];
        }

        $faceProfile = $student->faceProfile;
        if ($faceProfile && $faceProfile->status === 'enrolled') {
            $activities[] = [
                'icon' => 'video-camera',
                'color' => 'text-blue-500',
                'text' => 'Face Recognition enrollment completed',
                'time' => $faceProfile->enrolled_at?->format('M d, Y') ?? 'N/A',
            ];
        }

        $activities[] = [
            'icon' => 'pencil',
            'color' => 'text-indigo-500',
            'text' => 'Profile updated by Administrator',
            'time' => $student->updated_at?->format('M d, Y') ?? 'N/A',
        ];

        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 5);
    }

    protected function calculateAttendanceRate(Student $student): string
    {
        $totalSessions = \App\Models\AttendanceSession::whereHas('attendances', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })->count();

        if ($totalSessions === 0) {
            return 'N/A';
        }

        $present = \App\Models\Attendance::where('student_id', $student->id)
            ->where('attendance_status', 'present')
            ->count();

        $rate = round(($present / $totalSessions) * 100, 1);
        return $rate . '%';
    }

    /**
     * Display archived students.
     */
    public function archived()
    {
        $archivedStudents = Student::with(['user', 'qrToken'])
            ->where('status', 'inactive')
            ->latest()
            ->paginate(15);

        $archivedStudents->getCollection()->transform(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'full_name' => $student->full_name,
                'course' => $student->course,
                'year' => $student->year_level,
                'section' => $student->section,
                'email' => $student->user->email ?? 'N/A',
                'status' => $student->status === 'inactive' ? 'Archived' : ucfirst($student->status ?? 'active'),
                'profile_photo' => $student->profile_photo ? Storage::url($student->profile_photo) : null,
                'created_at' => $student->created_at?->format('M d, Y'),
            ];
        });

        return view('admin.students.archived', compact('archivedStudents'));
    }

    /**
     * Archive a student (soft delete / mark as archived).
     */
    public function archive(Student $student)
    {
        $student->update(['status' => 'inactive']);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student archived successfully.');
    }

    /**
     * Restore an archived student.
     */
    public function restore(Student $student)
    {
        $student->update(['status' => 'active']);

        return redirect()->route('admin.students.archived')
            ->with('success', 'Student restored successfully.');
    }

    /**
     * Permanently delete a student.
     */
    public function forceDelete(Student $student)
    {
        $qrToken = $student->qrToken;
        if ($qrToken) {
            $qrService = new QRService();
            $qrService->deleteQRImage($qrToken->token);
            $qrToken->delete();
        }

        $user = $student->user;
        $student->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.students.archived')
            ->with('success', 'Student permanently deleted.');
    }
}
