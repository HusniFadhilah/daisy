<?php

namespace Tests\Feature\De;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->withRole($role)->create();
    }

    // =====================================================
    // Permohonan Akreditasi (DE)
    // =====================================================

    public function test_guest_cannot_access_de_permohonan_index(): void
    {
        $response = $this->get('/de/permohonan-akreditasi');
        $response->assertRedirect('/login');
    }

    public function test_sekretariat_can_access_de_permohonan_index(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/de/permohonan-akreditasi');
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_de_permohonan_index(): void
    {
        $user = $this->makeUser('super_admin');
        $response = $this->actingAs($user)->get('/de/permohonan-akreditasi');
        $response->assertStatus(200);
    }

    public function test_asesor_cannot_access_de_permohonan_index(): void
    {
        $user = $this->makeUser('asesor');
        $response = $this->actingAs($user)->get('/de/permohonan-akreditasi');
        $response->assertStatus(403);
    }

    public function test_admin_prodi_cannot_access_de_permohonan_index(): void
    {
        $user = $this->makeUser('admin_prodi');
        $response = $this->actingAs($user)->get('/de/permohonan-akreditasi');
        $response->assertStatus(403);
    }

    public function test_validator_cannot_access_de_permohonan_index(): void
    {
        $user = $this->makeUser('validator');
        $response = $this->actingAs($user)->get('/de/permohonan-akreditasi');
        $response->assertStatus(403);
    }

    public function test_keuangan_cannot_access_de_permohonan_index(): void
    {
        $user = $this->makeUser('keuangan_lamdepilar');
        $response = $this->actingAs($user)->get('/de/permohonan-akreditasi');
        $response->assertStatus(403);
    }

    // =====================================================
    // Penetapan Hasil Akreditasi (DE)
    // =====================================================

    public function test_guest_cannot_access_de_penetapan_hasil(): void
    {
        $response = $this->get('/de/penetapan-hasil-akreditasi');
        $response->assertRedirect('/login');
    }

    public function test_sekretariat_can_access_de_penetapan_hasil_index(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/de/penetapan-hasil-akreditasi');
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_de_penetapan_hasil_index(): void
    {
        $user = $this->makeUser('super_admin');
        $response = $this->actingAs($user)->get('/de/penetapan-hasil-akreditasi');
        $response->assertStatus(200);
    }

    public function test_admin_prodi_cannot_access_de_penetapan_hasil(): void
    {
        $user = $this->makeUser('admin_prodi');
        $response = $this->actingAs($user)->get('/de/penetapan-hasil-akreditasi');
        $response->assertStatus(403);
    }

    // =====================================================
    // Penyampaian Hasil Akreditasi (DE)
    // =====================================================

    public function test_guest_cannot_access_de_penyampaian_hasil(): void
    {
        $response = $this->get('/de/penyampaian-hasil-akreditasi');
        $response->assertRedirect('/login');
    }

    public function test_sekretariat_can_access_de_penyampaian_hasil_index(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/de/penyampaian-hasil-akreditasi');
        $response->assertStatus(200);
    }

    public function test_asesor_cannot_access_de_penyampaian_hasil(): void
    {
        $user = $this->makeUser('asesor');
        $response = $this->actingAs($user)->get('/de/penyampaian-hasil-akreditasi');
        $response->assertStatus(403);
    }

    // =====================================================
    // Penugasan AK (DE)
    // =====================================================

    public function test_guest_cannot_access_de_penugasan_ak(): void
    {
        $response = $this->get('/de/penugasan-ak');
        $response->assertRedirect('/login');
    }

    public function test_sekretariat_can_access_de_penugasan_ak_index(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/de/penugasan-ak');
        $response->assertStatus(200);
    }

    public function test_validator_cannot_access_de_penugasan_ak(): void
    {
        $user = $this->makeUser('validator');
        $response = $this->actingAs($user)->get('/de/penugasan-ak');
        $response->assertStatus(403);
    }

    // =====================================================
    // Masa Sanggah (DE)
    // =====================================================

    public function test_guest_cannot_access_de_masa_sanggah(): void
    {
        $response = $this->get('/de/masa-sanggah');
        $response->assertRedirect('/login');
    }

    public function test_sekretariat_can_access_de_masa_sanggah_index(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/de/masa-sanggah');
        $response->assertStatus(200);
    }
}
