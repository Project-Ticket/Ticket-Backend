<form action="{{ route('config.payment-method.update', $paymentMethod->id) }}" method="POST"
    id="edit-payment-method-form">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title">Edit Payment Method</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" value="{{ $paymentMethod->code }}" placeholder="Code" class="form-control"
                required readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="{{ $paymentMethod->name }}" placeholder="Name" class="form-control"
                required>
        </div>
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select class="form-select" name="type" required>
                <option value="bank_transfer" {{ $paymentMethod->type == 'bank_transfer' ? 'selected' : '' }}>Bank
                    Transfer</option>
                <option value="credit_card" {{ $paymentMethod->type == 'credit_card' ? 'selected' : '' }}>Credit Card
                </option>
                <option value="ewallet" {{ $paymentMethod->type == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Fee Percentage</label>
            <input type="number" name="fee_percentage" value="{{ $paymentMethod->fee_percentage }}"
                placeholder="Fee Percentage" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fixed Fee</label>
            <input type="number" name="fee_fixed" value="{{ $paymentMethod->fee_fixed }}" placeholder="Fixed Fee"
                class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Active</label>
            <input type="checkbox" name="is_active" {{ $paymentMethod->is_active ? 'checked' : '' }}>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>

<script>
    $('#edit-payment-method-form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                $('#globalModal').modal('hide');
                $('#payment-method-table').DataTable().ajax.reload(null, false);
                Swal.fire('Success', response.message, 'success');
            },
            error: function (xhr) {
                Swal.fire('Fail', xhr.responseJSON?.message || 'An error occurred.', 'error');
            }
        });
    });
</script>