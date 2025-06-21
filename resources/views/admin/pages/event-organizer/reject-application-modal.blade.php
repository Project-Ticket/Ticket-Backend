<form method="POST" action="{{ route('event-organizer.update-status', $organizer->uuid) }}" id="rejectApplicationForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Tolak Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <input type="hidden" name="status_type" value="application_status">
    <input type="hidden" name="status_value" value="rejected">
    <div class="modal-body row">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Anda akan menolak aplikasi untuk <strong>{{ $organizer->organization_name }}</strong>
        </div>
        <div class="mb-3">
            <label class="form-label">Alasan Penolakan Aplikasi <span class="text-danger">*</span></label>
            <textarea class="form-control" name="rejection_reason" rows="4"
                placeholder="Jelaskan alasan penolakan aplikasi..." required></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Aplikasi</button>
    </div>
</form>
<script>
    $('#rejectApplicationForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function (res) {
                $('#globalModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                    willClose: () => {
                        window.location.reload();
                    }
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                });
            },
            complete: function () {
                submitBtn.prop('disabled', false);
            }
        });
    });
</script>
