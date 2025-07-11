@extends('layouts.admin.main')

@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Dashboard / </span> Coupons
        </h4>
        <button class="btn btn-primary open-global-modal" data-url="{{ route('coupon.create') }}" data-title="Add Coupon">
            Add New Coupon
        </button>
    </div>
@endpush

@section('content')
    <div class="card">
        <h5 class="card-header">Coupons</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table table-bordered table-hover" id="coupons-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Valid From</th>
                        <th>Valid Until</th>
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
            $('#coupons-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('coupon.getData') }}',
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'value',
                        name: 'value'
                    },
                    {
                        data: 'valid_from',
                        name: 'valid_from'
                    },
                    {
                        data: 'valid_until',
                        name: 'valid_until'
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
                            $('#coupons-table').DataTable().ajax.reload(null, false);
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