<form action="{{ route('config.permission.store') }}" method="POST" id="permission-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Add Permission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" placeholder="Name Permission" class="form-control">
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
    $('#permission-form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                $('#globalModal').modal('hide');
                $('#permission-table').DataTable().ajax.reload(null, false);
                Swal.fire('Berhasil', response.message, 'success');
            },
            error: function (xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    });
</script>