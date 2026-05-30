<?php

namespace Tests\Feature;

use App\Models\BanptAccreditationChange;
use App\Models\BanptSyncRun;
use App\Models\HasilAkreditasi;
use App\Models\PengajuanAkreditasi;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use App\Services\Banpt\BanptAccreditationSyncService;
use App\Services\Banpt\BanptClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BanptSyncTest extends TestCase
{
    use RefreshDatabase;

    private BanptAccreditationSyncService $service;
    private BanptClient $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = Mockery::mock(BanptClient::class);
        $this->service    = new BanptAccreditationSyncService($this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function makeProgram(array $overrides = []): StudyProgram
    {
        return StudyProgram::factory()->create(array_merge([
            'rumpun'               => 'arsitektur',
            'peringkat_akreditasi' => 'Baik',
            'tanggal_kedaluwarsa'  => '2025-01-01',
            'status_kedaluwarsa'   => 'Kedaluwarsa',
            'akreditasi_source'    => null,
        ], $overrides));
    }

    private function banptResult(array $overrides = []): array
    {
        return array_merge([
            'pt_label'           => 'UNIVERSITAS_TEST',
            'ps_label'           => 'S1-ARSITEKTUR',
            'nama_pt'            => 'Universitas Test',
            'nama_ps'            => 'Arsitektur',
            'jenjang'            => 'S1',
            'tanggal_sk'         => '01-01-2020',
            'peringkat'          => 'Baik Sekali',
            'tanggal_kedaluwarsa' => '2028-01-01',
            'aktif'              => true,
            'raw_payload'        => [],
        ], $overrides);
    }

    private function makeSyncRun(): BanptSyncRun
    {
        return BanptSyncRun::create([
            'started_at' => now(),
            'status'     => BanptSyncRun::STATUS_RUNNING,
        ]);
    }

    // ── Test 1: Sync membuat pending change jika BAN-PT berbeda ──

    public function test_sync_membuat_pending_change_jika_banpt_berbeda(): void
    {
        $program = $this->makeProgram();
        $run     = $this->makeSyncRun();

        $this->mockClient->shouldReceive('findActiveAccreditation')
            ->once()
            ->andReturn($this->banptResult([
                'peringkat'          => 'Unggul',
                'tanggal_kedaluwarsa' => '2030-06-01',
            ]));

        $change = $this->service->syncStudyProgram($program, $run);

        $this->assertNotNull($change);
        $this->assertEquals(BanptAccreditationChange::STATUS_PENDING, $change->status);
        $this->assertEquals('Unggul', $change->new_peringkat_akreditasi);
        $this->assertEquals('2030-06-01', $change->new_tanggal_kedaluwarsa->format('Y-m-d'));
    }

    // ── Test 2: Sync tidak membuat duplicate pending ────────────

    public function test_sync_tidak_membuat_duplicate_pending(): void
    {
        $program = $this->makeProgram();
        $run     = $this->makeSyncRun();
        $run2    = $this->makeSyncRun();

        $banptData = $this->banptResult([
            'peringkat'          => 'Unggul',
            'tanggal_kedaluwarsa' => '2030-06-01',
        ]);

        $this->mockClient->shouldReceive('findActiveAccreditation')
            ->twice()
            ->andReturn($banptData);

        $change1 = $this->service->syncStudyProgram($program, $run);
        $change2 = $this->service->syncStudyProgram($program, $run2);

        $this->assertNotNull($change1);
        $this->assertNull($change2, 'Duplicate pending seharusnya tidak dibuat');

        $this->assertEquals(1, BanptAccreditationChange::where('id_study_program', $program->id)
            ->where('status', 'pending')->count());
    }

    // ── Test 3: Apply berhasil jika tidak ada blocking ───────────

    public function test_apply_berhasil_jika_tidak_ada_blocking(): void
    {
        $program = $this->makeProgram();
        $run     = $this->makeSyncRun();
        $user    = User::factory()->create();

        $change = BanptAccreditationChange::create([
            'id_sync_run'              => $run->id,
            'id_study_program'         => $program->id,
            'old_peringkat_akreditasi' => 'Baik',
            'old_tanggal_kedaluwarsa'  => '2025-01-01',
            'old_status_kedaluwarsa'   => 'Kedaluwarsa',
            'new_peringkat_akreditasi' => 'Unggul',
            'new_tanggal_kedaluwarsa'  => '2030-06-01',
            'new_status_kedaluwarsa'   => 'Aktif',
            'change_hash'              => $this->service->buildChangeHash(
                ['peringkat' => 'Baik', 'kedaluwarsa' => '2025-01-01', 'status' => 'Kedaluwarsa'],
                ['peringkat' => 'Unggul', 'kedaluwarsa' => '2030-06-01', 'status' => 'Aktif']
            ),
            'status'      => 'pending',
            'detected_at' => now(),
        ]);

        $this->service->applyChange($change, $user);

        $change->refresh();
        $this->assertEquals(BanptAccreditationChange::STATUS_APPLIED, $change->status);
        $this->assertEquals($user->id, $change->applied_by);

        $program->refresh();
        $this->assertEquals('Unggul', $program->peringkat_akreditasi);
        $this->assertEquals('banpt', $program->akreditasi_source);
    }

    // ── Test 4: Apply gagal jika ada pengajuan aktif ─────────────

    public function test_apply_menjadi_conflict_jika_ada_pengajuan_aktif(): void
    {
        $program = $this->makeProgram();
        $run     = $this->makeSyncRun();
        $user    = User::factory()->create();

        PengajuanAkreditasi::factory()->create([
            'id_program_studi' => $program->id,
            'status'           => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
        ]);

        $change = BanptAccreditationChange::create([
            'id_sync_run'              => $run->id,
            'id_study_program'         => $program->id,
            'old_peringkat_akreditasi' => 'Baik',
            'old_tanggal_kedaluwarsa'  => '2025-01-01',
            'old_status_kedaluwarsa'   => 'Kedaluwarsa',
            'new_peringkat_akreditasi' => 'Unggul',
            'new_tanggal_kedaluwarsa'  => '2030-06-01',
            'new_status_kedaluwarsa'   => 'Aktif',
            'change_hash'              => $this->service->buildChangeHash(
                ['peringkat' => 'Baik', 'kedaluwarsa' => '2025-01-01', 'status' => 'Kedaluwarsa'],
                ['peringkat' => 'Unggul', 'kedaluwarsa' => '2030-06-01', 'status' => 'Aktif']
            ),
            'status'      => 'pending',
            'detected_at' => now(),
        ]);

        $this->service->applyChange($change, $user);

        $change->refresh();
        $this->assertEquals(BanptAccreditationChange::STATUS_CONFLICT, $change->status);
        $this->assertNotNull($change->conflict_reason);

        $program->refresh();
        $this->assertEquals('Baik', $program->peringkat_akreditasi, 'Data prodi tidak boleh berubah');
    }

    // ── Test 5: Apply gagal jika source = lamdepilar ─────────────

    public function test_apply_menjadi_conflict_jika_source_lamdepilar(): void
    {
        $program = $this->makeProgram(['akreditasi_source' => 'lamdepilar']);
        $run     = $this->makeSyncRun();
        $user    = User::factory()->create();

        $change = BanptAccreditationChange::create([
            'id_sync_run'              => $run->id,
            'id_study_program'         => $program->id,
            'old_peringkat_akreditasi' => 'Baik',
            'old_tanggal_kedaluwarsa'  => '2025-01-01',
            'old_status_kedaluwarsa'   => 'Kedaluwarsa',
            'new_peringkat_akreditasi' => 'Unggul',
            'new_tanggal_kedaluwarsa'  => '2030-06-01',
            'new_status_kedaluwarsa'   => 'Aktif',
            'change_hash'              => $this->service->buildChangeHash(
                ['peringkat' => 'Baik', 'kedaluwarsa' => '2025-01-01', 'status' => 'Kedaluwarsa'],
                ['peringkat' => 'Unggul', 'kedaluwarsa' => '2030-06-01', 'status' => 'Aktif']
            ),
            'status'      => 'pending',
            'detected_at' => now(),
        ]);

        $this->service->applyChange($change, $user);

        $change->refresh();
        $this->assertEquals(BanptAccreditationChange::STATUS_CONFLICT, $change->status);

        $program->refresh();
        $this->assertEquals('Baik', $program->peringkat_akreditasi, 'Data prodi tidak boleh berubah');
    }

    // ── Test 6: Ignore tidak mengubah study_programs ─────────────

    public function test_ignore_tidak_mengubah_study_programs(): void
    {
        $program = $this->makeProgram();
        $run     = $this->makeSyncRun();
        $user    = User::factory()->create();

        $change = BanptAccreditationChange::create([
            'id_sync_run'              => $run->id,
            'id_study_program'         => $program->id,
            'old_peringkat_akreditasi' => 'Baik',
            'new_peringkat_akreditasi' => 'Unggul',
            'new_tanggal_kedaluwarsa'  => '2030-06-01',
            'new_status_kedaluwarsa'   => 'Aktif',
            'change_hash'              => 'testhash123',
            'status'                   => 'pending',
            'detected_at'              => now(),
        ]);

        $this->service->ignoreChange($change, $user);

        $change->refresh();
        $this->assertEquals(BanptAccreditationChange::STATUS_IGNORED, $change->status);
        $this->assertEquals($user->id, $change->ignored_by);

        $program->refresh();
        $this->assertEquals('Baik', $program->peringkat_akreditasi);
    }

    // ── Test 7: markConflict tidak mengubah study_programs ───────

    public function test_conflict_tidak_mengubah_study_programs(): void
    {
        $program = $this->makeProgram();
        $run     = $this->makeSyncRun();
        $user    = User::factory()->create();

        $change = BanptAccreditationChange::create([
            'id_sync_run'              => $run->id,
            'id_study_program'         => $program->id,
            'old_peringkat_akreditasi' => 'Baik',
            'new_peringkat_akreditasi' => 'Unggul',
            'new_tanggal_kedaluwarsa'  => '2030-06-01',
            'new_status_kedaluwarsa'   => 'Aktif',
            'change_hash'              => 'testhash456',
            'status'                   => 'pending',
            'detected_at'              => now(),
        ]);

        $this->service->markConflict($change, $user, 'Test conflict reason');

        $change->refresh();
        $this->assertEquals(BanptAccreditationChange::STATUS_CONFLICT, $change->status);
        $this->assertEquals('Test conflict reason', $change->conflict_reason);

        $program->refresh();
        $this->assertEquals('Baik', $program->peringkat_akreditasi);
    }

    // ── Test 8: Command artisan berjalan dan membuat sync run ─────

    public function test_command_artisan_membuat_sync_run(): void
    {
        // Mock agar tidak hit API asli
        $this->mockClient->shouldReceive('findActiveAccreditation')->andReturn(null);
        $this->app->instance(BanptClient::class, $this->mockClient);
        $this->app->instance(BanptAccreditationSyncService::class, $this->service);

        $this->artisan('banpt:sync-accreditation')
            ->assertExitCode(0);

        $this->assertDatabaseHas('banpt_sync_runs', ['status' => 'success']);
    }
}
