<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_role_user_redirects_directly_to_dashboard_after_login(): void
    {
        User::factory()->sekretariat()->create([
            'email'    => 'single@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'single@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    }

    public function test_guest_cannot_access_role_selection_page(): void
    {
        $response = $this->get('/select-role');
        $response->assertRedirect('/login');
    }

    public function test_single_role_user_redirected_from_role_selection(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->get('/select-role');
        $response->assertRedirect('/dashboard');
    }

    public function test_change_password_first_page_accessible_when_authenticated(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->get('/change-password-first');
        $response->assertStatus(200);
    }

    public function test_change_password_first_page_not_accessible_by_guest(): void
    {
        $response = $this->get('/change-password-first');
        $response->assertRedirect('/login');
    }

    public function test_user_can_change_password_successfully(): void
    {
        $user = User::factory()->sekretariat()->create([
            'password'             => bcrypt('old_password_123'),
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->post('/change-password-first', [
            'current_password'          => 'old_password_123',
            'new_password'              => 'new_password_456',
            'new_password_confirmation' => 'new_password_456',
        ]);

        $response->assertRedirect();
        $this->assertFalse((bool) $user->fresh()->must_change_password);
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->sekretariat()->create([
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->actingAs($user)->post('/change-password-first', [
            'current_password'          => 'wrong_password',
            'new_password'              => 'new_password_456',
            'new_password_confirmation' => 'new_password_456',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_change_password_requires_new_password_confirmation(): void
    {
        $user = User::factory()->sekretariat()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->post('/change-password-first', [
            'current_password'          => 'old_password',
            'new_password'              => 'new_password_456',
            'new_password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors('new_password');
    }

    public function test_change_password_requires_minimum_8_chars(): void
    {
        $user = User::factory()->sekretariat()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->post('/change-password-first', [
            'current_password'          => 'old_password',
            'new_password'              => 'short',
            'new_password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('new_password');
    }

    public function test_skip_change_password_clears_flag(): void
    {
        $user = User::factory()->sekretariat()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)->post('/change-password-skip');

        $this->assertFalse((bool) $user->fresh()->must_change_password);
    }
}
