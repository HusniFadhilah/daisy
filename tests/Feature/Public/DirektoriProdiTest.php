<?php

namespace Tests\Feature\Public;

use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirektoriProdiTest extends TestCase
{
    use RefreshDatabase;

    public function test_direktori_prodi_accessible_without_login(): void
    {
        $response = $this->get('/direktori/program-studi');
        $response->assertStatus(200);
    }

    public function test_direktori_prodi_accessible_when_authenticated(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->get('/direktori/program-studi');
        $response->assertStatus(200);
    }

    public function test_guest_direktori_prodi_uses_public_user_menu_without_account_actions(): void
    {
        $response = $this->get('/direktori/program-studi');

        $response->assertStatus(200);
        $response->assertSee('User Daisy');
        $response->assertDontSee('Dr. Eng. Maryono');
        $response->assertDontSee('Profil Saya');
        $response->assertDontSee('Ubah Password');
        $response->assertDontSee('Keluar');
    }

    public function test_guest_direktori_prodi_does_not_render_charts(): void
    {
        $response = $this->get('/direktori/program-studi');

        $response->assertStatus(200);
        $response->assertDontSee('Statistik &amp; Visualisasi', false);
        $response->assertDontSee('chartPeringkat');
    }

    public function test_authenticated_direktori_prodi_renders_charts(): void
    {
        $user = User::factory()->sekretariat()->create();

        $response = $this->actingAs($user)->get('/direktori/program-studi');

        $response->assertStatus(200);
        $response->assertSee('Statistik & Visualisasi', false);
        $response->assertSee('chartPeringkat');
    }

    public function test_direktori_prodi_shows_stats_with_zero_when_empty(): void
    {
        $response = $this->get('/direktori/program-studi');
        $response->assertStatus(200);
        $response->assertViewHas('stats');

        $stats = $response->viewData('stats');
        $this->assertSame(0, $stats['total']);
    }

    public function test_direktori_prodi_counts_only_non_example_programs(): void
    {
        University::factory()->create(['is_example' => false]);
        StudyProgram::factory()->example()->create();

        $response = $this->get('/direktori/program-studi');
        $response->assertStatus(200);

        $stats = $response->viewData('stats');
        $this->assertSame(0, $stats['total']);
    }

    public function test_direktori_prodi_counts_active_non_example_programs(): void
    {
        StudyProgram::factory()->count(3)->active()->create(['is_example' => false]);
        StudyProgram::factory()->example()->create();

        $response = $this->get('/direktori/program-studi');
        $stats = $response->viewData('stats');

        $this->assertSame(3, $stats['total']);
    }

    public function test_direktori_prodi_ajax_returns_json_structure(): void
    {
        $response = $this->getJson('/direktori/program-studi/ajax');
        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_direktori_prodi_ajax_returns_zero_records_on_empty_db(): void
    {
        $response = $this->getJson('/direktori/program-studi/ajax');
        $response->assertStatus(200)
            ->assertJson([
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
    }

    public function test_direktori_prodi_ajax_excludes_example_programs(): void
    {
        StudyProgram::factory()->example()->create();

        $response = $this->getJson('/direktori/program-studi/ajax');
        $response->assertJson(['recordsTotal' => 0]);
    }

    public function test_direktori_prodi_ajax_search_text_filter(): void
    {
        StudyProgram::factory()->active()->create([
            'name'       => 'Arsitektur Lingkungan',
            'is_example' => false,
        ]);

        $response = $this->getJson('/direktori/program-studi/ajax?search_text=arsitektur');
        $response->assertStatus(200)
            ->assertJsonStructure(['recordsFiltered', 'data']);
    }

    public function test_direktori_prodi_ajax_status_filter(): void
    {
        StudyProgram::factory()->active()->create(['is_example' => false]);
        StudyProgram::factory()->expired()->create(['is_example' => false]);

        $response = $this->getJson('/direktori/program-studi/ajax?status=Aktif');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertSame(1, $data['recordsFiltered']);
    }

    public function test_direktori_prodi_ajax_returns_correct_draw_value(): void
    {
        $response = $this->getJson('/direktori/program-studi/ajax?draw=5');
        $response->assertJson(['draw' => 5]);
    }

    public function test_direktori_prodi_ajax_returns_no_sk(): void
    {
        StudyProgram::factory()->active()->create([
            'is_example' => false,
            'no_sk' => '123/SK/LAMDEPILAR/VII/2026',
        ]);

        $response = $this->getJson('/direktori/program-studi/ajax');

        $response->assertStatus(200);
        $this->assertSame('123/SK/LAMDEPILAR/VII/2026', $response->json('data.0.no_sk'));
    }

    public function test_direktori_prodi_ajax_paginates_with_length_param(): void
    {
        StudyProgram::factory()->count(10)->active()->create(['is_example' => false]);

        $response = $this->getJson('/direktori/program-studi/ajax?start=0&length=3');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(3, $data['data']);
        $this->assertSame(10, $data['recordsTotal']);
    }

    public function test_direktori_prodi_view_has_required_data(): void
    {
        $response = $this->get('/direktori/program-studi');
        $response->assertViewHas('stats');
        $response->assertViewHas('universities');
        $response->assertViewHas('degreeLevels');
        $response->assertViewHas('chartData');
    }
}
