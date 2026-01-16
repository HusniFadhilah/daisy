<div class="modal fade" id="modalRevisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clipboard-check"></i> Detail Validasi & Revisi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body">

                {{-- Catatan Validator --}}
                @if(false)

                @if($currentValidator->borangValidation->catatan_validator)
                <div class="alert alert-info">
                    <strong>Catatan Validator:</strong><br>
                    {{ $currentValidator->borangValidation->catatan_validator }}
                </div>
                @endif

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-led">
                            LED
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-suplemen">
                            Suplemen
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lkps">
                            LKPS
                        </button>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content">

                    {{-- TAB LED --}}
                    <div class="tab-pane fade show active" id="tab-led">
                        <ul class="list-group list-group-flush">
                            @foreach($revisiLed as $item)
                            <li class="list-group-item">
                                <strong>{{ $item['kode'] }}</strong><br>
                                <span class="text-muted">{{ $item['deskripsi'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- TAB SUPLEMEN --}}
                    <div class="tab-pane fade" id="tab-suplemen">
                        <ul class="list-group list-group-flush">
                            @foreach($revisiSuplemen as $item)
                            <li class="list-group-item">
                                <strong>{{ $item['bagian'] }}</strong><br>
                                <span class="text-muted">{{ $item['deskripsi'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- TAB LKPS --}}
                    <div class="tab-pane fade" id="tab-lkps">
                        <ul class="list-group list-group-flush">
                            @foreach($revisiLkps as $item)
                            <li class="list-group-item">
                                <strong>{{ $item['kode'] }}</strong><br>
                                <span class="text-muted">{{ $item['deskripsi'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
