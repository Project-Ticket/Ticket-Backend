<form action="{{ route('coupon.store') }}" method="POST" id="coupon-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Add New Coupon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body row">
        <div class="mb-3 col-md-6">
            <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text" name="code" class="form-control" required>
                <button class="btn btn-outline-primary" type="button" id="button-addon2">Generate</button>
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Coupon Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Coupon Type <span class="text-danger">*</span></label>
            <select name="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="percentage">Percentage</option>
                <option value="fixed_amount">Fixed Amount</option>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Value <span class="text-danger">*</span></label>
            <input type="number" name="value" class="form-control" required min="0" step="0.01">
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Minimum Amount</label>
            <input type="number" name="minimum_amount" class="form-control" min="0" step="0.01">
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Maximum Discount</label>
            <input type="number" name="maximum_discount" class="form-control" min="0" step="0.01">
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Organizer</label>
            <select name="organizer_id" class="form-select">
                <option value="">-- Select Organizer --</option>
                @foreach ($organizers as $organizer)
                    <option value="{{ $organizer->id }}">{{ $organizer->organization_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Applicable To <span class="text-danger">*</span></label>
            <select name="applicable_to" class="form-select" required>
                <option value="tickets">Tickets</option>
                <option value="merchandise">Merchandise</option>
                <option value="both">Both</option>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Usage Limit</label>
            <input type="number" name="usage_limit" class="form-control" min="0">
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Usage Limit Per User</label>
            <input type="number" name="usage_limit_per_user" class="form-control" min="0">
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Valid From <span class="text-danger">*</span></label>
            <input type="datetime-local" name="valid_from" class="form-control" required>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Valid Until <span class="text-danger">*</span></label>
            <input type="datetime-local" name="valid_until" class="form-control" required>
        </div>

        <div class="mb-3 col-12">
            <label class="form-label" for="multicol-applicable">Applicable Events</label>
            <select id="multicol-applicable" name="applicable_events[]" class="select2 form-select" multiple>
                <option value="">-- Select Events --</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3 col-12">
            <label class="form-label" for="multicol-merchandise">Applicable Merchandise</label>
            <select id="multicol-merchandise" name="applicable_merchandise[]" class="select2 form-select" multiple>
                <option value="">-- Select Merchandise --</option>
                @foreach ($merchandises as $merchandise)
                    <option value="{{ $merchandise->id }}">{{ $merchandise->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3 col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label">Active Status</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Coupon</button>
    </div>
</form>

<script src="{{ url('/admin') }}/js/form-layouts.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/select2/select2.js"></script>

<script>
    $(document).ready(function () {

        $('#button-addon2').on('click', function () {
            $.ajax({
                url: '{{ route('coupon.generate') }}',
                method: 'GET',
                success: function (response) {
                    $('input[name="code"]').val(response.code);
                },
                error: function () {
                    Swal.fire('Error', 'Gagal generate kode kupon', 'error');
                }
            });
        });

        $('#coupon-form').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function (response) {
                    $('#globalModal').modal('hide');
                    $('#coupons-table').DataTable().ajax.reload(null, false);
                    Swal.fire('Success', response.message, 'success');
                },
                error: function (xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'An error occurred';

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorText = '';

                        $.each(errors, function (field, messages) {
                            errorText += messages.join('\n') + '\n';
                        });

                        errorMessage = errorText || errorMessage;
                    }

                    Swal.fire('Failed', errorMessage, 'error');
                }
            });
        });
    });
</script>