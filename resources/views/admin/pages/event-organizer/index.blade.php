@extends('layouts.admin.main')

@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Dashboard / </span> Event Organizer
        </h4>
    </div>
@endpush

@section('content')
    <div class="card">
        <h5 class="card-header">Event Organizer</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table table-bordered table-hover" id="eo-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Organization</th>
                        <th>Owner</th>
                        <th>Owner Email</th>
                        <th>Owner Phone</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Verification</th>
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
            $('#eo-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('event-organizer.getData') }}',
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'organization_name',
                    name: 'organization_name'
                },
                {
                    data: 'owner_name',
                    name: 'user.name'
                },
                {
                    data: 'owner_email',
                    name: 'user.email'
                },
                {
                    data: 'owner_phone',
                    name: 'user.phone'
                },
                {
                    data: 'city',
                    name: 'city'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'verification_status',
                    name: 'verification_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                ]
            });
        });

        $(document).on('click', '.link-under-review', function (e) {
            e.preventDefault();
            const url = $(this).data('url');
            const uuid = $(this).data('uuid');

            $.ajax({
                url: `/~admin-panel/event-organizer/${uuid}/mark-under-review`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                complete: function () {
                    window.location.href = url;
                }
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
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            Swal.fire('Berhasil!', res.message, 'success');
                            $('#eo-table').DataTable().ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message ||
                                'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endpush
