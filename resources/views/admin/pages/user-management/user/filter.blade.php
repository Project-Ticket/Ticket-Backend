<div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="globalOffcanvasTitle">{{ $title ?? 'Filter User' }}</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
</div>

<div class="offcanvas-body flex-grow-1">
    <form id="user-filter-form">
        @csrf

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select mb-2">
                <option value="">-- Pilih Role --</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{$role->name}}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti ti-user"></i></span>
                <input type="text" class="form-control" name="name" id="name" placeholder="John Doe">
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                <input type="email" class="form-control" name="email" id="email" placeholder="email@example.com">
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Phone</label>
            <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti ti-phone"></i></span>
                <input type="number" class="form-control" name="phone" id="phone" placeholder="08123456789">
            </div>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" id="status">
                <option value="">-- Select Status --</option>
                @foreach($userStatuses as $id => $label)
                    <option value="{{ $id }}">{{ ucfirst(strtolower($label)) }}</option>
                @endforeach
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-outline-danger" id="reset-filter">Reset</button>
            <div>
                <button type="submit" class="btn btn-primary me-2">Apply</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </div>
    </form>
</div>

<script>
    $('#reset-filter').on('click', function () {
        console.log('Reset filter clicked');

        $('#user-filter-form')[0].reset(); // Reset form input
        userTable.ajax.reload(); // Reload table tanpa filter
    });

</script>