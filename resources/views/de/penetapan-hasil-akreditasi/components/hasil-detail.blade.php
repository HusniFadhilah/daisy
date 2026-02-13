{{-- resources/views/de/penyampaian-hasil-akreditasi/components/hasil-detail.blade.php --}}
{{-- Bisa digunakan oleh penyampaian DAN penetapan --}}

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-secondary text-white border-0">
        <h5 class="mb-0">
            <i class="bi bi-info-circle"></i>
            Keterangan Batasan Skor Akreditasi
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-2">
            {{-- 0-250: Tidak Terakreditasi --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #f8d7da;">
                    <div class="me-3">
                        <strong class="text-dark">0 - 250</strong>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-dark">Tidak Terakreditasi</strong>
                        <div><small class="text-muted">Ditolak validator dokumen</small></div>
                    </div>
                </div>
            </div>

            {{-- 251-350: Terakreditasi --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #d1ecf1;">
                    <div class="me-3">
                        <strong class="text-dark">251 - 350</strong>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-dark">Terakreditasi</strong>
                    </div>
                </div>
            </div>

            {{-- 351-360: Terakreditasi Unggul with requirement --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #d4edda;">
                    <div class="me-3">
                        <strong class="text-dark">351 - 360</strong>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-dark">⁠Terakreditasi Unggul with Requirement (2 Tahun)*</strong>
                        <div><small class="text-muted">*Dengan syarat pelampauan standar</small></div>
                    </div>
                </div>
            </div>

            {{-- 361-400: Terakreditasi Unggul --}}
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 rounded h-100" style="background-color: #c3e6cb;">
                    <div class="me-3">
                        <strong class="text-dark">361 - 400</strong>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-dark">Terakreditasi Unggul (5 Tahun)</strong>
                        <div><small class="text-muted">*Dengan syarat pelampauan standar</small></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-light alert-permanent mt-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Catatan:</strong> Untuk status akreditasi <strong>Unggul</strong>, selain mencapai skor >= 351,
            program studi harus memiliki <strong>minimal 1 elemen dengan kategori: Pelampauan Standar</strong>
            di <strong>setiap kriteria</strong> (D, E, P, I, L, A, R).
        </div>
    </div>
</div>

{{-- Validasi Peringkat Unggul --}}
@if($hasil->skor_al >= 361)
<div class="card mb-4 border-{{ $validationSummary['dapat_unggul'] ? 'success' : 'warning' }}">
    <div class="card-header bg-{{ $validationSummary['dapat_unggul'] ? 'success' : 'warning' }} text-white">
        <h5 class="mb-0">
            <i class="bi bi-{{ $validationSummary['dapat_unggul'] ? 'shield-check' : 'exclamation-triangle' }}"></i>
            Validasi Syarat Status Akreditasi UNGGUL
        </h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-success bg-opacity-10 rounded">
                    <i class="bi bi-check-circle-fill text-success fs-3 me-3"></i>
                    <div>
                        <strong>Skor Memenuhi Syarat</strong>
                        <div class="text-muted">Skor >= 361 ({{ number_format($hasil->skor_al, 2) }})</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-{{ $validationSummary['pelampauan_memenuhi'] ? 'success' : 'danger' }} bg-opacity-10 rounded">
                    <i class="bi bi-{{ $validationSummary['pelampauan_memenuhi'] ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} fs-3 me-3"></i>
                    <div>
                        <strong>Pelampauan Standar</strong>
                        <div class="text-muted">
                            @if($validationSummary['pelampauan_memenuhi'])
                            Semua kriteria terpenuhi ✓
                            @else
                            {{ count($validationSummary['missing_kriteria']) }} kriteria belum memiliki pelampauan
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail per Kriteria --}}
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Kriteria</th>
                        <th class="text-center" width="25%">Status Pelampauan</th>
                        <th class="text-center" width="20%">Jumlah Elemen Skor 4</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\HasilAkreditasi::KRITERIA_REQUIRED as $kode)
                    @php
                    $status = $validationSummary['kriteria_status'][$kode] ?? ['has_pelampauan' => false, 'jumlah_elemen_skor_4' => 0];
                    @endphp
                    <tr>
                        <td class="text-center"><strong>{{ $kode }}</strong></td>
                        <td class="text-center">
                            @if($status['has_pelampauan'])
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Terpenuhi
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Belum Terpenuhi
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $status['jumlah_elemen_skor_4'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!$validationSummary['pelampauan_memenuhi'])
        <div class="alert alert-warning alert-permanent mt-3 mb-0">
            <strong>⚠️ Perhatian:</strong> Meskipun skor mencapai >= 361, status akreditasi <strong>TIDAK DAPAT</strong> ditetapkan sebagai UNGGUL
            karena kriteria <strong>{{ implode(', ', $validationSummary['missing_kriteria']) }}</strong>
            belum memiliki minimal 1 elemen dengan kategori Pelampauan Standar.
            <br><br>
            Status akreditasi akan diubah menjadi: <strong class="text-danger">BAIK SEKALI</strong>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Detail Skor per Kriteria --}}
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-bar-chart-fill"></i>
            Detail Skor per Kriteria
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="10%">Kode</th>
                        <th width="40%">Nama Kriteria</th>
                        <th class="text-center" width="15%">Jumlah Elemen</th>
                        <th class="text-center" width="15%">Total Bobot</th>
                        <th class="text-center" width="15%">Skor Tertimbang</th>
                        <th class="text-center" width="10%">Pelampauan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kriteriaList as $kode => $data)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $kode }}</span></td>
                        <td>{{ $data['nama'] }}</td>
                        <td class="text-center">{{ $data['elemen_count'] }}</td>
                        <td class="text-center">{{ number_format($data['total_bobot'], 2) }}</td>
                        <td class="text-center">
                            <strong class="text-primary">{{ number_format($data['total_skor'], 2) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($data['has_pelampauan'])
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            @else
                            <i class="bi bi-dash-circle text-muted fs-5"></i>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(!empty($kriteriaList))
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">TOTAL:</th>
                        <th class="text-center">{{ number_format($hasil->total_bobot_al ?? 0, 2) }}</th>
                        <th class="text-center">
                            <strong class="text-primary fs-5">{{ number_format($hasil->skor_al ?? 0, 2) }}</strong>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- Detail Skor per Elemen Standar --}}
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">
            <i class="bi bi-list-check"></i>
            Detail Skor per Elemen Standar
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover" id="table-elemen">
                <thead class="table-light align-middle">
                    <tr>
                        <th width="5%">Kriteria - Elemen</th>
                        <th width="30%">Pernyataan Elemen</th>
                        <th class="text-center" width="35%">Kategori</th>
                        <th class="text-center" width="10%">Bobot</th>
                        <th class="text-center" width="15%">Skor Tertimbang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($elemenList as $elemen)
                    @php
                    $kategori = $elemen['skor_kategori'] ?? ['label' => '-', 'class' => 'secondary'];
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="badge bg-secondary mb-1" style="width: fit-content;">{{ $elemen['kode_kriteria'] }}</span>
                                <code class="text-primary">{{ $elemen['kode_elemen'] }}</code>
                            </div>
                        </td>
                        <td>{{ Str::limit($elemen['nama_elemen'], 120) }}</td>
                        <td class="text-center">
                            <span class="badge text-wrap" style="width: 15rem; background-color: {{ $kategori['color'] }}; color: #222;">
                                {{ $kategori['label'] }}
                            </span>
                            <div class="mt-1">
                                <small class="text-muted">({{ number_format($elemen['skor'], 2) }})</small>
                            </div>
                        </td>
                        <td class="text-center">{{ number_format($elemen['bobot'], 2) }}</td>
                        <td class="text-center">
                            <strong>{{ number_format($elemen['skor_tertimbang'], 2) }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Catatan Validasi --}}
@if($hasil->catatan_validasi)
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="bi bi-chat-left-text-fill"></i>
            Catatan Validasi
        </h5>
    </div>
    <div class="card-body">
        <pre class="mb-0" style="white-space: pre-wrap;">{{ $hasil->catatan_validasi }}</pre>
    </div>
</div>
@endif
