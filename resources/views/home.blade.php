<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAISY - DEPILAR Accreditation Information System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Welcome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/guest.css') }}">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="welcome-navbar" id="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                <div class="navbar-logo">
                    <img src="{{ asset('assets/images/logo-square.png') }}" alt="DAISY Logo">
                </div>
                <div class="d-block">
                    <span class="navbar-title">DAISY</span><br>
                    <small>DEPILAR Accreditation Information System</small>
                </div>
            </a>

            <div class="navbar-menu">
                <ul class="navbar-links">
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#fitur">Fitur</a></li>
                    <li><a href="#cara-kerja">Cara Kerja</a></li>
                    <li><a href="#testimoni">Testimoni</a></li>
                </ul>

                <div class="navbar-buttons">
                    @if(auth()->check())
                    <!-- User sudah login -->
                    <a href="{{ route('dashboard') }}" class="btn-register">Dashboard</a>
                    @else
                    <!-- User belum login -->
                    <a href="{{ route('login') }}" class="btn-login">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-register">Daftar</a>
                    @endif
                </div>


                <button class="mobile-toggle" onclick="toggleMobileMenu()">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="beranda">
        <div class="hero-container">
            <div class="hero-content fade-in-up">
                <div class="hero-text">
                    <h1>
                        Sistem Informasi Akreditasi
                        <span class="highlight">Terpercaya</span> untuk Perguruan Tinggi
                    </h1>
                    <p>
                        DAISY (DEPILAR Accreditation Information System) adalah platform digital yang memudahkan
                        proses akreditasi program studi dengan sistem yang terintegrasi, aman, dan efisien.
                    </p>

                    <div class="hero-buttons">
                        <a href="{{ route('register') }}" class="btn-hero-primary">
                            <span>Mulai Sekarang</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="#fitur" class="btn-hero-secondary">
                            <i class="bi bi-play-circle"></i>
                            <span>Pelajari Lebih Lanjut</span>
                        </a>
                    </div>

                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Program Studi</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">150+</span>
                            <span class="stat-label">Universitas</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">98%</span>
                            <span class="stat-label">Tingkat Kepuasan</span>
                        </div>
                    </div>
                </div>

                <div class="hero-image">
                    <img src="{{ asset('assets/images/dashboard-mockup.png') }}" alt="DAISY Dashboard" class="hero-mockup">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="fitur">
        <div class="section-header scroll-reveal">
            <span class="section-label">Fitur Unggulan</span>
            <h2 class="section-title">Mengapa Memilih DAISY?</h2>
            <p class="section-description">
                Sistem komprehensif yang dirancang khusus untuk memudahkan proses akreditasi program studi Anda
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card scroll-reveal">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Dashboard Lengkap</h3>
                <p class="feature-description">
                    Monitor semua proses akreditasi dalam satu dashboard yang intuitif dan mudah dipahami
                </p>
            </div>

            <div class="feature-card scroll-reveal">
                <div class="feature-icon">📝</div>
                <h3 class="feature-title">Asesmen Terintegrasi</h3>
                <p class="feature-description">
                    Kelola asesmen kecukupan (AK) dan asesmen lapangan (AL) dengan sistem yang terintegrasi
                </p>
            </div>

            <div class="feature-card scroll-reveal">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Keamanan Terjamin</h3>
                <p class="feature-description">
                    Data terenkripsi dengan standar keamanan tinggi dan backup otomatis setiap hari
                </p>
            </div>

            <div class="feature-card scroll-reveal">
                <div class="feature-icon">👥</div>
                <h3 class="feature-title">Kolaborasi Tim</h3>
                <p class="feature-description">
                    Koordinasi mudah antara asesor, partner, dan institusi dalam satu platform
                </p>
            </div>

            <div class="feature-card scroll-reveal">
                <div class="feature-icon">📈</div>
                <h3 class="feature-title">Statistik Real-time</h3>
                <p class="feature-description">
                    Lihat progress dan statistik akreditasi secara real-time dengan visualisasi yang informatif
                </p>
            </div>

            <div class="feature-card scroll-reveal">
                <div class="feature-icon">📱</div>
                <h3 class="feature-title">Akses Mobile</h3>
                <p class="feature-description">
                    Akses sistem kapan saja, di mana saja melalui desktop, tablet, atau smartphone
                </p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="cara-kerja">
        <div class="section-header scroll-reveal">
            <span class="section-label">Cara Kerja</span>
            <h2 class="section-title">Mudah & Cepat Digunakan</h2>
            <p class="section-description">
                Mulai perjalanan akreditasi Anda hanya dalam 4 langkah sederhana
            </p>
        </div>

        <div class="steps-container">
            <div class="step-card scroll-reveal">
                <div class="step-number">1</div>
                <div class="step-icon">📝</div>
                <h3 class="step-title">Daftar Akun</h3>
                <p class="step-description">
                    Buat akun dengan mengisi data institusi dan program studi Anda
                </p>
            </div>

            <div class="step-card scroll-reveal">
                <div class="step-number">2</div>
                <div class="step-icon">📋</div>
                <h3 class="step-title">Upload Dokumen</h3>
                <p class="step-description">
                    Upload dokumen yang diperlukan untuk proses akreditasi
                </p>
            </div>

            <div class="step-card scroll-reveal">
                <div class="step-number">3</div>
                <div class="step-icon">👨‍🏫</div>
                <h3 class="step-title">Asesmen</h3>
                <p class="step-description">
                    Tim asesor akan melakukan penilaian dan visitasi lapangan
                </p>
            </div>

            <div class="step-card scroll-reveal">
                <div class="step-number">4</div>
                <div class="step-icon">🎓</div>
                <h3 class="step-title">Hasil Akreditasi</h3>
                <p class="step-description">
                    Dapatkan hasil dan sertifikat akreditasi program studi Anda
                </p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section" id="testimoni">
        <div class="section-header scroll-reveal">
            <span class="section-label">Testimoni</span>
            <h2 class="section-title">Apa Kata Mereka?</h2>
            <p class="section-description">
                Pengalaman nyata dari berbagai institusi yang telah menggunakan DAISY
            </p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card scroll-reveal">
                <div class="quote-icon">"</div>
                <p class="testimonial-text">
                    DAISY sangat membantu proses akreditasi program studi kami. Sistem yang terintegrasi
                    membuat semua proses lebih efisien dan terorganisir dengan baik.
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">DM</div>
                    <div class="author-info">
                        <h4>Dr. Eng. Maryono, ST., MT</h4>
                        <p>Asesor LAMDEPILAR</p>
                        <div class="rating">★★★★★</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card scroll-reveal">
                <div class="quote-icon">"</div>
                <p class="testimonial-text">
                    Interface yang user-friendly dan fitur lengkap. Monitoring progress akreditasi
                    menjadi lebih mudah dan transparan untuk semua pihak yang terlibat.
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">SP</div>
                    <div class="author-info">
                        <h4>Dr. Eng. Maryono, ST., MT</h4>
                        <p>Asesor LAMDEPILAR</p>
                        <div class="rating">★★★★★</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card scroll-reveal">
                <div class="quote-icon">"</div>
                <p class="testimonial-text">
                    Dokumentasi dan pelaporan menjadi jauh lebih terstruktur. Tim support juga
                    sangat responsif membantu setiap kendala yang kami hadapi.
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">AB</div>
                    <div class="author-info">
                        <h4>Dr. Eng. Maryono, ST., MT</h4>
                        <p>Asesor LAMDEPILAR</p>
                        <div class="rating">★★★★★</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-container">
            <h2>Siap Memulai Perjalanan Akreditasi Anda?</h2>
            <p>
                Bergabunglah dengan ratusan institusi yang telah mempercayai DAISY
                untuk proses akreditasi program studi mereka
            </p>
            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn-hero-primary">
                    <span>Daftar Sekarang</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
                <a href="{{ route('login') }}" class="btn-hero-secondary">
                    <span>Login</span>
                    <i class="bi bi-box-arrow-in-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>DAISY</h3>
                    <p>
                        DEPILAR Accreditation Information System - Platform digital
                        untuk memudahkan proses akreditasi program studi perguruan tinggi.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <div class="footer-column">
                    <h4>Produk</h4>
                    <ul class="footer-links">
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#cara-kerja">Cara Kerja</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Perusahaan</h4>
                    <ul class="footer-links">
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Karir</a></li>
                        <li><a href="#">Kontak</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Bantuan</h4>
                    <ul class="footer-links">
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Panduan</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} DAISY - DEPILAR Accreditation Information System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            alert('Mobile menu akan ditampilkan dengan dropdown');
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                        , block: 'start'
                    });
                }
            });
        });

        // Scroll reveal animation
        const observerOptions = {
            threshold: 0.1
            , rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-reveal').forEach(element => {
            observer.observe(element);
        });

        // Prevent navbar overlap on page load with hash
        window.addEventListener('load', function() {
            if (window.location.hash) {
                setTimeout(function() {
                    const target = document.querySelector(window.location.hash);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                            , block: 'start'
                        });
                    }
                }, 100);
            }
        });

    </script>
</body>
</html>
