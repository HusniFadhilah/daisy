<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (optional, jika diperlukan) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Custom JavaScript -->
<script>
    // Toggle Sidebar for Mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
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
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nav-link').forEach(item => {
            item.addEventListener('click', function(e) {
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
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.querySelector('.menu-toggle');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !menuToggle.contains(event.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });

</script>

<!-- Additional Scripts -->
@stack('scripts')

</body>
</html>
