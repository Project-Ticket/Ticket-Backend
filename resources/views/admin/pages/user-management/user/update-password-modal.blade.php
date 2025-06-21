<form action="{{ route('user-management.user.update-password') }}" method="POST" id="password-form">
    @csrf
    <input type="hidden" name="user_id" value="{{ $user->id }}">

    <div class="modal-header">
        <h5 class="modal-title">Ubah Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
    </div>

    <div class="modal-body row">
        <div class="mb-3 col-12">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control" required minlength="6"
                placeholder="Minimal 6 karakter">
        </div>

        <div class="mb-3 col-12">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control" required
                placeholder="Ulangi password">
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>

<script>
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#globalModal').modal('hide');
                $('#user-table').DataTable().ajax.reload(null, false);
                Swal.fire('Berhasil', response.message, 'success');
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    });
</script>
