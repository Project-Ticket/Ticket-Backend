@extends('layouts.admin.main')
@php
    use App\Services\Status;
@endphp
@push('page-header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('event-organizer') }}" class="text-decoration-none">Event Organizer</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $organizer->organization_name }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Detail Event Organizer</h4>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('event-organizer') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-1"></i> Aksi
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal">
                        <i class="fas fa-edit me-2"></i>Edit Data</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                @if ($organizer->verification_status == 'pending')
                    <li>
                        <a class="dropdown-item text-success" href="#"
                            onclick="updateStatus('verification_status', 'verified')">
                            <i class="fas fa-check-circle me-2"></i>Verifikasi</a>
                    </li>
                    <li>

                        <button class="dropdown-item text-warning open-global-modal"
                            data-url="{{ route('event-organizer.reject-verification-modal', $organizer->uuid) }}"
                            data-title="Add New Setting">
                            <i class="fas fa-times-circle me-2"></i>Tolak Verifikasi
                        </button>
                    </li>
                @endif
                <li>
                    <hr class="dropdown-divider">
                </li>
                @if ($organizer->application_status == 'pending' || $organizer->application_status == 'under_review')
                    <li><a class="dropdown-item text-success" href="#" onclick="updateStatus('application_status', 'approved')">
                            <i class="fas fa-thumbs-up me-2"></i>Setujui Aplikasi</a>
                    </li>
                    <li>
                        <button class="dropdown-item text-warning open-global-modal"
                            data-url="{{ route('event-organizer.reject-application-modal', $organizer->uuid) }}"
                            data-title="Add New Setting">
                            <i class="fas fa-thumbs-down me-2"></i>Tolak Aplikasi
                        </button>
                    </li>
                @endif
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item {{ $organizer->status == 1 ? 'text-danger' : 'text-success' }}" href="#"
                        onclick="toggleActiveStatus()">
                        <i class="fas fa-{{ $organizer->status == 1 ? 'ban' : 'check' }} me-2"></i>
                        {{ $organizer->status == 1 ? 'Nonaktifkan' : 'Aktifkan' }}
                    </a></li>
            </ul>
        </div>
    </div>
@endpush

@section('content')
    <!-- Status Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-certificate text-primary fs-2"></i>
                    </div>
                    <h6 class="card-title text-muted mb-1">Status Verifikasi</h6>
                    <span
                        class="badge fs-6 bg-{{ $organizer->verification_status == 'verified' ? 'success' : ($organizer->verification_status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst($organizer->verification_status) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-clipboard-check text-info fs-2"></i>
                    </div>
                    <h6 class="card-title text-muted mb-1">Status Aplikasi</h6>
                    <span
                        class="badge fs-6 bg-{{ $organizer->application_status == 'approved' ? 'success' : ($organizer->application_status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst(str_replace('_', ' ', $organizer->application_status)) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i
                            class="fas fa-toggle-{{ $organizer->status == 1 ? 'on text-success' : 'off text-danger' }} fs-2"></i>
                    </div>
                    <h6 class="card-title text-muted mb-1">Status Aktif</h6>
                    <span class="badge fs-6 bg-{{ $organizer->status == 1 ? 'success' : 'danger' }}">
                        {{ $organizer->status == 1 ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-calendar text-secondary fs-2"></i>
                    </div>
                    <h6 class="card-title text-muted mb-1">Tanggal Daftar</h6>
                    <small class="text-dark">{{ $organizer->created_at->format('d M Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Organization Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building text-primary me-2"></i>
                        Informasi Organisasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Nama Organisasi</label>
                                <p class="fw-semibold mb-0">{{ $organizer->organization_name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Slug</label>
                                <p class="mb-0">
                                    <code class="bg-light px-2 py-1 rounded">{{ $organizer->organization_slug }}</code>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Website</label>
                                @if($organizer->website)
                                    <p class="mb-0">
                                        <a href="{{ $organizer->website }}" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            {{ $organizer->website }}
                                        </a>
                                    </p>
                                @else
                                    <p class="text-muted mb-0">-</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Lokasi</label>
                                <p class="mb-1">{{ $organizer->city }}, {{ $organizer->province }}</p>
                                <small class="text-muted">{{ $organizer->postal_code }}</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Alamat Lengkap</label>
                                <p class="mb-0">{{ $organizer->address }}</p>
                            </div>
                        </div>
                    </div>

                    @if($organizer->description)
                        <div class="border-top pt-3 mb-3">
                            <label class="form-label text-muted small">Deskripsi</label>
                            <p class="mb-0">{{ $organizer->description }}</p>
                        </div>
                    @endif

                    <!-- Social Media -->
                    <div class="border-top pt-3">
                        <label class="form-label text-muted small">Media Sosial</label>
                        <div class="d-flex flex-wrap gap-2">
                            @if($organizer->instagram)
                                <a href="https://instagram.com/{{ $organizer->instagram }}" target="_blank"
                                    class="btn btn-outline-danger btn-sm">
                                    <i class="fab fa-instagram me-1"></i> {{ $organizer->instagram }}
                                </a>
                            @endif
                            @if($organizer->twitter)
                                <a href="https://twitter.com/{{ $organizer->twitter }}" target="_blank"
                                    class="btn btn-outline-info btn-sm">
                                    <i class="fab fa-twitter me-1"></i> {{ $organizer->twitter }}
                                </a>
                            @endif
                            @if($organizer->facebook)
                                <a href="https://facebook.com/{{ $organizer->facebook }}" target="_blank"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-facebook me-1"></i> {{ $organizer->facebook }}
                                </a>
                            @endif
                            @if(!$organizer->instagram && !$organizer->twitter && !$organizer->facebook)
                                <span class="text-muted">Tidak ada media sosial yang terdaftar</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Banking Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-address-book text-success me-2"></i>
                        Informasi Kontak & Perbankan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted border-bottom pb-2 mb-3">Kontak Person</h6>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Nama</label>
                                <p class="mb-0">{{ $organizer->contact_person }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Telepon</label>
                                <p class="mb-0">
                                    <a href="tel:{{ $organizer->contact_phone }}" class="text-decoration-none">
                                        <i class="fas fa-phone me-1"></i>
                                        {{ $organizer->contact_phone }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Email</label>
                                <p class="mb-0">
                                    <a href="mailto:{{ $organizer->contact_email }}" class="text-decoration-none">
                                        <i class="fas fa-envelope me-1"></i>
                                        {{ $organizer->contact_email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted border-bottom pb-2 mb-3">Informasi Bank</h6>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Bank</label>
                                <p class="mb-0">{{ $organizer->bank_name ?? '-' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Nomor Rekening</label>
                                <p class="mb-0">
                                    @if($organizer->bank_account_number)
                                        <code class="bg-light px-2 py-1 rounded">{{ $organizer->bank_account_number }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Nama Pemegang</label>
                                <p class="mb-0">{{ $organizer->bank_account_name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    @if($organizer->application_fee || $organizer->security_deposit)
                        <div class="border-top pt-3">
                            <h6 class="text-muted border-bottom pb-2 mb-3">Informasi Finansial</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Biaya Pendaftaran</label>
                                        <p class="mb-0 fw-semibold">
                                            Rp{{ number_format($organizer->application_fee ?? 0, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Deposit Keamanan</label>
                                        <p class="mb-0 fw-semibold">
                                            Rp{{ number_format($organizer->security_deposit ?? 0, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Owner Information & Documents -->
        <div class="col-lg-4">
            <!-- Owner Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user text-info me-2"></i>
                        Informasi Pemilik
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-user text-muted fs-2"></i>
                        </div>
                        <h6 class="mb-0">{{ $organizer->user->name }}</h6>
                        <small class="text-muted">Owner</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <p class="mb-0">
                            <a href="mailto:{{ $organizer->user->email }}" class="text-decoration-none">
                                {{ $organizer->user->email }}
                            </a>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Telepon</label>
                        <p class="mb-0">
                            @if($organizer->user->phone)
                                <a href="tel:{{ $organizer->user->phone }}" class="text-decoration-none">
                                    {{ $organizer->user->phone }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Status Akun</label>
                        <div>{!! Status::getBadgeHtml('userStatus', $organizer->user->status) !!}</div>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt text-warning me-2"></i>
                        Dokumen
                    </h5>
                    <span class="badge bg-secondary">
                        {{ $organizer->uploaded_documents ? count(json_decode($organizer->uploaded_documents, true)) : 0 }}
                    </span>
                </div>

                <div class="card-body">
                    @if($organizer->uploaded_documents)
                        @php
                            $documents = json_decode($organizer->uploaded_documents, true);
                        @endphp

                        <div class="list-group list-group-flush">
                            @foreach($documents as $docPath)
                                @php
                                    $fileName = basename($docPath); // contoh: file-ktp.pdf
                                    $fileUrl = asset('storage/' . $docPath); // contoh: /storage/documents/file-ktp.pdf
                                @endphp
                                <div class="list-group-item px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <span class="small">{{ $fileName }}</span>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ $fileUrl }}" download class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-folder-open fs-1 mb-2 d-block"></i>
                            <p class="mb-0">Belum ada dokumen yang diupload</p>
                        </div>
                    @endif
                </div>
            </div>


            <!-- Review Information -->
            @if($organizer->reviewed_by || $organizer->reviewed_at)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-check text-success me-2"></i>
                            Informasi Review
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($organizer->reviewed_by)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Direview oleh</label>
                                <p class="mb-0">Admin #{{ $organizer->reviewed_by }}</p>
                            </div>
                        @endif
                        @if($organizer->reviewed_at)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Tanggal Review</label>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($organizer->reviewed_at)->format('d M Y H:i') }}</p>
                            </div>
                        @endif
                        @if($organizer->verified_at)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Tanggal Verifikasi</label>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($organizer->verified_at)->format('d M Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Notes Section -->
    @if($organizer->verification_notes || $organizer->rejection_reason)
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sticky-note text-secondary me-2"></i>
                            Catatan & Keterangan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($organizer->verification_notes)
                                <div class="col-md-6">
                                    <div class="alert alert-info border-0">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Catatan Verifikasi
                                        </h6>
                                        <p class="mb-0">{{ $organizer->verification_notes }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($organizer->rejection_reason)
                                <div class="col-md-6">
                                    <div class="alert alert-danger border-0">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Alasan Penolakan
                                        </h6>
                                        <p class="mb-0">{{ $organizer->rejection_reason }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event Organizer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST" action="{{ url('event-organizer.update', $organizer->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Pendaftaran</label>
                                    <input type="number" class="form-control" name="application_fee"
                                        value="{{ $organizer->application_fee }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Deposit Keamanan</label>
                                    <input type="number" class="form-control" name="security_deposit"
                                        value="{{ $organizer->security_deposit }}">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Verifikasi</label>
                            <textarea class="form-control" name="verification_notes"
                                rows="3">{{ $organizer->verification_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(statusType, statusValue) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Anda akan mengubah status ini!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, ubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("event-organizer.update-status", $organizer->uuid) }}';

                    form.innerHTML = `
                                @csrf
                                <input type="hidden" name="status_type" value="${statusType}">
                                <input type="hidden" name="status_value" value="${statusValue}">
                            `;

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function toggleActiveStatus() {
            const currentStatus = {{ $organizer->status }};
            const newStatus = currentStatus == 1 ? 2 : 1;
            const action = newStatus == 1 ? 'mengaktifkan' : 'menonaktifkan';

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Anda akan ${action} Event Organizer ini.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `Ya, ${action}`,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("event-organizer.update-status", $organizer->uuid) }}';

                    form.innerHTML = `
                                @csrf
                                <input type="hidden" name="status_type" value="status">
                                <input type="hidden" name="status_value" value="${newStatus}">
                            `;

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection