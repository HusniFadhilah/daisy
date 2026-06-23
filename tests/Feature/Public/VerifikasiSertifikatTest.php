<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiSertifikatTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_accessible_without_login(): void
    {
        $response = $this->get('/verifikasi-sertifikat');
        $response->assertStatus(200);
    }

    public function test_search_page_shows_verifikasi_heading(): void
    {
        $response = $this->get('/verifikasi-sertifikat');
        $response->assertStatus(200)
            ->assertSee('Verifikasi Sertifikat Akreditasi');
    }

    public function test_search_page_shows_search_form(): void
    {
        $response = $this->get('/verifikasi-sertifikat');
        $response->assertStatus(200)
            ->assertSee('nomor', false)
            ->assertSee('Cari', false);
    }

    public function test_search_redirects_when_nomor_provided(): void
    {
        $nomor = '0001/SK/LAM-DEPILAR/AK/VIII/2024';

        $response = $this->get('/verifikasi-sertifikat?nomor=' . urlencode($nomor));

        $response->assertRedirect();
    }

    public function test_search_stays_on_page_when_nomor_empty(): void
    {
        $response = $this->get('/verifikasi-sertifikat?nomor=');
        $response->assertStatus(200);
    }

    public function test_search_stays_on_page_when_nomor_whitespace(): void
    {
        // URL-encode whitespace for valid HTTP request
        $response = $this->get('/verifikasi-sertifikat?nomor=+');
        $response->assertStatus(200);
    }

    public function test_show_accessible_without_login(): void
    {
        $response = $this->get('/verifikasi-sertifikat/RANDOM-NOMOR-123');
        $response->assertStatus(200);
    }

    public function test_show_returns_tidak_valid_for_unknown_nomor(): void
    {
        $response = $this->get('/verifikasi-sertifikat/NOMOR-TIDAK-ADA-DI-DB');
        $response->assertStatus(200)
            ->assertSee('TIDAK VALID');
    }

    public function test_show_displays_the_queried_nomor_sertifikat(): void
    {
        $nomor = 'CONTOH-NOMOR-001';
        $response = $this->get('/verifikasi-sertifikat/' . $nomor);
        $response->assertStatus(200)
            ->assertSee($nomor);
    }

    public function test_show_includes_search_form_when_not_found(): void
    {
        $response = $this->get('/verifikasi-sertifikat/NOMOR-TIDAK-ADA');
        $response->assertStatus(200)
            ->assertSee('Cari nomor sertifikat lain', false);
    }

    public function test_show_includes_back_link_when_not_found(): void
    {
        $response = $this->get('/verifikasi-sertifikat/NOMOR-TIDAK-ADA');
        $response->assertStatus(200)
            ->assertSee('Kembali ke pencarian', false);
    }

    public function test_show_with_slash_in_nomor_is_accessible(): void
    {
        $response = $this->get('/verifikasi-sertifikat/0001/SK/LAM-DEPILAR/AK/VIII/2024');
        $response->assertStatus(200);
    }

    public function test_search_redirect_target_is_show_route(): void
    {
        $nomor = 'NOMOR-TEST-001';

        $response = $this->get('/verifikasi-sertifikat?nomor=' . $nomor);

        $response->assertRedirectContains('/verifikasi-sertifikat/' . $nomor);
    }
}
