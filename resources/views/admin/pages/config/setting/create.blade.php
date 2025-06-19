<form action="{{ route('config.setting.store') }}" method="POST" id="setting-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Add New Setting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Key</label>
            <input type="text" name="key" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Value</label>
            <textarea name="value" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                <option value="string">String</option>
                <option value="integer">Integer</option>
                <option value="boolean">Boolean</option>
                <option value="json">JSON</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control"
                placeholder="Penjelasan singkat setting ini (opsional)">
        </div>

        <div class="mb-3">
            <label class="form-label">Group</label>
            <input type="text" name="group" class="form-control" value="general" required>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="is_public">
            <label class="form-check-label" for="is_public">Is Public</label>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
    $('#setting-form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                $('#globalModal').modal('hide');
                $('#setting-table').DataTable().ajax.reload(null, false);
                Swal.fire('Berhasil', response.message, 'success');
            },
            error: function (xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    });
</script>