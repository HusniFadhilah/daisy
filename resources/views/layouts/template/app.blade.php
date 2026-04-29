@include('layouts.template.header')
@include('layouts.template.navbar')

@include('layouts.template.sidebar')

<!-- Main Content -->
<main id="mainContent" class="main-content">
    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-permanent alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-permanent alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        {!! session('error') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-permanent alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {!! session('warning') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-permanent alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        {!! session('info') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Page Content -->
    @yield('content')
</main>
@include('layouts.template.footer')
