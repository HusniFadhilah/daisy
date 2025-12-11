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

// Show Notifications
function showNotifications() {
    // Implementasi notifikasi dapat disesuaikan
    alert('Notifikasi:\n\n1. Penawaran asesmen baru (2)\n2. Split nilai terdeteksi (1)\n3. Pesan baru dari partner (3)\n4. Validasi selesai (1)\n5. Deadline mendekati (2)');
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
