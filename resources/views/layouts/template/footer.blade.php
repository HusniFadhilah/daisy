<form id="delete-form" action="" method="POST" class="d-none">
    @csrf
    @method('delete')
</form>
<form id="confirm-form" action="" method="POST" class="d-none">
    @csrf
</form>
<form id="process-form" action="" method="POST" class="d-none">
    @csrf
</form>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
/**
 * Initialize Select2 on all plain <select> elements inside a given container.
 * Skips elements already initialized or those with a custom [data-no-select2] attribute.
 * Called on page load and must be re-called after AJAX content injection.
 */
function initSelect2All(container) {
    $(container || document).find('select:not(.select2-hidden-accessible):not([data-no-select2]):not(.swal2-select)').each(function () {
        var $el     = $(this);
        if ($el.closest('.swal2-container').length) return;

        var id      = $el.attr('id');
        var $label  = id ? $('label[for="' + id + '"]') : $();
        var firstEmpty  = $el.find('option[value=""]').first().text().trim();
        var labelText   = $label.length ? $label.text().replace(/[\s*]+$/, '').trim() : '';
        var placeholder = firstEmpty || labelText || 'Pilih...';
        var $modal  = $el.closest('.modal');

        var opts = {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: placeholder,
            allowClear: $el.find('option[value=""]').length > 0,
        };

        if ($modal.length) {
            opts.dropdownParent = $modal;
        }

        $el.select2(opts);
    });
}

$(document).ready(function () {
    initSelect2All(document);
});
</script>
<!-- Custom JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Inject Global Skor Mapping -->
@php
$exportSkorMap = \App\Models\JenjangPenilaian::exportSkorMap(true)
@endphp
<script>
    window.SkorJenjang = @json($exportSkorMap);

</script>
<script src="{{ asset('assets/js/utility.js') }}"></script>

<!-- Additional Scripts -->
@stack('scripts')

</body>
</html>
