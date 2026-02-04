{{-- resources/views/de/penerimaan-permohonan/_components/stats-cards.blade.php --}}
{{-- Stat Card --}}
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
    <div class="col mb-3">
        <x-stat-card title="Total Permohonan Akreditasi" :value="$stats['total']" description="Permohonan Akreditasi yang telah diterima" icon="file-earmark-check" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
    </div>

    <div class="col mb-3">
        <x-stat-card title="Penerimaan Permohonan Akreditasi Belum Dikirim" :value="$stats['belum_terkirim']" description="Perlu segera mengirim penerimaan permohonan akreditasi" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
    </div>

    <div class="col mb-3">
        <x-stat-card title="Penerimaan Permohonan Akreditasi Telah Dikirim" :value="$stats['terkirim']" description="Penerimaan permohonan akreditasi telah dikirim ke PS" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
    </div>
</div>
