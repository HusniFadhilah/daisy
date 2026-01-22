<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Daftar Monitoring Pelaporan AL
        </h6>
        <span class="badge bg-primary">Total: {{ $pengajuans->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($pengajuans->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">Nomor Permohonan</th>
                        <th width="22%">Program Studi</th>
                        <th width="13%">Status</th>
                        <th width="15%">Validator</th>
                        <th width="15%">Status Pelaporan</th>
                        <th width="10%">Tanggal</th>
                        <th width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $index => $pengajuan)
                    @php
                    // Get validators
                    $validators = $pengajuan->asesmen?->asesmenUserRoles->filter(function($aur) {
                    return $aur->role_selected->name === 'validator';
                    }) ?? collect();

                    // Get laporan documents
                    $laporanDocs = $pengajuan->asesmen?->asesmenDocuments ?? collect();
                    $hasLaporan = $laporanDocs->count() > 0;

                    // Determine status
                    if ($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN) {
                    $statusConfig = [
                    'class' => 'success',
                    'icon' => 'check-circle-fill',
                    'text' => 'Sudah Dilaporkan'
                    ];
                    $pelaporanConfig = [
                    'class' => 'success',
                    'icon' => 'file-earmark-check-fill',
                    'text' => 'Sudah Upload'
                    ];
                    } else {
                    $statusConfig = [
                    'class' => 'warning',
                    'icon' => 'clock',
                    'text' => 'Validasi Selesai'
                    ];
                    $pelaporanConfig = $hasLaporan ? [
                    'class' => 'info',
                    'icon' => 'file-earmark-arrow-up',
                    'text' => 'Ada Laporan'
                    ] : [
                    'class' => 'danger',
                    'icon' => 'x-circle',
                    'text' => 'Belum Upload'
                    ];
                    }
                    @endphp
                    <tr>
                        <td>{{ $pengajuans->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                        </td>
                        <td>
                            <div class="mb-1">
                                <strong>{{ $pengajuan->studyProgram->name }}</strong>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-building"></i> {{ $pengajuan->studyProgram->university->name }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusConfig['class'] }}">
                                <i class="bi bi-{{ $statusConfig['icon'] }}"></i> {{ $statusConfig['text'] }}
                            </span>
                        </td>
                        <td>
                            @if($validators->count() > 0)
                            @foreach($validators as $validator)
                            <div class="mb-1">
                                <small>
                                    <i class="bi bi-person-check"></i> {{ $validator->user->name }}
                                </small>
                            </div>
                            @endforeach
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $pelaporanConfig['class'] }}">
                                <i class="bi bi-{{ $pelaporanConfig['icon'] }}"></i> {{ $pelaporanConfig['text'] }}
                            </span>
                            @if($hasLaporan)
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="bi bi-file-earmark"></i> {{ $laporanDocs->count() }} file
                                </small>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($pengajuan->tanggal_pelaporan_al)
                            <small><strong>{{ \Carbon\Carbon::parse($pengajuan->tanggal_pelaporan_al)->format('d M Y') }}</strong></small>
                            <br>
                            <small class="text-muted">Dilaporkan</small>
                            @else
                            <small>{{ $pengajuan->created_at->format('d M Y') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.pelaporan-al.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3 mb-0">Tidak ada data pelaporan AL</p>
        </div>
        @endif
    </div>
    @if($pengajuans->hasPages())
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Menampilkan {{ $pengajuans->firstItem() }} - {{ $pengajuans->lastItem() }} dari {{ $pengajuans->total() }} data
            </div>
            <div>
                {{ $pengajuans->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
