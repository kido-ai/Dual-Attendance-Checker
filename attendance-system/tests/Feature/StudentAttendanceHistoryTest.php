<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_attendance_history_uses_real_data_and_supports_pdf_export(): void
    {
        $studentUser = User::factory()->create([
            'name' => 'Student One',
            'role' => 'student',
        ]);

        $teacherUser = User::factory()->create([
            'name' => 'Teacher One',
            'role' => 'teacher',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => '2026-0001',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'course' => 'BSIT',
            'year_level' => '3',
            'section' => 'A',
            'status' => 'active',
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_id' => 'EMP-101',
            'first_name' => 'Teacher',
            'last_name' => 'One',
            'email' => 'teacher.one@example.com',
            'phone' => '09170000000',
            'status' => 'active',
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'time_in' => now()->subDay(),
            'qr_status' => 'verified',
            'face_status' => 'matched',
            'attendance_status' => 'present',
            'remarks' => 'On time',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/attendance');

        $response->assertOk();
        $response->assertSee('Ana Reyes');
        $response->assertSee('Present');
        $response->assertSee('On time');

        $pdfResponse = $this->actingAs($studentUser)->get('/student/attendance/export-pdf?attendance_status=present');

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }
}
