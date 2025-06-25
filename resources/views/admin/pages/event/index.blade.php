@extends('layouts.admin.main')

@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Dashboard / </span> Events
        </h4>
    </div>
@endpush

@section('content')
    <div class="card">
        <h5 class="card-header">Events</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table table-bordered table-hover" id="events-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Title</th>
                        <th>Organizer</th>
                        <th>Category</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#events-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('event.getData') }}',
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'organizer_name',
                    name: 'organizer_name'
                },
                {
                    data: 'category.name',
                    name: 'category.name'
                },
                {
                    data: 'start_datetime',
                    name: 'start_datetime'
                },
                {
                    data: 'end_datetime',
                    name: 'end_datetime'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }]
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
                            $('#events-table').DataTable().ajax.reload(null, false);
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