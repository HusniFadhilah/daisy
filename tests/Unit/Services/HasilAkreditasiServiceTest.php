<?php

namespace Tests\Unit\Services;

use App\Models\Asesmen;
use App\Models\HasilAkreditasi;
use App\Models\PengajuanAkreditasi;
use App\Repositories\SyaratAkreditasiRepository;
use App\Services\HasilAkreditasiService;
use App\Services\LkpsDataReaderService;
use Database\Seeders\Tests\HasilAkreditasiTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HasilAkreditasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private HasilAkreditasiService $service;
    private $mockSyaratRepo;
    private $mockLkpsReader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSyaratRepo = Mockery::mock(SyaratAkreditasiRepository::class);
        $this->mockLkpsReader = Mockery::mock(LkpsDataReaderService::class);

        $this->service = new HasilAkreditasiService(
            $this->mockLkpsReader,
            $this->mockSyaratRepo,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────
    // calculateAK — validation errors
    // ─────────────────────────────────────────────────────────────

    public function test_calculateAK_throws_when_study_program_has_no_category(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();
        $asesmen = $data['asesmen'];

        // Remove category from study program
        $asesmen->studyProgram->update(['id_category' => null]);
        $asesmen->load('studyProgram');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Program studi belum memiliki kategori');

        $this->service->calculateAK($asesmen);
    }

    public function test_calculateAK_throws_when_no_penilaian_exists(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();
        $asesmen = $data['asesmen'];

        // Delete all penilaian
        \App\Models\PenilaianElemenAk::where('id_asesmen', $asesmen->id)->delete();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Belum ada penilaian AK');

        $this->service->calculateAK($asesmen);
    }

    // ─────────────────────────────────────────────────────────────
    // calculateAK — happy path
    // ─────────────────────────────────────────────────────────────

    public function test_calculateAK_returns_expected_structure(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();
        $asesmen = $data['asesmen'];

        $result = $this->service->calculateAK($asesmen);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('skor_total', $result);
        $this->assertArrayHasKey('skor_tertimbang', $result);
        $this->assertArrayHasKey('total_bobot', $result);
        $this->assertArrayHasKey('detail_kriteria', $result);
        $this->assertArrayHasKey('detail_elemen', $result);
        $this->assertArrayHasKey('pelampauan_standar', $result);
        $this->assertArrayHasKey('jumlah_elemen', $result);
    }

    public function test_calculateAK_counts_elemen_correctly(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        // Seeder creates 3 penilaian for 3 different elemen
        $result = $this->service->calculateAK($data['asesmen']);

        $this->assertSame(3, $result['jumlah_elemen']);
    }

    public function test_calculateAK_skor_is_sum_of_weighted_scores(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        // Each elemen: skor=3, bobot=10 → tertimbang = 30
        // 3 elemen → total = 90
        $result = $this->service->calculateAK($data['asesmen']);

        $this->assertEqualsWithDelta(90.0, $result['skor_total'], 0.01);
    }

    public function test_calculateAK_groups_detail_per_kriteria(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        $result = $this->service->calculateAK($data['asesmen']);

        // Seeder has 2 kriteria: A (2 elemen) and B (1 elemen)
        $this->assertCount(2, $result['detail_kriteria']);
        $this->assertArrayHasKey('A', $result['detail_kriteria']);
        $this->assertArrayHasKey('B', $result['detail_kriteria']);
    }

    // ─────────────────────────────────────────────────────────────
    // calculateAL — happy path
    // ─────────────────────────────────────────────────────────────

    public function test_calculateAL_throws_when_no_penilaian_exists(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();
        $asesmen = $data['asesmen'];

        \App\Models\PenilaianElemenAl::where('id_asesmen', $asesmen->id)->delete();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Belum ada penilaian AL');

        $this->service->calculateAL($asesmen);
    }

    public function test_calculateAL_returns_expected_structure(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        $result = $this->service->calculateAL($data['asesmen']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('skor_total', $result);
        $this->assertArrayHasKey('jumlah_elemen', $result);
        $this->assertSame(3, $result['jumlah_elemen']);
    }

    public function test_calculateAL_skor_calculated_correctly(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        // Each AL penilaian: skor=3, bobot=10 → tertimbang = 30 each → total = 90
        $result = $this->service->calculateAL($data['asesmen']);

        $this->assertEqualsWithDelta(90.0, $result['skor_total'], 0.01);
    }

    // ─────────────────────────────────────────────────────────────
    // pelampauan_standar — elemen dengan skor >= 4
    // ─────────────────────────────────────────────────────────────

    public function test_calculateAK_marks_pelampauan_for_skor_4_and_above(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();
        $asesmen = $data['asesmen'];

        // Update skor of one elemen to 4 (pelampauan standar)
        \App\Models\PenilaianElemenAk::where('id_asesmen', $asesmen->id)
            ->where('id_elemen', $data['elemenA1']->id)
            ->update(['skor' => 4, 'skor_final' => 4]);

        $result = $this->service->calculateAK($asesmen);

        // elemenA1 kriteria is 'A' → should appear in pelampauan_standar['A']
        $this->assertArrayHasKey('A', $result['pelampauan_standar']);
        $this->assertCount(1, $result['pelampauan_standar']['A']);
    }

    public function test_calculateAK_no_pelampauan_when_all_skor_below_4(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        // All penilaian have skor=3 (below 4) from seeder
        $result = $this->service->calculateAK($data['asesmen']);

        $this->assertEmpty($result['pelampauan_standar']);
    }

    // ─────────────────────────────────────────────────────────────
    // buildScoreResult — returns zero when degree_level missing
    // ─────────────────────────────────────────────────────────────

    public function test_calculateAK_returns_zero_skor_when_bobot_not_found(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();
        $asesmen = $data['asesmen'];

        // Delete all bobot_penilaian so no weights are found
        \App\Models\BobotPenilaian::query()->delete();

        $result = $this->service->calculateAK($asesmen);

        // All elemen skipped due to missing bobot → total = 0
        $this->assertSame(0, $result['jumlah_elemen']);
        $this->assertEqualsWithDelta(0.0, $result['skor_total'], 0.01);
    }

    // ─────────────────────────────────────────────────────────────
    // canUnfinalizeHasilAL — sampai masa sanggah berakhir
    // ─────────────────────────────────────────────────────────────

    public function test_can_unfinalize_while_masa_sanggah_masih_berjalan(): void
    {
        $pengajuan = new PengajuanAkreditasi([
            'status' => PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
            'tanggal_masa_sanggah_selesai' => now()->addDay(),
        ]);
        $hasil = new HasilAkreditasi(['status' => 'final_al']);

        $this->assertTrue($this->service->canUnfinalizeHasilAL($pengajuan, $hasil));
    }

    public function test_cannot_unfinalize_when_masa_sanggah_selesai(): void
    {
        $pengajuan = new PengajuanAkreditasi([
            'status' => PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            'tanggal_masa_sanggah_selesai' => now()->subMinute(),
        ]);
        $hasil = new HasilAkreditasi(['status' => 'final_al']);

        $this->assertFalse($this->service->canUnfinalizeHasilAL($pengajuan, $hasil));
    }

    public function test_cannot_unfinalize_when_deadline_passed_but_status_belum_update(): void
    {
        $pengajuan = new PengajuanAkreditasi([
            'status' => PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
            'tanggal_masa_sanggah_selesai' => now()->subMinute(),
        ]);
        $hasil = new HasilAkreditasi(['status' => 'final_al']);

        $this->assertFalse($this->service->canUnfinalizeHasilAL($pengajuan, $hasil));
    }

    public function test_has_unfinished_al_asesor_when_penilaian_draft(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        $this->assertFalse($this->service->hasUnfinishedAlAsesor($data['asesmen']->id));

        \App\Models\PenilaianElemenAl::where('id_asesmen', $data['asesmen']->id)
            ->update(['status' => 'draft']);

        $this->assertTrue($this->service->hasUnfinishedAlAsesor($data['asesmen']->id));
    }
}
