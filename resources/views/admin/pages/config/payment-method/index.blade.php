@extends('layouts.admin.main')
@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light"> Dashboard / Config</span> / Payment Methods
        </h4>
        <button href="#" class="btn btn-primary open-global-modal" data-url="{{ route('config.payment-method.create') }}"
            data-title="Add Payment Method">
            Add new Payment Method
        </button>
    </div>
@endpush
@section('content')
    <div class="card">
        <h5 class="card-header">Payment Methods</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table" id="payment-method-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Status</th> <!-- Kolom baru untuk status -->
                        <th>Fee Percentage</th>
                        <th>Fixed Fee</th>
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
            $('#payment-method-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('config.payment-method.getData') }}',
                columns: [
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    { data: 'type', name: 'type' },
                    { data: 'status', name: 'status', orderable: false, searchable: false }, // Kolom status
                    { data: 'fee_percentage', name: 'fee_percentage' },
                    { data: 'fee_fixed', name: 'fee_fixed' },
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

                            if ($.fn.DataTable.isDataTable('#payment-method-table')) {
                                $('#payment-method-table').DataTable().ajax.reload(null, false);
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

        // Handle switch click to update status
        $(document).on('change', '.status-toggle', function () {
            const id = $(this).data('id');
            const newStatus = $(this).is(':checked');
            const url = `{{ url('config/payment-method') }}/${id}/status`;

            $.ajax({
                url: url,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: newStatus
                },
                success: function (response) {
                    Swal.fire('Berhasil!', response.message, 'success');
                    $('#payment-method-table').DataTable().ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan saat mengubah status.', 'error');
                    // Reset switch to original state if error occurs
                    $(this).prop('checked', !newStatus);
                }
            });
        });
    </script>
@endpush