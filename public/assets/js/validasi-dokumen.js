async function fetchValidationSummary(url) {
    const elLoading = document.getElementById('validationLoading');
    const elContent = document.getElementById('validationContent');
    const elEmpty = document.getElementById('validationEmpty');
    const elError = document.getElementById('validationError');
    const elBadge = document.getElementById('validationBadge');

    // reset state
    if (elLoading) {
        elLoading.classList.remove('d-none');
    }
    if (elContent) {
        elContent.classList.add('d-none');
    }
    if (elEmpty) {
        elEmpty.classList.add('d-none');
    }
    if (elError) {
        elError.classList.add('d-none');
    }
    if (elBadge) {
        elBadge.className = 'badge bg-secondary';
        elBadge.textContent = 'Memuat...';
    }

    try {
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Request gagal');

        if (elLoading) elLoading.classList.add('d-none');

        if (!data.has_validation) {
            elEmpty.classList.remove('d-none');
            elBadge.className = 'badge bg-secondary';
            elBadge.textContent = 'Belum ada';
            return;
        }

        // render
        const v = data.validation;
        const counts = v.counts;

        document.getElementById('validatorName').textContent = data.validator ? data.validator.name : '-';

        document.getElementById('valLedCount').textContent = `${counts.led.reviewed}/${counts.led.total}`;
        document.getElementById('valSuplemenCount').textContent = `${counts.suplemen.reviewed}/${counts.suplemen.total}`;
        document.getElementById('valLkpsCount').textContent = `${counts.lkps.reviewed}/${counts.lkps.total}`;

        const pct = counts.total.percentage ? counts.total.percentage : 0;
        const bar = document.getElementById('valTotalBar');
        bar.style.width = pct + '%';
        document.getElementById('valTotalText').textContent = `${pct}%`;
        document.getElementById('valUpdatedAt').textContent = v.updated_at ? v.updated_at : '-';

        // notes
        document.getElementById('valNoteAll').textContent = (v.notes.catatan_validator || '-');
        document.getElementById('valNoteLed').textContent = (v.notes.catatan_led || '-');
        document.getElementById('valNoteSuplemen').textContent = (v.notes.catatan_suplemen || '-');
        document.getElementById('valNoteLkps').textContent = (v.notes.catatan_lkps || '-');

        // badge status: final_action dan completeness
        // final_action: approve|revision|null
        if (!v.final_action) {
            elBadge.className = 'badge bg-info';
            elBadge.textContent = v.is_complete ? 'Lengkap (Belum Submit)' : 'Belum Lengkap';
        } else if (v.final_action === 'approve') {
            elBadge.className = 'badge bg-success';
            elBadge.textContent = 'Disubmit (Telah Disetujui)';
        } else if (v.final_action === 'revision') {
            elBadge.className = 'badge bg-warning text-dark';
            elBadge.textContent = 'Disubmit (Perlu Revisi)';
        } else {
            elBadge.className = 'badge bg-secondary';
            elBadge.textContent = 'Status';
        }

        elContent.classList.remove('d-none');

    } catch (err) {
        console.error(err);
        elLoading.classList.add('d-none');
        elError.classList.remove('d-none');
        elBadge.className = 'badge bg-danger';
        elBadge.textContent = 'Error';
    }
}

function isKategoriA(grade) {
    return String(grade || '').toUpperCase() === 'A';
}

function hasCatatan(x) {
    return !!(x && x.catatan && String(x.catatan).trim().length);
}

function isSudahTepat(x) {
    return normalizeGrade(x.grade) === 'A' && x.needs_revision == false;
}

function normalizeGrade(grade) {
    const g = String(grade || '').trim().toUpperCase();
    const m = g.match(/[ABC]/); // ambil A/B/C pertama
    return m ? m[0] : '';
}

// item masuk "Perlu revisi" kalau:
// - grade B/C, atau
// - needs_revision true, atau
// - ada catatan (meskipun grade kosong)
function isPerluRevisi(x) {
    const g = normalizeGrade(x.grade);
    return x.needs_revision === true || g === 'B' || g === 'C';
}

