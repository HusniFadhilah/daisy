{{-- resources/views/de/validasi-pembayaran-banding/components/table-content.blade.php --}}

<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Invoice Pembayaran Banding</h5>
        <span class="badge bg-primary">Total: {{ $pembayarans->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="4%">#</th>
                        <th width="18%">Nomor Invoice</th>
                        <th width="24%">Permohonan Akreditasi</th>
                        <th width="13%">Nominal</th>
                        <th width="13%">Jatuh Tempo</th>
                        <th width="15%">Status Pembayaran</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $index => $pembayaran)
                    <tr>
                        <td>{{ $pembayarans->firstItem() + $index }}</td>
                        <td>
                            <strong class="d-block">{{ $pembayaran->nomor_invoice }}</strong>
                            <small class="text-muted">
                                {{ $pembayaran->created_at->locale('id')->translatedFormat('d M Y') }}
                            </small>
                        </td>
                        <td>
                            {!! $pembayaran->pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                        </td>
                        <td>
                            <strong class="text-success">
                                Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                            </strong>
                        </td>
                        <td>
                            @if($pembayaran->tanggal_jatuh_tempo)
                            {{ $pembayaran->tanggal_jatuh_tempo->locale('id')->translatedFormat('d M Y') }}
                            @if($pembayaran->tanggal_jatuh_tempo < now() && !in_array($pembayaran->status_pembayaran, ['terverifikasi']))
                                <br><span class="badge bg-danger">Terlambat</span>
                                @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                        </td>
                        <td>
                            @php
                            $cfg = [
                            'menunggu_pembayaran' => ['warning', 'hourglass-split', 'Menunggu Pembayaran'],
                            'menunggu_verifikasi' => ['info', 'clock-history', 'Menunggu Validasi'],
                            'upload_ulang' => ['secondary', 'arrow-repeat', 'Upload Ulang'],
                            'terverifikasi' => ['success', 'check-circle', 'Tervalidasi'],
                            'ditolak' => ['danger', 'x-circle', 'Ditolak'],
                            ][$pembayaran->status_pembayaran] ?? ['secondary','question-circle','—'];
                            @endphp
                            <span class="badge bg-{{ $cfg[0] }} text-wrap">
                                <i class="bi bi-{{ $cfg[1] }}"></i> {{ $cfg[2] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('de.validasi-pembayaran-banding.show', $pembayaran->id) }}" class="btn btn-sm btn-info" title="Detail & Validasi">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                            <span class="text-muted">Tidak ada data invoice pembayaran banding</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pembayarans->hasPages())
    <div class="card-footer">{{ $pembayarans->onEachSide(1)->links() }}</div>
    @endif
</div>
