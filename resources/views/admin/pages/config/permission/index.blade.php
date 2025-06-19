@extends('layouts.admin.main')
@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light"> Dashboard / Config</span> / Permission
        </h4>
        <button href="#" class="btn btn-primary open-global-modal" data-url="{{ route('config.permission.create') }}"
            data-title="Add Permission">
            Add new permission
        </button>
    </div>
@endpush
@section('content')
    <div class="card">
        <h5 class="card-header">Permissions</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table" id="permission-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Guard Name</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function () {
            $('#permission-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('config.permission.getData') }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'guard_name', name: 'guard_name' },
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
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            if ($.fn.DataTable.isDataTable('#permission-table')) {
                                $('#permission-table').DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush