{{-- resources/views/de/penerimaan-dokumen/components/table-content.blade.php --}}

<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Penerimaan Draft Dokumen
            </h5>
            <span class="badge bg-primary">Total: {{ $pengajuans->total() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Permohonan Akreditasi</th>
                        <th width="25%">Status Penerimaan Dokumen</th>
                        <th width="20%">Status Dokumen</th>
                        <th width="20%">Tanggal Update Status</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $index => $pengajuan)
                    @php
                    // Check document status
                    $hasLED = $pengajuan->dokumen->whereIn('jenis_dokumen', ['draft_borang', 'data_kualitatif'])->isNotEmpty();
                    $hasLKPS = $pengajuan->dokumen->where('jenis_dokumen', 'data_kuantitatif')->isNotEmpty();
                    $hasSuplemen = $pengajuan->dokumen->where('jenis_dokumen', 'data_suplemen')->isNotEmpty();

                    $docComplete = $hasLED && $hasLKPS;
                    $docCount = ($hasLED ? 1 : 0) + ($hasLKPS ? 1 : 0) + ($hasSuplemen ? 1 : 0);

                    // Gunakan actual_status dari controller (sudah diset dari latestStatusLog)
                    $currentStatus = $pengajuan->actual_status ?? $pengajuan->status;
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>
                        <td>
                            {!! $pengajuan->getCustomBadgeLastStatus('draft_borang','de','label_short_for') !!}

                            @if($currentStatus == \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI)
                            <br><small class="text-muted">Perlu menugaskan validator</small>
                            @endif
                        </td>
                        <td>
                            @if($docComplete)
                            <div class="doc-status-badge doc-complete">
                                <i class="bi bi-check-circle-fill"></i> Lengkap
                            </div>
                            <div class="progress progress-custom mt-2">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            @elseif($docCount > 0)
                            <div class="doc-status-badge doc-incomplete">
                                <i class="bi bi-exclamation-triangle-fill"></i> Belum Lengkap
                            </div>
                            <div class="progress progress-custom mt-2">
                                <div class="progress-bar bg-warning" style="width: {{ ($docCount / 2) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $hasLED ? '✓ LED' : '✗ LED' }} |
                                {{ $hasLKPS ? '✓ LKPS' : '✗ LKPS' }}
                                @if($pengajuan->jenis_akreditasi == 'menuju_unggul')
                                {{ $hasSuplemen ? '| ✓ Suplemen' : '| ✗ Suplemen' }}
                                @endif
                            </small>
                            @else
                            <div class="doc-status-badge doc-none">
                                <i class="bi bi-x-circle-fill"></i> Belum Diupload
                            </div>
                            <div class="progress progress-custom mt-2">
                                <div class="progress-bar bg-danger" style="width: 0%"></div>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($pengajuan->status_changed_at)
                            <small class="text-muted">
                                <i class="bi bi-clock"></i>
                                {{ \Carbon\Carbon::parse($pengajuan->status_changed_at)->format('d M Y H:i') }}
                            </small>
                            @elseif($pengajuan->tanggal_draft_borang)
                            {{ $pengajuan->tanggal_draft_borang->format('d M Y H:i') }}
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>

                            {{-- @if($docComplete && $currentStatus == \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA)
                            <button type="button" class="btn btn-sm btn-success" onclick="konfirmasiPenerimaan({{ $pengajuan->id }})" title="Konfirmasi Penerimaan">
                            <i class="bi bi-check-circle"></i>
                            </button>
                            @endif --}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">Tidak ada data pengajuan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pengajuans->hasPages())
    <div class="card-footer">
        {{ $pengajuans->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    function konfirmasiPenerimaan(id) {
        if (confirm('Konfirmasi bahwa dokumen telah diterima lengkap?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/de/penerimaan-dokumen/${id}/konfirmasi`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            document.body.appendChild(form);
            form.submit();
        }
    }

</script>
@endpush
