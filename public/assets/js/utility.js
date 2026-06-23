/**
 * ============================================
 * UTILITY FUNCTIONS
 * ============================================
 */

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit'
        , month: 'short'
        , year: 'numeric'
        , hour: '2-digit'
        , minute: '2-digit'
    });
}

// Toggle Sidebar for Mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
}

// Toggle Sidebar Collapse for Desktop
function toggleSidebarCollapse() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const collapseIcon = document.getElementById('collapseIcon');

    if (sidebar) sidebar.classList.toggle('collapsed');
    if (mainContent) mainContent.classList.toggle('sidebar-collapsed');

    // Toggle icon direction
    if (sidebar.classList.contains('collapsed')) {
        collapseIcon.classList.remove('bi-chevron-left');
        collapseIcon.classList.add('bi-chevron-right');
    } else {
        collapseIcon.classList.remove('bi-chevron-right');
        collapseIcon.classList.add('bi-chevron-left');
    }

    // Save state to localStorage
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
}

// Toggle Submenu
function toggleSubmenu(event, submenuId) {
    event.preventDefault();
    const submenu = document.getElementById(submenuId);
    submenu.classList.toggle('show');
}

// Add active class to menu items on click
document.addEventListener('DOMContentLoaded', function () {
    // Restore sidebar collapse state from localStorage (desktop only)
    if (window.innerWidth > 768) {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const collapseIcon = document.getElementById('collapseIcon');

            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            collapseIcon.classList.remove('bi-chevron-left');
            collapseIcon.classList.add('bi-chevron-right');
        }
    }

    // Menu item active state
    document.querySelectorAll('.nav-link').forEach(item => {
        item.addEventListener('click', function (e) {
            if (!this.getAttribute('onclick') || !this.getAttribute('onclick').includes('toggleSubmenu')) {
                document.querySelectorAll('.nav-link').forEach(link => {
                    if (!link.closest('.submenu')) {
                        link.classList.remove('active');
                    }
                });
                if (!this.closest('.submenu')) {
                    this.classList.add('active');
                }
            }
        });
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (event) {
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.querySelector('.menu-toggle');

        if (window.innerWidth <= 768 &&
            !sidebar.contains(event.target) &&
            !menuToggle.contains(event.target) &&
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function () {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        // Reset sidebar on resize
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
        }
    });
});

// ========================================
// ✅ TOAST NOTIFICATION (Keep existing)
// ========================================
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    toastContainer.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, {
        delay: 3000
    });
    bsToast.show();

    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

async function swalConfirmSubmit(icon = 'question', message = 'Apakah Anda yakin?', showCancelButton = true) {
    // Ganti \n jadi <br> agar baris baru terlihat di SweetAlert HTML
    const htmlMessage = message.replace(/\n/g, '<br>');
    const res = await Swal.fire({
        icon: icon,
        html: htmlMessage,
        showCancelButton: showCancelButton
    });
    return res.isConfirmed;
}

// Fungsi dasar SweetAlert
function triggerSweetalert(title, text, icon = 'info') {
    Swal.fire({ title, text, icon });
}

// Fungsi submit form dengan SweetAlert
function submitFormSwal(form, message, href, isForceDelete = false, hrefPermanent = '', confirmButtonText = 'Ya, lanjutkan', denyButtonText = 'Ya, hapus permanen') {
    if (!form) return; // aman jika form tidak ada

    Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        showDenyButton: isForceDelete,
        denyButtonText: denyButtonText,
        confirmButtonColor: '#3085d6',
        denyButtonColor: '#e74c3c',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: confirmButtonText
    }).then(result => {
        if (result.isConfirmed) {
            form.action = href;
            form.submit();
        } else if (result.isDenied && isForceDelete) {
            form.action = hrefPermanent;
            form.submit();
        }
    });
}

function confirmDelete(href = "", text = "") {
    let form = document.getElementById('delete-form');
    submitFormSwal(form, `Data ${text} ${customMessages.alerts.delete_text}`, href);
};

function confirmDeletePermanent(href = "", text = "", href_permanent = "") {
    let form = document.getElementById('delete-form');
    submitFormSwal(form, `Data ${text} ${customMessages.alerts.delete_text}`, href, true, href_permanent);
};

// Pasang event listener untuk tombol
function alertConfirm(options) {
    const { selector, formId = null, isMessage = false, isDataHref = false, isPermanent = false } = options;
    const buttons = document.querySelectorAll(selector);
    if (!buttons.length) return;
    buttons.forEach(button => {
        button.addEventListener('click', e => {
            e.preventDefault();

            const targetFormId = button.dataset.idForm || formId;
            const form = targetFormId ? document.getElementById(targetFormId) : null;
            if (!form) return;

            const message = isMessage
                ? button.dataset.message
                : `Data ${button.dataset.text || ''} akan dihapus.`;

            const href = isDataHref
                ? button.dataset.href
                : (button.dataset.href || button.getAttribute('href') || form.getAttribute('action'));
            const hrefPermanent = isPermanent ? button.dataset.hrefpermanent : '';

            submitFormSwal(form, message, href, isPermanent, hrefPermanent);
        });
    });
}

