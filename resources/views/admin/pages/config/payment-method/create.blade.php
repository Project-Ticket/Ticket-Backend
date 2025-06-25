<!-- resources/views/admin/pages/config/payment_method/create.blade.php -->

<!-- Modal for Add/Edit Payment Method -->
<form action="{{ route('config.payment-method.store') }}" method="POST" id="payment-method-form">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Add Payment Method</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" placeholder="Code" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" placeholder="Name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select class="form-select" name="type" required>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="credit_card">Credit Card</option>
                <option value="ewallet">E-Wallet</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Fee Percentage</label>
            <input type="number" name="fee_percentage" placeholder="Fee Percentage" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fixed Fee</label>
            <input type="number" name="fee_fixed" placeholder="Fixed Fee" class="form-control" required>
        </div>
        <!-- Additional fields can be added here -->
        <div class="mb-3">
            <label class="form-label">Active</label>
            <input type="checkbox" name="is_active" checked>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<script>
    $('#payment-method-form').on('submit', function (e) {
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