@extends('layouts.admin.main')

@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Dashboard / User Management</span> / User
        </h4>
        <button class="btn btn-primary open-global-modal" data-url="{{ route('user.create') }}"
            data-title="Add New Setting">
            Add New User
        </button>
    </div>
@endpush

@section('content')
    <div class="card">
        <h5 class="card-header">User</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table table-bordered table-hover" id="user-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Birth Date</th>
                        <th>Created At</th>
                        <th width="120px">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#user-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('user.getData') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'gender', name: 'gender' },
                    { data: 'city', name: 'city' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'birth_date', name: 'birth_date' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });
        });

        $(document).on('click', '.btn-global-delete', function (e) {
            e.preventDefault();
            const url = $(this).data('url');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            Swal.fire('Berhasil!', res.message, 'success');
                            $('#user-table').DataTable().ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endpush