function kategoriReviewLabel(grade) {
    const g = String(grade || '').toUpperCase();
    if (g === 'A') return 'Sudah tepat';
    if (g === 'B') return 'Kurang lengkap, perlu melengkapi';
    if (g === 'C') return 'Perlu diperbaiki';
    return grade ? `${g}` : '-';
}

function kategoriBadgeClass(grade) {
    const g = String(grade || '').toUpperCase();
    if (g === 'A') return 'bg-success';
    if (g === 'B') return 'bg-warning text-dark';
    if (g === 'C') return 'bg-danger';
    return 'bg-secondary';
}

async function fetchValidationDetails(url) {
    const listEl = document.getElementById('valRevisionList');
    const emptyEl = document.getElementById('valRevisionEmpty');

    // reset
    const skEl = document.getElementById('valRevisionSkeleton');

    // reset + show skeleton
    listEl.classList.add('d-none');
    emptyEl.classList.add('d-none');
    listEl.innerHTML = '';

    if (skEl) skEl.classList.remove('d-none');

    const res = await fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.message || 'Gagal ambil detail validasi');

    if (!data.has_validation) {
        if (skEl) skEl.classList.add('d-none');
        emptyEl.classList.remove('d-none');
        return;
    }
    const items = data.items || {};

    // ambil semua items dulu (tanpa filter), nanti kita split ke 2 tab
    const ledAll = (items.led || []);
    const suplemenAll = (items.suplemen || []).map(it => ({
        ...it
        , label: formatSuplemenLabel(it)
    }));
    const lkpsAll = (items.lkps || []);

    // split: Perlu Revisi vs Sudah Tepat
    const ledRevisi = ledAll.filter(isPerluRevisi);
    const suplemenRevisi = suplemenAll.filter(isPerluRevisi);
    const lkpsRevisi = lkpsAll.filter(isPerluRevisi);

    const ledOk = ledAll.filter(isSudahTepat);
    const suplemenOk = suplemenAll.filter(isSudahTepat);
    const lkpsOk = lkpsAll.filter(isSudahTepat);

    const totalRevisi = ledRevisi.length + suplemenRevisi.length + lkpsRevisi.length;
    const totalOk = ledOk.length + suplemenOk.length + lkpsOk.length;

    // kalau dua-duanya kosong -> empty
    if (totalRevisi === 0 && totalOk === 0) {
        if (skEl) skEl.classList.add('d-none');
        emptyEl.classList.remove('d-none');
        return;
    }

    function formatSuplemenLabel(it) {
        // ambil judul aslinya tanpa prefix [xxx]
        const raw = (it.label || '').replace(/^\[[^\]]+\]\s*/i, '').trim();

        // kalau section bentuknya bagian_a -> Bagian A
        const m = (it.section || '').match(/^bagian_([a-z])$/i);
        if (m) {
            return `Bagian ${m[1].toUpperCase()} - ${raw}`;
        }

        // selain itu, bikin prefix dari section: pemastian_cpl -> Pemastian Cpl
        if (it.section) {
            return `${titleCaseWords(it.section)} - ${raw}`;
        }

        // fallback kalau section kosong
        return raw || it.label || '';
    }

    // helper render section
    function renderSection(title, arr, withOpenBtn) {
        if (!arr.length) return '';
        let out = `<div class="mb-3">
                <div class="fw-semibold mb-1">${title}</div>
                <ul class="list-group">`;

        arr.forEach(it => {
            out += `
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">${escapeHtml(it.label)}</div>
                        <div class="small text-muted">
                            Kategori review:
                            <span class="badge ${kategoriBadgeClass(it.grade)}">${escapeHtml(kategoriReviewLabel(it.grade))}</span>
                        </div>
                        ${it.catatan ? `<div class="small mt-1">${escapeHtml(it.catatan)}</div>` : ''}
                    </div>
                </li>`;
        });

        out += `</ul></div>`;
        return out;
    }

    // ==== TAB HTML ====
    let html = `
            <ul class="nav nav-tabs" id="revTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link ${totalRevisi ? 'active' : ''}" id="tab-revisi" data-bs-toggle="tab" data-bs-target="#pane-revisi" type="button" role="tab">
                Perlu revisi <span class="badge bg-danger ms-1">${totalRevisi}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link ${!totalRevisi ? 'active' : ''}" id="tab-ok" data-bs-toggle="tab" data-bs-target="#pane-ok" type="button" role="tab">
                Sudah tepat <span class="badge bg-success ms-1">${totalOk}</span>
                </button>
            </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom p-3" id="revTabContent">

            <div class="tab-pane fade ${totalRevisi ? 'show active' : ''}" id="pane-revisi" role="tabpanel">
                ${totalRevisi === 0 ? `<div class="text-muted">Tidak ada item yang perlu revisi.</div>` : `
                ${renderSection('Revisi LED', ledRevisi, true)}
                ${renderSection('Revisi Suplemen', suplemenRevisi, false)}
                ${renderSection('Revisi LKPS', lkpsRevisi, false)}
                `}
            </div>

            <div class="tab-pane fade ${!totalRevisi ? 'show active' : ''}" id="pane-ok" role="tabpanel">
                ${totalOk === 0 ? `<div class="text-muted">Belum ada item kategori A (Sudah tepat).</div>` : `
                ${renderSection('LED - Sudah tepat', ledOk, true)}
                ${renderSection('Suplemen - Sudah tepat', suplemenOk, false)}
                ${renderSection('LKPS - Sudah tepat', lkpsOk, false)}
                `}
            </div>

            </div>
            `;
    if (skEl) skEl.classList.add('d-none');
    listEl.innerHTML = html;
    listEl.classList.remove('d-none');
}

