@extends('layouts.template.app')

@section('content')
<style>
    .contact-page {
        max-width: 1180px;
        margin: 0 auto;
    }

    .contact-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
        gap: 24px;
        align-items: stretch;
    }

    .contact-panel {
        background: #fff;
        border: 1px solid #e7eaf0;
        border-radius: 8px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .contact-panel-main {
        display: flex;
        min-height: 280px;
    }

    .contact-accent {
        width: 12px;
        flex: 0 0 12px;
        background: #9f1239;
    }

    .contact-content {
        padding: 36px;
        flex: 1;
    }

    .contact-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #9f1239;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 14px;
    }

    .contact-address {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        margin-top: 28px;
        padding: 22px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .contact-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e5e7eb;
        color: #9f1239;
        flex: 0 0 42px;
        font-size: 20px;
    }

    .contact-actions {
        display: grid;
        gap: 14px;
        height: 100%;
    }

    .contact-action {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 22px;
        color: #111827;
        text-decoration: none;
        background: #fff;
        border: 1px solid #e7eaf0;
        border-radius: 8px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    .contact-action:hover {
        color: #111827;
        border-color: #cbd5e1;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .08);
        transform: translateY(-1px);
    }

    .contact-action strong {
        display: block;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .contact-action span {
        display: block;
        color: #2563eb;
        font-weight: 600;
        word-break: break-word;
    }

    .contact-note {
        margin-top: 18px;
        padding: 16px 18px;
        border-radius: 8px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #7c2d12;
        font-size: 14px;
    }

    @media (max-width: 991.98px) {
        .contact-hero {
            grid-template-columns: 1fr;
        }

        .contact-content {
            padding: 28px;
        }
    }

</style>

<div class="container-fluid">
    <div class="contact-page">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kontak Sekretariat</li>
            </ol>
        </nav>

        <div class="d-flex align-items-end justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1">Kontak LAMDEPILAR</h1>
                <p class="text-muted mb-0">Informasi resmi Sekretariat LAMDEPILAR.</p>
            </div>
        </div>

        <div class="contact-hero">
            <section class="contact-panel">
                <div class="contact-panel-main">
                    <div class="contact-accent"></div>
                    <div class="contact-content">
                        <div class="contact-eyebrow">
                            <i class="bi bi-building"></i>
                            Kantor Sekretariat
                        </div>

                        <h2 class="h4 mb-3">Sekretariat LAMDEPILAR</h2>
                        <p class="text-muted mb-0">
                            Hubungi sekretariat untuk bantuan akses, informasi layanan, atau koordinasi proses akreditasi.
                        </p>

                        <div class="contact-address">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <div class="fw-semibold mb-1">Alamat</div>
                                <div>Jl. Tambak no.21. Menteng. Jakarta Pusat - 10320. INDONESIA.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="contact-actions">
                <a href="mailto:sekretariat@lamdepilar.or.id" class="contact-action">
                    <div class="contact-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div>
                        <strong>Email</strong>
                        <span>sekretariat@lamdepilar.or.id</span>
                    </div>
                </a>

                <a href="https://lamdepilar.or.id/" target="_blank" rel="noopener" class="contact-action">
                    <div class="contact-icon">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <div>
                        <strong>Website</strong>
                        <span>https://lamdepilar.or.id/</span>
                    </div>
                </a>
            </aside>
        </div>
    </div>
</div>
@endsection
