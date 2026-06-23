<?php

namespace Tests\Feature\Asesmen;

use App\Domain\Akreditasi\AllowedStatus;
use App\Models\PengajuanAkreditasi;
use App\Models\User;
use Database\Seeders\Tests\HasilAkreditasiTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // Status constants integrity
    // ─────────────────────────────────────────────────────────────

    public function test_status_constants_have_expected_string_values(): void
    {
        $this->assertSame('new',            PengajuanAkreditasi::STATUS_NEW);
        $this->assertSame('draft',          PengajuanAkreditasi::STATUS_DRAFT);
        $this->assertSame('ak_in_progress', PengajuanAkreditasi::STATUS_AK_IN_PROGRESS);
        $this->assertSame('al_in_progress', PengajuanAkreditasi::STATUS_AL_IN_PROGRESS);
        $this->assertSame('pembayaran_diverifikasi', PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI);
        $this->assertSame('asesor_ak_assigned', PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED);
        $this->assertSame('asesor_al_assigned', PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED);
        $this->assertSame('hasil_akreditasi_dikirim', PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM);
        $this->assertSame('banding_diajukan', PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN);
    }

    // ─────────────────────────────────────────────────────────────
    // AllowedStatus::transitions() integrity
    // ─────────────────────────────────────────────────────────────

    public function test_allowed_status_transitions_returns_array(): void
    {
        $transitions = AllowedStatus::transitions();
        $this->assertIsArray($transitions);
        $this->assertNotEmpty($transitions);
    }

    public function test_draft_can_transition_to_pengingat_dikirim(): void
    {
        $transitions = AllowedStatus::transitions();

        $this->assertArrayHasKey(PengajuanAkreditasi::STATUS_DRAFT, $transitions);
        $this->assertContains(
            PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
            $transitions[PengajuanAkreditasi::STATUS_DRAFT]
        );
    }

    public function test_surat_permohonan_dikirim_has_multiple_transitions(): void
    {
        $transitions = AllowedStatus::transitions();
        $statusKey   = PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM;

        $this->assertArrayHasKey($statusKey, $transitions);
        $this->assertGreaterThanOrEqual(2, count($transitions[$statusKey]));
    }

    public function test_pembayaran_path_leads_to_diverifikasi(): void
    {
        $transitions = AllowedStatus::transitions();

        $this->assertContains(
            PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN,
            $transitions[PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Factory & model creation
    // ─────────────────────────────────────────────────────────────

    public function test_factory_creates_pengajuan_with_new_status(): void
    {
        $pengajuan = PengajuanAkreditasi::factory()->create();

        $this->assertSame(PengajuanAkreditasi::STATUS_NEW, $pengajuan->status);
    }

    public function test_factory_creates_in_progress_pengajuan(): void
    {
        $pengajuan = PengajuanAkreditasi::factory()->inProgress()->create();

        $this->assertSame(PengajuanAkreditasi::STATUS_AK_IN_PROGRESS, $pengajuan->status);
    }

    public function test_pengajuan_status_can_be_updated(): void
    {
        $pengajuan = PengajuanAkreditasi::factory()->create([
            'status' => PengajuanAkreditasi::STATUS_NEW,
        ]);

        $pengajuan->update(['status' => PengajuanAkreditasi::STATUS_DRAFT]);

        $this->assertSame(PengajuanAkreditasi::STATUS_DRAFT, $pengajuan->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────
    // Model business methods
    // ─────────────────────────────────────────────────────────────

    public function test_canBeReported_ak_returns_false_when_not_ak_selesai(): void
    {
        $pengajuan = PengajuanAkreditasi::factory()->create([
            'status' => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
        ]);

        $this->assertFalse($pengajuan->canBeReported('ak'));
    }

    public function test_canBeReported_ak_returns_true_when_ak_selesai_and_not_reported(): void
    {
        $pengajuan = PengajuanAkreditasi::factory()->create([
            'status'              => PengajuanAkreditasi::STATUS_AK_SELESAI,
            'tanggal_pelaporan_ak' => null,
        ]);

        $this->assertTrue($pengajuan->canBeReported('ak'));
    }

    public function test_pengajuan_jenis_akreditasi_constants_are_correct(): void
    {
        $this->assertSame('baru',          PengajuanAkreditasi::AKREDITASI_BARU);
        $this->assertSame('terakreditasi', PengajuanAkreditasi::AKREDITASI_TERAKREDITASI);
        $this->assertSame('perpanjangan',  PengajuanAkreditasi::AKREDITASI_PERPANJANGAN);
    }

    // ─────────────────────────────────────────────────────────────
    // Full workflow with seeder data
    // ─────────────────────────────────────────────────────────────

    public function test_seeder_creates_pengajuan_in_ak_in_progress_status(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        $this->assertSame(
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            $data['pengajuan']->status
        );
    }

    public function test_pengajuan_study_program_relationship_works(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();

        $pengajuan = PengajuanAkreditasi::find($data['pengajuan']->id);

        $this->assertNotNull($pengajuan->studyProgram);
        $this->assertSame($data['studyProgram']->id, $pengajuan->studyProgram->id);
    }

    public function test_pengajuan_status_transitions_persisted_correctly(): void
    {
        $data = (new HasilAkreditasiTestSeeder())->run();
        $pengajuan = $data['pengajuan'];

        // Simulate AL begins after AK
        $pengajuan->update(['status' => PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED]);
        $this->assertSame(
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            $pengajuan->fresh()->status
        );

        // Simulate AL in progress
        $pengajuan->update(['status' => PengajuanAkreditasi::STATUS_AL_IN_PROGRESS]);
        $this->assertSame(
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            $pengajuan->fresh()->status
        );

        // Simulate hasil dikirim
        $pengajuan->update(['status' => PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM]);
        $this->assertSame(
            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
            $pengajuan->fresh()->status
        );
    }

    public function test_multiple_pengajuan_with_different_statuses(): void
    {
        $statuses = [
            PengajuanAkreditasi::STATUS_NEW,
            PengajuanAkreditasi::STATUS_DRAFT,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
        ];

        foreach ($statuses as $status) {
            PengajuanAkreditasi::factory()->create(['status' => $status]);
        }

        foreach ($statuses as $status) {
            $count = PengajuanAkreditasi::where('status', $status)->count();
            $this->assertSame(1, $count, "Expected 1 pengajuan with status: {$status}");
        }

        $this->assertSame(4, PengajuanAkreditasi::count());
    }
}
