@extends('layouts.admin.main')

@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Dashboard / Config</span> / Settings
        </h4>
        <button class="btn btn-primary open-global-modal" data-url="{{ route('config.setting.create') }}"
            data-title="Add New Setting">
            Add New Setting
        </button>
    </div>
@endpush

@section('content')
    <div class="card">
        <h5 class="card-header">Settings</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table" id="setting-table">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                        <th>Type</th>
                        <th>Group</th>
                        <th>Is Public</th>
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
            $('#setting-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('config.setting.getData') }}',
                columns: [
                    { data: 'key', name: 'key' },
                    { data: 'value', name: 'value' },
                    { data: 'type', name: 'type' },
                    { data: 'group', name: 'group' },
                    { data: 'is_public', name: 'is_public', render: data => data ? 'Yes' : 'No' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
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
                            $('#setting-table').DataTable().ajax.reload(null, false);
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
