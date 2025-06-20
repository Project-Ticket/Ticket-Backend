<form action="{{ route('event-organizer.store') }}" method="POST" id="eo-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Add New Event Organizer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body row">
        {{-- USER --}}
        <div class="mb-3 col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3 col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3 col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        {{-- EVENT ORGANIZER --}}
        <div class="mb-3 col-md-6">
            <label class="form-label">Organization Name</label>
            <input type="text" name="organization_name" class="form-control" required>
        </div>

        <div class="mb-3 col-md-12">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2" required></textarea>
        </div>

        <div class="mb-3 col-md-4">
            <label class="form-label">Province</label>
            <input type="text" name="province" class="form-control" required>
        </div>
        <div class="mb-3 col-md-4">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" required>
        </div>
        <div class="mb-3 col-md-4">
            <label class="form-label">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" required>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Contact Person</label>
            <input type="text" name="contact_person" class="form-control" required>
        </div>
        <div class="mb-3 col-md-6">
            <label class="form-label">Contact Phone</label>
            <input type="text" name="contact_phone" class="form-control" required>
        </div>
        <div class="mb-3 col-md-12">
            <label class="form-label">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" required>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>


<script>
    $(document).ready(function() {
        $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function(data) {
            let options = '<option value="">-- Pilih Provinsi --</option>';
            $.each(data, function(i, province) {
                options +=
                    `<option value="${province.name}" data-id="${province.id}">${province.name}</option>`;
            });
            $('#province-select').html(options);
        });

        $('#province-select').on('change', function() {
            let selected = $(this).find(':selected');
            let provinceId = selected.data('id');

            if (provinceId) {
                $('#city-wrapper').removeClass('d-none');
                $('#city-select').html('<option value="">Memuat...</option>');

                $.getJSON(
                    `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`,
                    function(data) {
                        let cityOptions = '<option value="">-- Pilih Kota/Kabupaten --</option>';
                        $.each(data, function(i, city) {
                            cityOptions +=
                                `<option value="${city.name}">${city.name}</option>`;
                        });
                        $('#city-select').html(cityOptions);
                    });
            } else {
                $('#city-wrapper').addClass('d-none');
                $('#city-select').html('');
            }
        });
    });

    $('#eo-form').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#globalModal').modal('hide');
                $('#eo-table').DataTable().ajax.reload(null, false);
                Swal.fire('Berhasil', response.message, 'success');
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    });
</script>
