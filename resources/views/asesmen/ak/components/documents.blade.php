<div class="card mb-4">
    {{-- Header (clickable) --}}
    <div class="card-header bg-light d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseDokumenProdi" aria-expanded="false" style="cursor: pointer;">
        <h5 class="mb-0">
            <i class="bi bi-folder2-open me-1"></i> Dokumen dari Prodi
        </h5>

        <div class="d-flex align-items-center gap-2">
            <small class="text-muted">Lihat File</small>
            <i class="bi bi-chevron-down"></i>
        </div>
    </div>

    {{-- Body (collapsible) --}}
    <div id="collapseDokumenProdi" class="collapse">
        <div class="card-body">
            @php
            $docCards = [
            'led' => [
            'label' => 'Laporan Evaluasi Diri (LED)',
            'btn_class' => 'btn-primary',
            'empty_text' => 'Belum ada file LED diupload.',
            ],
            'suplemen' => [
            'label' => 'Suplemen',
            'btn_class' => 'btn-info text-white',
            'empty_text' => 'Belum ada file suplemen yang diupload.',
            ],
            'lkps' => [
            'label' => 'Laporan Kinerja Program Studi (LKPS)',
            'btn_class' => 'btn-success',
            'empty_text' => 'Belum ada file LKPS yang diupload.',
            ],
            ];
            @endphp

            <div class="row g-3">
                @foreach($docCards as $key => $config)
                @php $file = $uploadedFiles[$key] ?? null; @endphp

                <div class="col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-start gap-3">
                            {{-- Icon --}}
                            <i class="bi {{ $file?->file_icon_class ?? 'bi-file-earmark' }} fs-4 flex-shrink-0"></i>

                            {{-- Text --}}
                            <div class="flex-grow-1">
                                <div class="fw-bold mb-1">{{ $config['label'] }}</div>

                                @if($file)
                                <div class="text-muted small fw-semibold text-break">
                                    {{ $file->original_filename }}
                                </div>
                                <div class="text-muted small">
                                    {{ $file->created_at->diffForHumans() }}
                                </div>
                                @else
                                <div class="text-muted small">
                                    {{ $config['empty_text'] }}
                                </div>
                                @endif
                            </div>

                            {{-- Button --}}
                            @if($file && $file->download_url)
                            <div class="flex-shrink-0">
                                <a class="btn btn-sm {{ $config['btn_class'] }}" href="{{ $file->download_url }}" target="_blank" rel="noopener">
                                    <i class="bi bi-download"></i> Buka
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Optional: pengesahan --}}
            @if(!empty($uploadedFiles['pengesahan']))
            <hr class="my-3">
            <div class="d-flex align-items-start gap-3">
                <i class="bi {{ $uploadedFiles['pengesahan']->file_icon_class }} fs-4 flex-shrink-0"></i>

                <div class="flex-grow-1">
                    <div class="fw-bold">Lembar Pengesahan</div>
                    <div class="text-muted small text-break">
                        {{ $uploadedFiles['pengesahan']->original_filename }}
                        • {{ $uploadedFiles['pengesahan']->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="flex-shrink-0">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ $uploadedFiles['pengesahan']->download_url }}" target="_blank" rel="noopener">
                        <i class="bi bi-download"></i> Buka
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
