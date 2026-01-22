{{-- resources/views/de/validasi-pembayaran/components/table-content.blade.php --}}

<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Daftar Pembayaran
            </h5>
            <span class="badge bg-primary">Total: {{ $pembayarans->total() }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Nomor Invoice</th>
                        <th width="20%">Program Studi</th>
                        <th width="12%">Jumlah</th>
                        <th width="12%">Jatuh Tempo</th>
                        <th width="12%">Tanggal Bayar</th>
                        <th width="12%">Status</th>
                        <th width="12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $index => $pembayaran)
                    <tr>
                        <td>{{ $pembayarans->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $pembayaran->nomor_invoice }}</strong>
                            <br>
                            <small class="text-muted">
                                Dibuat pada: {{ $pembayaran->created_at->format('d M Y') }}
                            </small>
                            <br class="mb-2">
                            <small class="text-muted">
                                <span>Jenis akreditasi:</span>
                                <b>{{ $pembayaran->pengajuan->jenis_akreditasi_label }}</b>
                            </small>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $pembayaran->pengajuan->studyProgram->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $pembayaran->pengajuan->studyProgram->university->name }}
                                </small>
                                <br>
                                <span class="badge bg-info">
                                    {{ $pembayaran->pengajuan->studyProgram->degreeLevel->name }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <strong class="text-success">
                                Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                            </strong>
                        </td>
                        <td>
                            @if($pembayaran->tanggal_jatuh_tempo)
                            {{ $pembayaran->tanggal_jatuh_tempo->format('d M Y') }}
                            <br>
                            @if($pembayaran->tanggal_jatuh_tempo < now() && $pembayaran->status_pembayaran == 'menunggu_pembayaran')
                                <span class="badge bg-danger">Terlambat</span>
                                @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                        </td>
                        <td>
                            @if($pembayaran->tanggal_pembayaran)
                            {{ $pembayaran->tanggal_pembayaran->format('d M Y H:i') }}
                            @else
                            <span class="text-muted">Belum dibayar</span>
                            @endif
                        </td>
                        <td>
                            @php
                            $statusConfig = [
                            'menunggu_pembayaran' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu Pembayaran'],
                            'menunggu_verifikasi' => ['class' => 'info', 'icon' => 'clock-history', 'text' => 'Menunggu Verifikasi'],
                            'terverifikasi' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Terverifikasi'],
                            'upload_ulang' => ['class' => 'secondary', 'icon' => 'arrow-repeat', 'text' => 'Upload Ulang'],
                            'ditolak' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                            ];
                            $status = $statusConfig[$pembayaran->status_pembayaran] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                            @endphp
                            <span class="badge bg-{{ $status['class'] }}">
                                <i class="bi bi-{{ $status['icon'] }}"></i>
                                {{ $status['text'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.validasi-pembayaran.show', $pembayaran->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>

                            @if($pembayaran->status_pembayaran == 'menunggu_verifikasi')
                            <button type="button" class="btn btn-sm btn-success" onclick="showValidasiModal({{ $pembayaran->id }})" title="Validasi">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">Tidak ada data pembayaran</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pembayarans->hasPages())
    <div class="card-footer">
        {{ $pembayarans->links() }}
    </div>
    @endif
</div>