// Contoh penggunaan untuk semua tombol
alertConfirm({ selector: '.tombol-konfirmasi', formId: 'confirm-form', isMessage: true });
alertConfirm({ selector: '.tombol-bulk-konfirmasi', formId: 'bulkconfirm-form', isMessage: true });
alertConfirm({ selector: '.tombol-bulk-konfirmasi-custom', isMessage: true });
alertConfirm({ selector: '.tombol-hapus', formId: 'delete-form' });
alertConfirm({ selector: '.tombol-permanent', formId: 'delete-form', isMessage: true });
alertConfirm({ selector: '.tombol-hapus-permanent', formId: 'delete-form', isPermanent: true });
alertConfirm({ selector: '.tombol-hapus-multiple', formId: 'bulkdestroy-form', isDataHref: true });
alertConfirm({ selector: '.tombol-hapus-multiple-permanent', formId: 'bulkdestroy-form', isDataHref: true, isPermanent: true });
/**
 * ============================================
 * GET COLOR FOR SCORE (JavaScript version)
 * ============================================
 */
function getSkorColorJS(skor) {
    return window.SkorJenjang?.[skor]?.color || '#e0e0e0';
}

function getSkorLabel(skor) {
    return window.SkorJenjang?.[skor]?.label || '-';
}

function getSkorLabelShort(skor, full = false) {
    const label = window.SkorJenjang?.[skor]?.label;
    if (!label) return '-';

    if (!full) {
        // hapus angka di depan jika ada
        return label.replace(/^\d+\s-\s/, '');
    }

    return label;
}

function textColorByBgJS(hex) {
    return '#000';
    // hex = hex.replace('#', '');
    // return (parseInt(hex.substr(0, 2), 16) * 0.299 +
    //     parseInt(hex.substr(2, 2), 16) * 0.587 +
    //     parseInt(hex.substr(4, 2), 16) * 0.114) > 186
    //     ? '#000'
    //     : '#fff';
}

function getSkorButtonClass(skor) {
    const classes = {
        0: 'danger'
        , 1: 'warning'
        , 2: 'warning'
        , 3: 'success'
        , 4: 'success'
    };
    return classes[skor] || 'secondary';
}

async function fetchJSON(url, options = {}) {
    try {
        // Ensure we request JSON
        options.headers = {
            ...options.headers,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest', // Mark as AJAX
        };

        const response = await fetch(url, options);
        const contentType = response.headers.get('content-type');

        // Check if response is JSON
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response received:', {
                url,
                status: response.status,
                contentType,
                body: text.substring(0, 500)
            });
            throw new Error('Server mengembalikan response yang tidak valid');
        }

        const data = await response.json();

        // Check if request was successful
        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
        }

        return { success: true, data };

    } catch (error) {
        console.error('Fetch error:', error);
        return { success: false, error: error.message };
    }
}


// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

function showModalById(id) {
    const el = document.getElementById(id);
    const modal = bootstrap.Modal.getOrCreateInstance(el, {
        backdrop: true,
        keyboard: true
    });
    modal.show();
    return modal;
}

function titleCaseWords(s) {
    return (s || '')
        .replace(/_/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .split(' ')
        .map(w => w ? (w[0].toUpperCase() + w.slice(1)) : '')
        .join(' ');
}

$(document).ready(function () {
    $('#datatable,.datatable').DataTable({});
});

document.querySelectorAll('.form-delete').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Yakin ingin menghapus?'
            , text: 'Data yang sudah dihapus tidak dapat dikembalikan.'
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#d33'
            , cancelButtonColor: '#6c757d'
            , confirmButtonText: 'Ya, hapus'
            , cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

function initSensitiveToggle() {

    document.addEventListener('click', function (e) {

        const button = e.target.closest('.toggle-sensitive');
        if (!button) return;

        const container = button.closest('td');
        const valueEl = container.querySelector('.sensitive-value');
        const icon = button.querySelector('i');

        const originalValue = valueEl.dataset.value;
        const type = valueEl.dataset.type || 'text';
        const isHidden = valueEl.dataset.hidden !== 'false';

        if (isHidden) {

            let formattedValue = originalValue;

            if (type === 'currency') {
                formattedValue = "Rp " +
                    new Intl.NumberFormat('id-ID').format(originalValue);
            }

            valueEl.textContent = formattedValue;
            valueEl.dataset.hidden = 'false';

            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');

        } else {

            valueEl.textContent = '••••••••';
            valueEl.dataset.hidden = 'true';

            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
}

function escapeHtml(str) {
    return String(str || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function appendChildHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
