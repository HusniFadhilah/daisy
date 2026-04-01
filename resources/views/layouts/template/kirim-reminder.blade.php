<form id="formReminderAssignment" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="pesan_reminder" id="pesanReminderAssignment">
</form>

<script>
    function kirimReminderAssignment(assignmentId, title, defaultMessage) {
        Swal.fire({
            title: title
            , input: 'textarea'
            , inputLabel: 'Pesan Pengingat'
            , inputValue: defaultMessage
            , inputAttributes: {
                'aria-label': 'Pesan Pengingat'
                , 'rows': 8
            }
            , didOpen: () => {
                const textarea = Swal.getInput();
                textarea.style.height = '250px';
            }
            , showCancelButton: true
            , confirmButtonText: 'Kirim'
            , cancelButtonText: 'Batal'
            , inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Pesan pengingat wajib diisi';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('formReminderAssignment');
                const input = document.getElementById('pesanReminderAssignment');

                input.value = result.value;
                form.action = "{{ route('de.kirim-reminder-assignment',$assignment->id) }}";
                form.submit();
            }
        });
    }

</script>
