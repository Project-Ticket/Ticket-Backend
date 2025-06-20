<div class="modal-header">
    <h5 class="modal-title">Show user</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-4 text-center">
            @if ($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" class="img-fluid rounded-circle mb-3" width="150"
                    height="150" alt="Avatar">
            @else
                <div class="bg-secondary text-white rounded-circle d-inline-flex justify-content-center align-items-center mb-3"
                    style="width: 150px; height: 150px; font-size: 48px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <h5>{{ $user->name }}</h5>
            <p class="text-muted">{{ $user->email }}</p>
        </div>
        <div class="col-md-8">
            <table class="table table-sm table-bordered">
                <tr>
                    <th>Phone</th>
                    <td>{{ $user->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td>{{ ucfirst($user->gender ?? '-') }}</td>
                </tr>
                <tr>
                    <th>Birth Date</th>
                    <td>{{ $user->birth_date ? date('d M Y', strtotime($user->birth_date)) : '-' }}</td>
                </tr>
                <tr>
                    <th>Province</th>
                    <td>{{ $user->province ?? '-' }}</td>
                </tr>
                <tr>
                    <th>City</th>
                    <td>{{ $user->city ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Postal Code</th>
                    <td>{{ $user->postal_code ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{!! $user->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}
                    </td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $user->address ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" data-bs-dismiss="modal" class="btn btn-primary">Close</button>
</div>