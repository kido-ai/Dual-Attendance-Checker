<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_displays_the_authenticated_admin_full_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Santos',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Maria Santos');
    }
}
