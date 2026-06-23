<?php

namespace Tests\Feature\Upps;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UppsRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->withRole($role)->create();
    }

    // =====================================================
    // UPPS — admin_prodi only
    // =====================================================

    public function test_guest_cannot_access_upps_pengingat(): void
    {
        $response = $this->get('/upps/pengingat-akreditasi');
        $response->assertRedirect('/login');
    }

    public function test_admin_prodi_can_access_upps_pengingat(): void
    {
        $user = $this->makeUser('admin_prodi');
        $response = $this->actingAs($user)->get('/upps/pengingat-akreditasi');
        $response->assertStatus(200);
    }

    public function test_sekretariat_cannot_access_upps_pengingat(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/upps/pengingat-akreditasi');
        $response->assertStatus(403);
    }

    public function test_asesor_cannot_access_upps_pengingat(): void
    {
        $user = $this->makeUser('asesor');
        $response = $this->actingAs($user)->get('/upps/pengingat-akreditasi');
        $response->assertStatus(403);
    }

    public function test_super_admin_cannot_access_upps_pengingat(): void
    {
        $user = $this->makeUser('super_admin');
        $response = $this->actingAs($user)->get('/upps/pengingat-akreditasi');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_upps_penyampaian_hasil(): void
    {
        $response = $this->get('/upps/penyampaian-hasil-akreditasi');
        $response->assertRedirect('/login');
    }

    public function test_admin_prodi_can_access_upps_penyampaian_hasil(): void
    {
        $user = $this->makeUser('admin_prodi');
        $response = $this->actingAs($user)->get('/upps/penyampaian-hasil-akreditasi');
        $response->assertStatus(200);
    }

    public function test_sekretariat_cannot_access_upps_penyampaian_hasil(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/upps/penyampaian-hasil-akreditasi');
        $response->assertStatus(403);
    }

    // =====================================================
    // Permohonan Akreditasi (admin_prodi + admin_univ)
    // =====================================================

    public function test_guest_cannot_access_permohonan_akreditasi(): void
    {
        $response = $this->get('/permohonan-akreditasi');
        $response->assertRedirect('/login');
    }

    public function test_admin_prodi_can_access_permohonan_akreditasi(): void
    {
        $user = $this->makeUser('admin_prodi');
        $response = $this->actingAs($user)->get('/permohonan-akreditasi');
        $response->assertStatus(200);
    }

    public function test_sekretariat_cannot_access_permohonan_akreditasi(): void
    {
        $user = $this->makeUser('sekretariat');
        $response = $this->actingAs($user)->get('/permohonan-akreditasi');
        $response->assertStatus(403);
    }

    public function test_asesor_cannot_access_permohonan_akreditasi(): void
    {
        $user = $this->makeUser('asesor');
        $response = $this->actingAs($user)->get('/permohonan-akreditasi');
        $response->assertStatus(403);
    }

    // =====================================================
    // UPPS Surat Permohonan
    // =====================================================

    public function test_guest_cannot_access_upps_surat_permohonan(): void
    {
        $response = $this->get('/upps/surat-permohonan');
        $response->assertRedirect('/login');
    }

    public function test_admin_prodi_can_access_upps_surat_permohonan(): void
    {
        $user = $this->makeUser('admin_prodi');
        $response = $this->actingAs($user)->get('/upps/surat-permohonan');
        $response->assertStatus(200);
    }

    // =====================================================
    // UPPS Validasi Pembayaran
    // =====================================================

    public function test_admin_prodi_can_access_upps_validasi_pembayaran(): void
    {
        $user = $this->makeUser('admin_prodi');
        $response = $this->actingAs($user)->get('/upps/validasi-pembayaran');
        $response->assertStatus(200);
    }

    public function test_validator_cannot_access_upps_validasi_pembayaran(): void
    {
        $user = $this->makeUser('validator');
        $response = $this->actingAs($user)->get('/upps/validasi-pembayaran');
        $response->assertStatus(403);
    }
}
