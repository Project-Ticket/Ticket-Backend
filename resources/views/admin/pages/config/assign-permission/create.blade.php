@extends('layouts.admin.main')

@push('page-haeder')
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Assign User Permission</h2>
            </div>
        </div>
    </div>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .permission-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .permission-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .permission-card.selected {
            border-color: #007bff;
            background-color: #f8f9fa;
        }

        .permission-item {
            padding: 12px 16px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            background: white;
            transition: all 0.2s ease;
        }

        .permission-item:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
        }

        .permission-item.selected {
            background-color: #e7f3ff;
            border-color: #007bff;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .permission-counter {
            font-size: 0.9em;
            color: #6c757d;
        }

        .btn-action {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .permission-list {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .permission-list::-webkit-scrollbar {
            width: 6px;
        }

        .permission-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .permission-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .permission-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong>Name:</strong> {{ $user->name }} <br>
                <strong>Email:</strong> {{ $user->email }}
            </div>
            <div>
                <a href="{{ route('config.assign') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
    <div class="row align-items-start justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Available Permissions</h3>
                    <span class="badge bg-primary permission-counter" id="available-counter">
                        {{ count($availablePermissions) }} items
                    </span>
                </div>
                <div class="card-body">
                    <div class="search-box">
                        <input type="text" class="form-control" id="search-available"
                            placeholder="🔍 Search available permissions...">
                    </div>
                    <div class="permission-list" id="available-permissions">
                        @foreach ($availablePermissions as $permission)
                            <div class="permission-item" data-permission="{{ $permission->name }}" data-type="available">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $permission->name }}</strong>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $permission->name }}"
                                            name="available_permissions[]">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="action-buttons">
                <button class="btn btn-primary btn-action" id="assign-btn" title="Assign Permissions">
                    <i class="fas fa-arrow-right"></i>
                </button>
                <div class="text-center">
                    <small class="text-muted">Transfer</small>
                </div>
                <button class="btn btn-danger btn-action" id="revoke-btn" title="Revoke Permissions">
                    <i class="fas fa-arrow-left"></i>
                </button>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Assigned Permissions</h3>
                    <span class="badge bg-success permission-counter" id="assigned-counter">
                        {{ count($assignedPermissions) }} items
                    </span>
                </div>
                <div class="card-body">
                    <div class="search-box">
                        <input type="text" class="form-control" id="search-assigned"
                            placeholder="🔍 Search assigned permissions...">
                    </div>
                    <div class="permission-list" id="assigned-permissions">
                        @foreach ($assignedPermissions as $permission)
                            <div class="permission-item" data-permission="{{ $permission->name }}" data-type="assigned">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $permission->name }}</strong>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $permission->name }}"
                                            name="assigned_permissions[]">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#search-available').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                $('#available-permissions .permission-item').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
                updateCounter('available');
            });

            $('#search-assigned').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                $('#assigned-permissions .permission-item').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
                updateCounter('assigned');
            });

            $('.permission-item').on('click', function (e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = $(this).find('input[type="checkbox"]');
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
                $(this).toggleClass('selected', $(this).find('input[type="checkbox"]').prop('checked'));
            });

            $('.permission-item input[type="checkbox"]').on('change', function () {
                $(this).closest('.permission-item').toggleClass('selected', $(this).prop('checked'));
            });

            $('#assign-btn').click(function () {
                const selectedPermissions = [];
                $('#available-permissions input[type="checkbox"]:checked').each(function () {
                    selectedPermissions.push($(this).val());
                });

                if (selectedPermissions.length === 0) {
                    showToast('Please select at least one permission to assign', 'warning');
                    return;
                }

                const button = $(this);
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route("config.assign.assign") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: '{{ $user->id }}',
                        permissions: selectedPermissions
                    },
                    success: function (response) {
                        if (response.success) {
                            movePermissions(selectedPermissions, 'available', 'assigned');
                            showToast('Permissions assigned successfully', 'success');
                        } else {
                            showToast('Failed to assign permissions', 'error');
                        }
                    },
                    error: function () {
                        showToast('Failed to assign permissions', 'error');
                    },
                    complete: function () {
                        button.prop('disabled', false).html('<i class="fas fa-arrow-right"></i>');
                    }
                });
            });

            $('#revoke-btn').click(function () {
                const selectedPermissions = [];
                $('#assigned-permissions input[type="checkbox"]:checked').each(function () {
                    selectedPermissions.push($(this).val());
                });

                if (selectedPermissions.length === 0) {
                    showToast('Please select at least one permission to revoke', 'warning');
                    return;
                }

                const button = $(this);
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route("config.assign.revoke") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: '{{ $user->id }}',
                        permissions: selectedPermissions
                    },
                    success: function (response) {
                        if (response.success) {
                            movePermissions(selectedPermissions, 'assigned', 'available');
                            showToast('Permissions revoked successfully', 'success');
                        } else {
                            showToast('Failed to revoke permissions', 'error');
                        }
                    },
                    error: function () {
                        showToast('Failed to revoke permissions', 'error');
                    },
                    complete: function () {
                        button.prop('disabled', false).html('<i class="fas fa-arrow-left"></i>');
                    }
                });
            });

            function movePermissions(permissions, from, to) {
                permissions.forEach(function (perm) {
                    const item = $(`#${from}-permissions .permission-item[data-permission="${perm}"]`);
                    item.find('input[type="checkbox"]').prop('checked', false);
                    item.removeClass('selected');
                    item.appendTo(`#${to}-permissions`);
                });
                updateCounter(from);
                updateCounter(to);
            }

            function updateCounter(type) {
                const count = $(`#${type}-permissions .permission-item:visible`).length;
                $(`#${type}-counter`).text(count + ' items');
            }

            function showToast(message, type) {
                const toastClass = {
                    success: 'bg-success text-white',
                    error: 'bg-danger text-white',
                    warning: 'bg-warning text-dark'
                }[type] || 'bg-info text-white';

                const toast = $(`
                                                                <div class="toast align-items-center ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                                                                    <div class="d-flex">
                                                                        <div class="toast-body">${message}</div>
                                                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                                                    </div>
                                                                </div>
                                                            `);

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();
                toast.on('hidden.bs.toast', function () {
                    $(this).remove();
                });
            }
        });
    </script>

    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;"></div>
@endpush