window.openElemen = function (elemenId) {
    const card = document.querySelector('.elemen-card[data-elemen-id="' + elemenId + '"]');
    if (!card) return;

    // 1) buka accordion parent kriteria (kalau masih tertutup)
    let kriteriaBody = null;
    const kriteriaCard = card.closest('.kriteria-card');
    if (kriteriaCard) {
        kriteriaBody = kriteriaCard.querySelector('.accordion-collapse');
    }

    if (kriteriaBody) {
        const kriteriaInstance = bootstrap.Collapse.getOrCreateInstance(kriteriaBody, {
            toggle: false
        });
        kriteriaInstance.show();

        // opsional: set chevron icon kriteria ke down
        let kriteriaHeaderBtn = null;
        if (kriteriaBody.parentElement) {
            kriteriaHeaderBtn = kriteriaBody.parentElement.querySelector('.kriteria-btn');
        }

        if (kriteriaHeaderBtn) {
            const kriteriaChevron = kriteriaHeaderBtn.querySelector('.chevron-icon');
            if (kriteriaChevron) {
                kriteriaChevron.classList.remove('bi-chevron-right');
                kriteriaChevron.classList.add('bi-chevron-down');
            }
        }
    }

    // 2) buka elemen collapse
    const elemenCollapse =
        document.getElementById('collapse-elemen-' + elemenId) ||
        card.querySelector('.elemen-collapse');

    const openElemenCollapse = function () {
        if (elemenCollapse) {
            const elemenInstance = bootstrap.Collapse.getOrCreateInstance(elemenCollapse, {
                toggle: false
            });
            elemenInstance.show();

            // opsional: set chevron icon elemen ke down
            const elemenHeaderBtn = card.querySelector('.elemen-btn');
            if (elemenHeaderBtn) {
                const elemenChevron = elemenHeaderBtn.querySelector('.chevron-icon');
                if (elemenChevron) {
                    elemenChevron.classList.remove('bi-chevron-right');
                    elemenChevron.classList.add('bi-chevron-down');
                }
            }
        }

        // 3) scroll + highlight
        card.scrollIntoView({
            behavior: 'smooth'
            , block: 'start'
        });

        card.classList.add('border', 'border-danger');
        setTimeout(function () {
            card.classList.remove('border', 'border-danger');
        }, 2000);
    };

    // kasih delay kalau parent kriteria dibuka dulu
    if (kriteriaBody) {
        setTimeout(openElemenCollapse, 250);
    } else {
        openElemenCollapse();
    }
};

function escapeHtml(str) {
    return String(str || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
