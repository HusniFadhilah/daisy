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
