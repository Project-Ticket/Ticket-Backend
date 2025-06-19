<form action="{{ route('user.store') }}" method="POST" id="user-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body row">
        <div class="mb-3 col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">-- Choose --</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Birth Date</label>
            <input type="date" name="birth_date" class="form-control">
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Province</label>
            <select name="province" id="province-select" class="form-select" required>
                <option value="">-- Pilih Provinsi --</option>
            </select>
        </div>

        <div class="mb-3 col-md-6 d-none" id="city-wrapper">
            <label class="form-label">City</label>
            <select name="city" id="city-select" class="form-select" required>
                <option value="">-- Pilih Kota/Kabupaten --</option>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control">
        </div>

        <div class="mb-3 col-12">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        // Load all provinces
        $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function (data) {
            let options = '<option value="">-- Pilih Provinsi --</option>';
            $.each(data, function (i, province) {
                options += `<option value="${province.name}" data-id="${province.id}">${province.name}</option>`;
            });
            $('#province-select').html(options);
        });

        // On change province
        $('#province-select').on('change', function () {
            let selected = $(this).find(':selected');
            let provinceId = selected.data('id');

            if (provinceId) {
                $('#city-wrapper').removeClass('d-none');
                $('#city-select').html('<option value="">Memuat...</option>');

                $.getJSON(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`, function (data) {
                    let cityOptions = '<option value="">-- Pilih Kota/Kabupaten --</option>';
                    $.each(data, function (i, city) {
                        cityOptions += `<option value="${city.name}">${city.name}</option>`;
                    });
                    $('#city-select').html(cityOptions);
                });
            } else {
                $('#city-wrapper').addClass('d-none');
                $('#city-select').html('');
            }
        });
    });
    $('#user-form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                $('#globalModal').modal('hide');
                $('#user-table').DataTable().ajax.reload(null, false);
                Swal.fire('Berhasil', response.message, 'success');
            },
            error: function (xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    });
</script>