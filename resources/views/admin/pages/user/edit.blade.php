<form action="{{ route('user.update', $user->uuid) }}" method="POST" id="user-edit-form">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body row">
        <div class="mb-3 col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
        <div class="mb-3 col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <div class="mb-3 col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
        </div>
        <div class="mb-3 col-md-6">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">-- Choose --</option>
                <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="mb-3 col-md-6">
            <label class="form-label">Birth Date</label>
            <input type="date" name="birth_date" class="form-control" value="{{ $user->birth_date }}">
        </div>

        {{-- Province & City --}}
        <div class="mb-3 col-md-6">
            <label class="form-label">Province</label>
            <select name="province" id="province-edit-select" class="form-select" required>
                <option value="">-- Pilih Provinsi --</option>
            </select>
        </div>

        <div class="mb-3 col-md-6" id="city-edit-wrapper" style="display: none">
            <label class="form-label">City</label>
            <select name="city" id="city-edit-select" class="form-select" required>
                <option value="">-- Pilih Kota/Kabupaten --</option>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="{{ $user->postal_code }}">
        </div>
        <div class="mb-3 col-12">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control">{{ $user->address }}</textarea>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        const currentProvince = @json($user->province);
        const currentCity = @json($user->city);

        $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function (data) {
            let options = '<option value="">-- Pilih Provinsi --</option>';
            data.forEach(function (item) {
                options += `<option value="${item.name}" data-id="${item.id}" ${item.name === currentProvince ? 'selected' : ''}>${item.name}</option>`;
            });
            $('#province-edit-select').html(options).trigger('change');
        });

        $('#province-edit-select').on('change', function () {
            const selected = $(this).find(':selected');
            const provinceId = selected.data('id');

            if (provinceId) {
                $('#city-edit-wrapper').show();
                $('#city-edit-select').html('<option value="">Memuat...</option>');

                $.getJSON(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`, function (data) {
                    let cityOptions = '<option value="">-- Pilih Kota/Kabupaten --</option>';
                    data.forEach(function (city) {
                        const selected = city.name === currentCity ? 'selected' : '';
                        cityOptions += `<option value="${city.name}" ${selected}>${city.name}</option>`;
                    });
                    $('#city-edit-select').html(cityOptions);
                });
            } else {
                $('#city-edit-wrapper').hide();
            }
        });

        $('#user-edit-form').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function (res) {
                    $('#globalModal').modal('hide');
                    $('#user-table').DataTable().ajax.reload(null, false);
                    Swal.fire('Berhasil', res.message, 'success');
                },
                error: function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });
    });
</script>