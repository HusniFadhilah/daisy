<!-- Payment Summary Accordion with Password Protection -->
<div class="accordion" id="paymentSummaryAccordion">
    <div class="accordion-item border-success shadow-sm">
        <h2 class="accordion-header" id="headingPaymentSummary">
            <button class="accordion-button collapsed bg-success text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePaymentSummary" aria-expanded="false" aria-controls="collapsePaymentSummary">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <div>
                        <i class="bi bi-shield-lock-fill me-2"></i>
                        <strong>Ringkasan Pembayaran Tervalidasi</strong>
                    </div>
                    <span class="badge bg-light text-dark ms-auto">
                        <i class="bi bi-lock-fill"></i> Protected
                    </span>
                </div>
            </button>
        </h2>

        <div id="collapsePaymentSummary" class="accordion-collapse collapse" aria-labelledby="headingPaymentSummary" data-bs-parent="#paymentSummaryAccordion">
            <div class="accordion-body" id="paymentSummaryContent">
                <!-- Default: Blurred Preview -->
                <div id="summaryBlurred">
                    <div class="row text-center mb-4" style="filter: blur(8px); user-select: none;">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted mb-2">Total Program Studi</h6>
                                <h2 class="mb-0 fw-bold text-primary">***</h2>
                                <small class="text-muted">Prodi yang telah membayar</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted mb-2">Total Nominal</h6>
                                <h2 class="mb-0 fw-bold text-success">Rp ***,***,***</h2>
                                <small class="text-muted">Total pembayaran tervalidasi</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light">
                                <h6 class="text-muted mb-2">Total Transaksi</h6>
                                <h2 class="mb-0 fw-bold text-info">***</h2>
                                <small class="text-muted">Jumlah invoice tervalidasi</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning alert-permanent text-center mb-0">
                        <i class="bi bi-lock-fill fs-1 mb-3 d-block"></i>
                        <h5 class="mb-3">Data ini Dilindungi Password</h5>
                        <p class="mb-3">Untuk melihat detail ringkasan pembayaran, Anda harus memasukkan password khusus.</p>
                        <button type="button" class="btn btn-primary" onclick="showPasswordModal()">
                            <i class="bi bi-key-fill"></i> Masukkan Password
                        </button>
                    </div>
                </div>

                <!-- After Password Verified -->
                <div id="summaryUnlocked" class="d-none">
                    <!-- Summary Stats -->
                    <div class="row text-center mb-4" id="summaryStats">
                        <!-- Will be populated by JS -->
                    </div>

                    <!-- Tabs for Detailed Breakdown -->
                    <ul class="nav nav-tabs mb-3" id="summaryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="university-tab" data-bs-toggle="tab" data-bs-target="#universityTab" type="button">
                                <i class="bi bi-building"></i> Per Universitas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="degree-tab" data-bs-toggle="tab" data-bs-target="#degreeTab" type="button">
                                <i class="bi bi-mortarboard"></i> Per Jenjang
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthlyTab" type="button">
                                <i class="bi bi-calendar-month"></i> Bulanan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recentTab" type="button">
                                <i class="bi bi-clock-history"></i> Terbaru
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="summaryTabsContent">
                        <!-- Per University -->
                        <div class="tab-pane fade show active" id="universityTab" role="tabpanel">
                            <div id="universityContent"></div>
                        </div>

                        <!-- Per Degree Level -->
                        <div class="tab-pane fade" id="degreeTab" role="tabpanel">
                            <div id="degreeContent"></div>
                        </div>

                        <!-- Monthly -->
                        <div class="tab-pane fade" id="monthlyTab" role="tabpanel">
                            <div id="monthlyContent"></div>
                        </div>

                        <!-- Recent Payments -->
                        <div class="tab-pane fade" id="recentTab" role="tabpanel">
                            <div id="recentContent"></div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="lockSummary()">
                            <i class="bi bi-lock-fill"></i> Kunci Kembali
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-shield-lock-fill"></i> Verifikasi Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info alert-permanent">
                    <i class="bi bi-info-circle"></i>
                    Masukkan password khusus untuk mengakses ringkasan pembayaran. Password ini berbeda dengan password akun login Anda.
                </div>

                <form id="passwordForm">
                    <div class="mb-3">
                        <label for="summaryPassword" class="form-label">Password Khusus</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="summaryPassword" placeholder="Masukkan password khusus..." required autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>

                    <div class="alert alert-warning alert-permanent small mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Session akan aktif selama <strong>{{ config('payment.session_timeout', 30) }} menit</strong>. Setelah itu, Anda harus memasukkan password lagi.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="verifyPassword()" id="btnVerify">
                    <i class="bi bi-key-fill"></i> Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Custom accordion styles */
    #paymentSummaryAccordion .accordion-button.collapsed {
        background: linear-gradient(135deg, #11998e 0%, #12a149ff 100%);
    }

    #paymentSummaryAccordion .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, #11998e 0%, #12a149ff 100%);
        color: white;
        box-shadow: none;
    }

    #paymentSummaryAccordion .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(17, 153, 142, 0.5);
    }

    #paymentSummaryAccordion .accordion-button::after {
        filter: brightness(0) invert(1);
    }

