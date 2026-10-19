<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attendance_index_displays_real_database_records(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'role' => 'admin',
        ]);

        $teacherUser = User::factory()->create([
            'name' => 'Teacher One',
            'role' => 'teacher',
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Student One',
            'role' => 'student',
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

        $session = AttendanceSession::create([
            'teacher_id' => $teacher->id,
            'title' => 'Database Systems',
            'attendance_date' => now()->toDateString(),
            'started_at' => now()->subMinutes(30),
            'status' => 'closed',
        ]);

        Attendance::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'time_in' => now()->subMinutes(15),
            'qr_status' => 'verified',
            'face_status' => 'matched',
            'attendance_status' => 'present',
            'remarks' => 'On time',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance');

        $response->assertOk();
        $response->assertSee('Ana Reyes');
        $response->assertSee('Teacher One');
        $response->assertSee('Present');
    }
}
