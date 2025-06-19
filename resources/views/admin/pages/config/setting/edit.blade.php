<form action="{{ route('config.setting.update', $setting->id) }}" method="POST" id="setting-form">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title">Edit Setting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Key</label>
            <input type="text" name="key" value="{{ old('key', $setting->key) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Value</label>
            <textarea name="value" class="form-control">{{ old('value', $setting->value) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                @foreach (['string', 'integer', 'boolean', 'json'] as $type)
                    <option value="{{ $type }}" @selected(old('type', $setting->type) === $type)>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Group</label>
            <input type="text" name="group" value="{{ old('group', $setting->group) }}" class="form-control" required>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="is_public"
                @checked(old('is_public', $setting->is_public))>
            <label class="form-check-label" for="is_public">Is Public</label>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>

<script>
    $('#setting-form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST', // tetap gunakan POST, Laravel akan override via _method PUT
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