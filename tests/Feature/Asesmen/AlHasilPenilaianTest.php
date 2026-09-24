<?php

namespace Tests\Feature\Asesmen;

use Tests\TestCase;

class AlHasilPenilaianTest extends TestCase
{
    public function test_guest_cannot_access_hasil_penilaian_al(): void
    {
        $response = $this->get('/al/berkas/1/hasil');
        $response->assertRedirect('/login');
    }

    public function test_hasil_penilaian_al_route_is_registered(): void
    {
        $this->assertEquals(
            url('/al/berkas/1/hasil'),
            route('al.berkas.hasil', 1)
        );
    }

    public function test_unfinalize_penyampaian_hasil_route_is_registered(): void
    {
        $this->assertEquals(
            url('/de/penyampaian-hasil-akreditasi/1/unfinalize'),
            route('de.penyampaian-hasil-akreditasi.unfinalize', 1)
        );
    }
}
