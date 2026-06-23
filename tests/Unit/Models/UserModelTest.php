<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    private function makeUser(array $attrs = []): User
    {
        return new User(array_merge([
            'name'             => 'Test User',
            'email'            => 'test@example.com',
            'role_selected'    => 'sekretariat',
            'roles'            => ['sekretariat'],
            'is_multiple_role' => false,
        ], $attrs));
    }

    public function test_hasRole_true_for_current_role_selected(): void
    {
        $user = $this->makeUser(['role_selected' => 'sekretariat']);
        $this->assertTrue($user->hasRole('sekretariat'));
    }

    public function test_hasRole_false_for_different_role(): void
    {
        $user = $this->makeUser(['role_selected' => 'sekretariat']);
        $this->assertFalse($user->hasRole('asesor'));
        $this->assertFalse($user->hasRole('admin_prodi'));
    }

    public function test_hasRole_is_case_insensitive(): void
    {
        $user = $this->makeUser(['role_selected' => 'Sekretariat']);
        $this->assertTrue($user->hasRole('sekretariat'));
        $this->assertTrue($user->hasRole('SEKRETARIAT'));
    }

    public function test_hasRole_accepts_array_returns_true_when_matched(): void
    {
        $user = $this->makeUser(['role_selected' => 'asesor']);
        $this->assertTrue($user->hasRole(['asesor', 'sekretariat']));
    }

    public function test_hasRole_accepts_array_returns_false_when_no_match(): void
    {
        $user = $this->makeUser(['role_selected' => 'asesor']);
        $this->assertFalse($user->hasRole(['sekretariat', 'admin_prodi']));
    }

    public function test_hasAnyRole_string_checks_roles_array(): void
    {
        $user = $this->makeUser(['roles' => ['asesor', 'validator']]);
        $this->assertTrue($user->hasAnyRole('asesor'));
        $this->assertTrue($user->hasAnyRole('validator'));
        $this->assertFalse($user->hasAnyRole('sekretariat'));
    }

    public function test_hasAnyRole_array_checks_intersection(): void
    {
        $user = $this->makeUser(['roles' => ['asesor', 'validator']]);
        $this->assertTrue($user->hasAnyRole(['validator', 'sekretariat']));
        $this->assertFalse($user->hasAnyRole(['sekretariat', 'admin_prodi']));
    }

    public function test_hasAnyRole_returns_false_when_roles_null(): void
    {
        $user = $this->makeUser(['roles' => null]);
        $this->assertFalse($user->hasAnyRole('asesor'));
    }

    public function test_roles_count_attribute_for_single_role(): void
    {
        $user = $this->makeUser(['roles' => ['asesor']]);
        $this->assertSame(1, $user->roles_count);
    }

    public function test_roles_count_attribute_for_multiple_roles(): void
    {
        $user = $this->makeUser(['roles' => ['asesor', 'validator', 'sekretariat']]);
        $this->assertSame(3, $user->roles_count);
    }

    public function test_roles_count_is_zero_when_null(): void
    {
        $user = $this->makeUser(['roles' => null]);
        $this->assertSame(0, $user->roles_count);
    }

    public function test_roles_count_is_zero_for_empty_array(): void
    {
        $user = $this->makeUser(['roles' => []]);
        $this->assertSame(0, $user->roles_count);
    }

    public function test_hasMultipleRoles_false_for_single_role(): void
    {
        $user = $this->makeUser(['roles' => ['asesor']]);
        $this->assertFalse($user->hasMultipleRoles());
    }

    public function test_hasMultipleRoles_true_for_two_roles(): void
    {
        $user = $this->makeUser(['roles' => ['asesor', 'validator']]);
        $this->assertTrue($user->hasMultipleRoles());
    }

    public function test_hasMultipleRoles_false_when_roles_null(): void
    {
        $user = $this->makeUser(['roles' => null]);
        $this->assertFalse($user->hasMultipleRoles());
    }

    public function test_hasMultipleRoles_false_for_empty_roles(): void
    {
        $user = $this->makeUser(['roles' => []]);
        $this->assertFalse($user->hasMultipleRoles());
    }
}
