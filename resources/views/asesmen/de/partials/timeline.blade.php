<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Timeline Pelaksanaan Akreditasi
        </h5>
    </div>
    <div class="card-body">
        <div class="timeline">
            @foreach([
            // ========================================
            // FASE 1: PERSIAPAN
            // ========================================
            [
            'date' => $pengajuan->tanggal_pengingat,
            'label' => 'Pengingat Masa Akreditasi',
            'icon' => 'bi-bell',
            'step' => 1
            ],
            [
            'date' => $pengajuan->tanggal_surat_permohonan_dikirim,
            'label' => 'Permohonan Akreditasi',
            'icon' => 'bi-envelope',
            'step' => 2
            ],
            [
            'date' => $pengajuan->tanggal_template_led_dikirim,
            'label' => 'Pengiriman Formulir dan Template Dokumen',
            'icon' => 'bi-file-earmark-arrow-down',
            'step' => 3
            ],
            [
            'date' => $pengajuan->tanggal_pembayaran,
            'label' => 'Validasi Pembayaran',
            'icon' => 'bi-credit-card-2-front',
            'step' => 4
            ],
            [
            'date' => $pengajuan->tanggal_draft_borang,
            'label' => 'Penerimaan Dokumen dari Prodi',
            'icon' => 'bi-file-earmark-check',
            'step' => 5
            ],

            // ========================================
            // FASE 2: VALIDASI DOKUMEN
            // ========================================
            [
            'date' => $pengajuan->tanggal_validasi_borang_assigned,
            'label' => 'Validasi Dokumen',
            'icon' => 'bi-clipboard-check',
            'step' => 6,
            'color' => 'primary'
            ],
            [
            'date' => $pengajuan->tanggal_pelaporan_validasi_borang,
            'label' => 'Pelaporan Validasi Dokumen',
            'icon' => 'bi-file-earmark-text',
            'step' => 7,
            'color' => 'primary'
            ],

            // ========================================
            // FASE 3: ASESMEN KECUKUPAN (AK)
            // ========================================
            [
            'date' => $pengajuan->tanggal_penugasan_asesor_ak,
            'label' => 'Penugasan Asesor untuk AK',
            'icon' => 'bi-person-check',
            'step' => 8,
            'color' => 'success'
            ],
            [
            'date' => $pengajuan->tanggal_validasi_ak,
            'label' => 'Validasi AK',
            'icon' => 'bi-clipboard2-check',
            'step' => 9,
            'color' => 'success'
            ],
            [
            'date' => $pengajuan->tanggal_pelaporan_ak,
            'label' => 'Pelaporan AK',
            'icon' => 'bi-file-earmark-medical',
            'step' => 10,
            'color' => 'success'
            ],

            // ========================================
            // FASE 4: ASESMEN LAPANGAN (AL)
            // ========================================
            [
            'date' => $pengajuan->tanggal_penugasan_asesor_al,
            'label' => 'Penugasan Asesor untuk AL',
            'icon' => 'bi-person-badge',
            'step' => 11,
            'color' => 'info'
            ],
            [
            'date' => $pengajuan->tanggal_pelaksanaan_al,
            'label' => 'Pelaksanaan AL dan Penyampaian Berita Acara AL',
            'icon' => 'bi-building',
            'step' => 12,
            'color' => 'info'
            ],
            [
            'date' => $pengajuan->tanggal_pelaporan_al,
            'label' => 'Pelaporan AL',
            'icon' => 'bi-clipboard-data',
            'step' => 13,
            'color' => 'info'
            ],

            // ========================================
            // FASE 5: PENYELESAIAN
            // ========================================
            [
            'date' => $pengajuan->tanggal_hasil_akreditasi_dikirim,
            'label' => 'Penyampaian Hasil Akreditasi',
            'icon' => 'bi-envelope-paper',
            'step' => 14,
            'color' => 'warning'
            ],
            [
            'date' => $pengajuan->tanggal_masa_sanggah_mulai,
            'label' => 'Masa Sanggah',
            'icon' => 'bi-clock-history',
            'step' => 15,
            'color' => 'warning',
            ],
            [
            'date' => $pengajuan->tanggal_pelaksanaan_banding,
            'label' => 'Pelaksanaan Banding',
            'icon' => 'bi-arrow-repeat',
            'step' => 16,
            'color' => 'danger',
            ],
            [
            'date' => $pengajuan->tanggal_pelaporan_banding,
            'label' => 'Pelaporan Banding',
            'icon' => 'bi-file-earmark-ruled',
            'step' => 17,
            'color' => 'danger',
            ],
            [
            'date' => $pengajuan->tanggal_penetapan,
            'label' => 'Penetapan Hasil Akreditasi',
            'icon' => 'bi-award',
            'step' => 18,
            'color' => 'success'
            ],
            [
            'date' => $pengajuan->tanggal_pelaporan_hasil,
            'label' => 'Pelaporan Hasil Akreditasi',
            'icon' => 'bi-megaphone',
            'step' => 19,
            'color' => 'success'
            ],
            [
            'date' => $pengajuan->tanggal_penyimpanan,
            'label' => 'Penyimpanan Arsip Akreditasi',
            'icon' => 'bi-archive',
            'step' => 20,
            'color' => 'secondary'
            ],
            ] as $item)
            @php
            $isCompleted = $item['date'] !== null;
            $iconColor = $isCompleted ? 'text-success' : 'text-muted';
            $itemColor = $item['color'] ?? ($isCompleted ? 'success' : 'muted');
            $isOptional = $item['optional'] ?? false;
            @endphp

            <div class="d-flex mb-3 {{ $isOptional && !$isCompleted ? 'opacity-50' : '' }}">
                <div class="text-center" style="min-width: 40px;">
                    @if($isCompleted)
                    <i class="bi bi-check-circle-fill {{ $iconColor }}" style="font-size: 1.2rem;"></i>
                    @else
                    <i class="bi bi-circle {{ $iconColor }}"></i>
                    @endif
                    {{-- <div class="mt-1">
                        <small class="badge bg-light text-dark">{{ $item['step'] }}</small>
                </div> --}}
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong class="{{ $isCompleted ? 'text-' . $itemColor : 'text-muted' }}">
                            <i class="{{ $item['icon'] ?? 'bi-circle' }} me-1"></i>
                            {{ $item['label'] }}
                            @if($isOptional)
                            <span class="badge bg-secondary ms-1">Opsional</span>
                            @endif
                        </strong>
                    </div>
                    @if($isCompleted)
                    <span class="badge bg-{{ $itemColor }}">
                        {{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('d M Y') : '' }}
                    </span>
                    @endif
                </div>
                @if($isCompleted)
                <small class="text-muted">
                    <i class="bi bi-clock"></i> {{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('H:i') : '' }} WIB
                </small>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
</div>