</style>
@endpush

@push('scripts')
<script>
    let passwordModal;

    document.addEventListener('DOMContentLoaded', function() {
        passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
    });

    function showPasswordModal() {
        document.getElementById('summaryPassword').value = '';
        document.getElementById('passwordError').textContent = '';
        document.getElementById('summaryPassword').classList.remove('is-invalid');
        passwordModal.show();
    }

    function togglePasswordVisibility() {
        const input = document.getElementById('summaryPassword');
        const icon = document.getElementById('toggleIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    async function verifyPassword() {
        const password = document.getElementById('summaryPassword').value;
        const btnVerify = document.getElementById('btnVerify');
        const passwordInput = document.getElementById('summaryPassword');
        const errorDiv = document.getElementById('passwordError');

        if (!password) {
            passwordInput.classList.add('is-invalid');
            errorDiv.textContent = 'Password harus diisi';
            return;
        }

        // Disable button
        btnVerify.disabled = true;
        btnVerify.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memverifikasi...';

        try {
            const response = await fetch("{{ route('de.payment-summary.verify') }}", {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    , 'Accept': 'application/json'
                , }
                , body: JSON.stringify({
                    password: password
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Password correct
                passwordModal.hide();
                loadSummaryData();
            } else {
                // Password wrong
                passwordInput.classList.add('is-invalid');
                errorDiv.textContent = data.message || 'Password yang Anda masukkan salah.';
            }
        } catch (error) {
            console.error('Error:', error);
            passwordInput.classList.add('is-invalid');
            errorDiv.textContent = 'Terjadi kesalahan saat memverifikasi password';
        } finally {
            btnVerify.disabled = false;
            btnVerify.innerHTML = '<i class="bi bi-key-fill"></i> Verifikasi';
        }
    }

    async function loadSummaryData() {
        try {
            const response = await fetch("{{ route('de.payment-summary.data') }}", {
                method: 'GET'
                , headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    , 'Accept': 'application/json'
                , }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                displaySummaryData(result.data);

                // Show unlocked, hide blurred
                document.getElementById('summaryBlurred').classList.add('d-none');
                document.getElementById('summaryUnlocked').classList.remove('d-none');
            } else {
                if (result.requires_password) {
                    showPasswordModal();
                } else {
                    Swal.fire('Perhatian', result.message || 'Gagal memuat data', 'error');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Perhatian', 'Terjadi kesalahan saat memuat data', 'error');
        }
    }

    function displaySummaryData(data) {
        const summary = data.summary;

        // Summary Stats
        document.getElementById('summaryStats').innerHTML = `
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <h6 class="text-muted mb-2">Total Program Studi</h6>
                    <h2 class="mb-0 fw-bold text-primary">${summary.total_prodi}</h2>
                    <small class="text-muted">Prodi yang telah membayar</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <h6 class="text-muted mb-2">Total Nominal</h6>
                    <h2 class="mb-0 fw-bold text-success">Rp ${formatNumber(summary.total_nominal)}</h2>
                    <small class="text-muted">Total pembayaran tervalidasi</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <h6 class="text-muted mb-2">Total Transaksi</h6>
                    <h2 class="mb-0 fw-bold text-info">${summary.total_transaksi}</h2>
                    <small class="text-muted">Jumlah invoice tervalidasi</small>
                </div>
            </div>
        `;

        // By University
        let universityHtml = '<div class="table-responsive"><table class="table table-sm table-hover">';
        universityHtml += '<thead><tr><th>Universitas</th><th>Prodi</th><th>Transaksi</th><th class="text-end">Total</th></tr></thead><tbody>';

        Object.entries(data.by_university).forEach(function(entry) {
            const univ = entry[0];
            const stats = entry[1];
            universityHtml += `
                <tr>
                    <td>${univ}</td>
                    <td><span class="badge bg-info">${stats.prodi_count}</span></td>
                    <td><span class="badge bg-primary">${stats.count}</span></td>
                    <td class="text-end"><strong class="text-success">Rp ${formatNumber(stats.total)}</strong></td>
                </tr>
            `;
        });
        universityHtml += '</tbody></table></div>';
        document.getElementById('universityContent').innerHTML = universityHtml;

        // By Degree
        let degreeHtml = '<div class="table-responsive"><table class="table table-sm table-hover">';
        degreeHtml += '<thead><tr><th>Jenjang</th><th>Transaksi</th><th class="text-end">Total</th></tr></thead><tbody>';

        Object.entries(data.by_degree).forEach(function(entry) {
            const degree = entry[0];
            const stats = entry[1];
            degreeHtml += `
                <tr>
                    <td>${degree}</td>
                    <td><span class="badge bg-primary">${stats.count}</span></td>
                    <td class="text-end"><strong class="text-success">Rp ${formatNumber(stats.total)}</strong></td>
                </tr>
            `;
        });
        degreeHtml += '</tbody></table></div>';
        document.getElementById('degreeContent').innerHTML = degreeHtml;

        // Monthly
        let monthlyHtml = '<div class="table-responsive"><table class="table table-sm table-hover">';
        monthlyHtml += '<thead><tr><th>Bulan</th><th>Transaksi</th><th class="text-end">Total</th></tr></thead><tbody>';

        data.monthly.forEach(function(month) {
            monthlyHtml += `
                <tr>
                    <td>${month.month}</td>
                    <td><span class="badge bg-primary">${month.count}</span></td>
                    <td class="text-end"><strong class="text-success">Rp ${formatNumber(month.total)}</strong></td>
                </tr>
            `;
        });
        monthlyHtml += '</tbody></table></div>';
        document.getElementById('monthlyContent').innerHTML = monthlyHtml;

        // Recent Payments
        let recentHtml = '<div class="table-responsive"><table class="table table-sm table-hover">';
        recentHtml += '<thead><tr><th>Invoice</th><th>Program Studi</th><th>Tanggal</th><th class="text-end">Jumlah</th></tr></thead><tbody>';

        data.recent_payments.forEach(function(payment) {
            recentHtml += `
                <tr>
                    <td><code>${payment.nomor_invoice}</code></td>
                    <td>${payment.prodi}</td>
                    <td><small class="text-muted">${payment.tanggal}</small></td>
                    <td class="text-end"><strong class="text-success">Rp ${formatNumber(payment.jumlah)}</strong></td>
                </tr>
            `;
        });
        recentHtml += '</tbody></table></div>';
        document.getElementById('recentContent').innerHTML = recentHtml;
    }

    async function lockSummary() {
        if (!(await swalConfirmSubmit('warning', 'Apakah Anda yakin ingin mengunci kembali ringkasan pembayaran?'))) {
            return;
        }

        try {
            const response = await fetch("{{ route('de.payment-summary.logout') }}", {
                method: 'POST'
                , headers: {
                    'Content-Type': 'application/json'
                    , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    , 'Accept': 'application/json'
                , }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                document.getElementById('summaryUnlocked').classList.add('d-none');
                document.getElementById('summaryBlurred').classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // Enter key to submit password
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('summaryPassword');
        if (passwordField) {
            passwordField.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    verifyPassword();
                }
            });
        }
    });

</script>
@endpush
