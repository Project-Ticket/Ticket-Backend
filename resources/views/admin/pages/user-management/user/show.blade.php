@php
    use App\Services\Status;
@endphp

<div class="modal-header">
    <h5 class="modal-title">
        <i class="fas fa-user-edit me-2"></i>User Profile & Management
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row">
        <!-- Left Panel -->
        <div class="col-md-4 text-center">
            @if ($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" class="img-fluid rounded-circle mb-3" alt="Avatar"
                    style="width: 120px; height: 120px;">
            @else
                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                    style="width: 120px; height: 120px; font-size: 2rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            @if ($user->email_verified_at)
                <span class="badge bg-success mb-2">
                    <i class="fas fa-check-circle me-1"></i>Verified
                </span>
            @endif

            <h4 class="mb-1">{{ $user->name }}</h4>
            <p class="text-muted mb-3">{{ $user->email }}</p>

            <div class="mb-3">
                {!! Status::getBadgeHtml('userStatus', $user->status) !!}
            </div>

            <div class="row text-center">
                <div class="col">
                    <div>
                        <h6>{{ $user->events_count ?? 0 }}</h6>
                        <small class="text-muted">Events</small>
                    </div>
                </div>
                <div class="col">
                    <div>
                        <h6>{{ $user->tickets_count ?? 0 }}</h6>
                        <small class="text-muted">Tickets</small>
                    </div>
                </div>
                <div class="col">
                    <div>
                        <h6>{{ number_format($user->total_spent ?? 0) }}</h6>
                        <small class="text-muted">Spent</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="col-md-8">
            <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Personal Information</h6>

            <ul class="list-group mb-4">
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-phone me-2 text-primary"></i>Phone</span>
                    <span>{{ $user->phone ?? '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-venus-mars me-2 text-danger"></i>Gender</span>
                    <span>{{ $user->gender ? ucfirst($user->gender) : '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-birthday-cake me-2 text-warning"></i>Birth Date</span>
                    <span>{{ $user->birth_date ? $user->birth_date->format('d M Y') : '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-map-marker-alt me-2 text-info"></i>Location</span>
                    <span>{{ $user->city ? $user->city . ', ' . $user->province : '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-mail-bulk me-2 text-secondary"></i>Postal Code</span>
                    <span>{{ $user->postal_code ?? '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-home me-2 text-success"></i>Address</span>
                    <span>{{ $user->address ?? '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fas fa-calendar-plus me-2 text-primary"></i>Member Since</span>
                    <span>{{ $user->created_at->format('d M Y, H:i') }}</span>
                </li>
                @if ($user->email_verified_at)
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-shield-alt me-2 text-success"></i>Email Verified</span>
                        <span>{{ $user->email_verified_at->format('d M Y, H:i') }}</span>
                    </li>
                @endif
            </ul>

            <!-- Status Management -->
            <h6 class="mb-2"><i class="fas fa-cogs me-2"></i>Status Management</h6>
            <p class="text-muted small mb-3">Change user status to manage account access and permissions.</p>

            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach (Status::getAll('userStatus') as $statusId => $statusName)
                    <button type="button"
                        class="btn btn-outline-secondary {{ $user->status == $statusId ? 'disabled' : '' }}"
                        onclick="changeUserStatus({{ $user->id }}, {{ $statusId }}, '{{ $statusName }}')">
                        <i
                            class="fas fa-{{ $statusId == 1 ? 'check-circle' : ($statusId == 2 ? 'pause-circle' : 'ban') }} me-1"></i>
                        {{ ucfirst(strtolower($statusName)) }}
                    </button>
                @endforeach
            </div>

            <!-- Status History -->
            <h6 class="mb-2"><i class="fas fa-history me-2"></i>Status History</h6>
            <div class="d-flex align-items-center">
                <div class="rounded-circle me-3"
                    style="width: 12px; height: 12px;
                    background: {{ Status::getFormatted('userStatus', $user->status)['class'] == 'badge bg-success' ? '#4facfe' : ($user->status == 2 ? '#a8a8a8' : '#ff6b6b') }};">
                </div>
                <div>
                    <strong>{{ Status::getName('userStatus', $user->status) }}</strong> - Current status
                    since {{ $user->updated_at->format('d M Y, H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i>Close
    </button>
    <button type="button" class="btn btn-primary" onclick="editUser({{ $user->id }})">
        <i class="fas fa-edit me-1"></i>Edit Profile
    </button>
</div>

<script>
    function changeUserStatus(userId, statusId, statusName) {
        // Tutup modal sebelum tampilkan konfirmasi Swal
        const modal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
        if (modal) modal.hide();

        // Tampilkan konfirmasi
        Swal.fire({
            title: 'Change Status?',
            text: `Are you sure you want to change status to ${statusName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('user-management.user.update-status') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        user_id: userId,
                        status_id: statusId
                    },
                    success: function(response) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            // Reload halaman untuk muat ulang status user
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message ?? 'Something went wrong.',
                                'error')
                            .then(() => {
                                // Jika gagal, buka kembali modal
                                if (modal) modal.show();
                            });
                    }
                });
            } else {
                // Jika batal, buka kembali modal
                if (modal) modal.show();
            }
        });
    }
</script>
