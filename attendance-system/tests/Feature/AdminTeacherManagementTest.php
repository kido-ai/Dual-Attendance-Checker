<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_active_and_archived_teacher_records(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'role' => 'admin',
        ]);

        $teacher = Teacher::create([
            'user_id' => $admin->id,
            'employee_id' => 'EMP-2026-001',
            'first_name' => 'Maria',
            'middle_name' => 'Dela Cruz',
            'last_name' => 'Santos',
            'email' => 'maria.santos@example.com',
            'phone' => '+63 912 345 6789',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.teachers.index'));
        $response->assertOk();
        $response->assertSee('Maria');
        $response->assertSee('EMP-2026-001');

        $teacher->update(['status' => 'archived']);

        $archiveResponse = $this->actingAs($admin)->get(route('admin.teachers.archived'));
        $archiveResponse->assertOk();
        $archiveResponse->assertSee('Maria');
        $archiveResponse->assertSee('EMP-2026-001');

        $detailResponse = $this->actingAs($admin)->get(route('admin.teachers.show', $teacher));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Maria Santos');
    }
}
