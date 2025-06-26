@extends('layouts.admin.main')
@php
    use App\Services\Status;
@endphp
@push('page-header')
    @push('page-header')
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0">
                    <span class="text-muted fw-light">Events / </span> {{ $event->title }}
                </h4>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('event') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <button class="btn btn-danger btn-delete" data-id="{{ $event->id }}">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Event
                </button>
            </div>
        </div>
    @endpush
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card overflow-hidden">
                    <div class="position-relative">
                        <img src="{{ Storage::url($event->banner_image) }}" class="card-img-top img-fluid"
                            alt="{{ $event->title }} Banner" style="max-height: 400px; object-fit: cover;">
                        <div class="position-absolute top-0 end-0 p-3">
                            {!! Status::getBadgeHtml('eventStatus', $event->status, 'badge fs-6') !!}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Event Details --}}
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary ">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-info-circle me-2"></i> Detail Acara
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Judul Acara</h6>
                                <p class="fw-bold">{{ $event->title }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Kategori</h6>
                                <span class="badge bg-info">
                                    {{ $event->category->name }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Tipe Acara</h6>
                                <span class="badge bg-secondary text-uppercase">
                                    {{ $event->type }}
                                </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">Rentang Usia</h6>
                                <p>
                                    {{ $event->min_age ? $event->min_age . ' - ' : 'Tidak ada batasan' }}
                                    {{ $event->max_age ?? '' }} Tahun
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Description Section --}}
                <div class="card mb-4">
                    <div class="card-header bg-success">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-align-left me-2"></i> Deskripsi Acara
                        </h5>
                    </div>
                    <div class="card-body">
                        {!! $event->description !!}
                    </div>
                </div>

                {{-- Terms and Conditions --}}
                @if($event->terms_conditions)
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-contract me-2"></i> Syarat & Ketentuan
                            </h5>
                        </div>
                        <div class="card-body">
                            {!! $event->terms_conditions !!}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Organizer Card --}}
                <div class="card mb-4">
                    <div class="card-header bg-info ">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-building me-2"></i> Penyelenggara
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $event->organizer->logo ? Storage::url($event->organizer->logo) : asset('default-logo.png') }}"
                            class="rounded-circle mb-3" width="100" height="100"
                            alt="{{ $event->organizer->organization_name }}">
                        <h5 class="mb-2">{{ $event->organizer->organization_name }}</h5>
                        <p class="text-muted mb-3">{{ $event->organizer->contact_person }}</p>
                        <div class="d-flex justify-content-center gap-2">
                            @if($event->organizer->website)
                                <a href="{{ $event->organizer->website }}" target="_blank"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-globe"></i>
                                </a>
                            @endif
                            @if($event->organizer->instagram)
                                <a href="{{ $event->organizer->instagram }}" target="_blank"
                                    class="btn btn-outline-danger btn-sm">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Event Datetime Card --}}
                <div class="card mb-4">
                    <div class="card-header bg-primary">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-calendar-alt me-2"></i> Waktu & Lokasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted">Mulai Acara</h6>
                            <p>
                                <i class="fas fa-clock me-2"></i>
                                {{ $event->start_datetime->format('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Selesai Acara</h6>
                            <p>
                                <i class="fas fa-clock me-2"></i>
                                {{ $event->end_datetime->format('d M Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <h6 class="text-muted">Lokasi</h6>
                            @if($event->type === 'online')
                                <p>
                                    <i class="fas fa-desktop me-2"></i>
                                    {{ $event->online_platform }}
                                    <a href="{{ $event->online_link }}" target="_blank">Link Acara</a>
                                </p>
                            @else
                                <p>
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    {{ $event->venue_name }}, {{ $event->venue_city }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.btn-delete').on('click', function () {
                const eventId = $(this).data('id');

                Swal.fire({
                    title: 'Hapus Event?',
                    text: "Anda yakin ingin menghapus event ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('~admin-panel/event/destroy', '') }}/${eventId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire(
                                    'Terhapus!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href = '{{ route('event') }}';
                                });
                            },
                            error: function (xhr) {
                                Swal.fire(
                                    'Gagal!',
                                    xhr.responseJSON?.message || 'Terjadi kesalahan',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush