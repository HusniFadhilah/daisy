<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_sekretariat_can_access_dashboard(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_asesor_can_access_dashboard(): void
    {
        $user = User::factory()->asesor()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_validator_can_access_dashboard(): void
    {
        $user = User::factory()->validator()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_prodi_can_access_dashboard(): void
    {
        $user = User::factory()->adminProdi()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_keuangan_can_access_dashboard(): void
    {
        $user = User::factory()->keuangan()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_unverified_user_can_access_dashboard(): void
    {
        // User model does not implement MustVerifyEmail,
        // so the 'verified' middleware is a no-op for this app.
        $user = User::factory()->sekretariat()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_kontak_sekretariat_accessible_by_authenticated_user(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->get('/kontak-sekretariat');
        $response->assertStatus(200);
    }

    public function test_kontak_sekretariat_not_accessible_by_guest(): void
    {
        $response = $this->get('/kontak-sekretariat');
        $response->assertRedirect('/login');
    }
}
