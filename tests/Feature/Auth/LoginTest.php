<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_accessible_by_guest(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_guest_redirected_from_dashboard_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_redirected_from_login_page(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect();
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        User::factory()->sekretariat()->create([
            'email'    => 'sekretariat@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'sekretariat@test.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->sekretariat()->create([
            'email'    => 'user@test.com',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'user@test.com',
            'password' => 'wrong_password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email'    => 'nonexistent@test.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_login_requires_email_field(): void
    {
        $response = $this->post('/login', [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_requires_password_field(): void
    {
        $response = $this->post('/login', [
            'email' => 'user@test.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->post('/login', [
            'email'    => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->sekretariat()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_authenticated_routes(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/penugasan/aktif',
            '/penugasan/selesai',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }
}
