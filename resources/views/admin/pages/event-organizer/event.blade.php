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
                    <li class="breadcrumb-item">
                        <a href="{{ route('event-organizer.show', $organizer->uuid) }}"
                            class="text-decoration-none">{{ $organizer->organization_name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Events</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Events - {{ $organizer->organization_name }}</h4>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('event-organizer.show', $organizer->uuid) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail
            </a>
            <a href="{{ route('event-organizer') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i> Semua EO
            </a>
        </div>
    </div>
@endpush

@section('content')
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fs-2 mb-2"></i>
                    <h3 class="mb-1">{{ $totalEvents }}</h3>
                    <small>Total Events</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-play-circle fs-2 mb-2"></i>
                    <h3 class="mb-1">{{ $activeEvents }}</h3>
                    <small>Events Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fs-2 mb-2"></i>
                    <h3 class="mb-1">{{ $upcomingEvents }}</h3>
                    <small>Events Mendatang</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fs-2 mb-2"></i>
                    {{-- <h3 class="mb-1">{{ $totalParticipants }}</h3> --}}
                    <small>Total Peserta</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizer Quick Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-building text-muted fs-2"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-1">{{ $organizer->organization_name }}</h5>
                            <p class="text-muted mb-2">{{ $organizer->city }}, {{ $organizer->province }}</p>
                            <div class="d-flex gap-2">
                                <span
                                    class="badge bg-{{ $organizer->verification_status == 'verified' ? 'success' : ($organizer->verification_status == 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($organizer->verification_status) }}
                                </span>
                                <span class="badge bg-{{ $organizer->status == 1 ? 'success' : 'danger' }}">
                                    {{ $organizer->status == 1 ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted d-block">Bergabung</small>
                                    <strong>{{ $organizer->created_at->format('M Y') }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Owner</small>
                                    <strong>{{ $organizer->user->name }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar-check text-primary me-2"></i>
                Daftar Events
            </h5>
            <div class="d-flex gap-2">
                <!-- Filter Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i> Filter Status
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?status=all">Semua Status</a></li>
                        <li><a class="dropdown-item" href="?status=active">Aktif</a></li>
                        <li><a class="dropdown-item" href="?status=draft">Draft</a></li>
                        <li><a class="dropdown-item" href="?status=completed">Selesai</a></li>
                        <li><a class="dropdown-item" href="?status=cancelled">Dibatalkan</a></li>
                    </ul>
                </div>

                <!-- Search -->
                <form method="GET" class="d-flex">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari event..."
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Event</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th style="width: 100px;">Peserta</th>
                                <th style="width: 120px;">Harga</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $index => $event)
                                {{-- @dd($event) --}}
                                <tr>
                                    <td class="text-muted">
                                        {{ ($events->currentPage() - 1) * $events->perPage() + $index + 1 }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                @if ($event->banner_image)
                                                    <img src="{{ asset('storage/' . $event->banner_image) }}"
                                                        alt="Event Banner" class="rounded"
                                                        style="width: 60px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                        style="width: 60px; height: 40px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="#" class="text-decoration-none text-dark fw-bold">
                                                        {{ $event->title }}
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $event->location ?? 'Online' }}
                                                </small>
                                                @if ($event->category)
                                                    <span
                                                        class="badge bg-light text-dark ms-2 small">{{ $event->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <strong
                                                class="d-block">{{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M') : '-' }}</strong>
                                            <small
                                                class="text-muted">{{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('Y') : '' }}</small>
                                            @if ($event->start_time)
                                                <small
                                                    class="d-block text-muted">{{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div>
                                            <strong class="d-block">{{ $event->registrations_count ?? 0 }}</strong>
                                            @if ($event->max_participants)
                                                <small class="text-muted">/ {{ $event->max_participants }}</small>
                                                <div class="progress mt-1" style="height: 3px;">
                                                    @php
                                                        $percentage =
                                                            $event->max_participants > 0
                                                                ? (($event->registrations_count ?? 0) /
                                                                        $event->max_participants) *
                                                                    100
                                                                : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-{{ $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success') }}"
                                                        style="width: {{ min($percentage, 100) }}%"></div>
                                                </div>
                                            @else
                                                <small class="text-muted">Unlimited</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($event->is_free)
                                            <span class="badge bg-success">GRATIS</span>
                                        @else
                                            <strong
                                                class="d-block">Rp{{ number_format($event->price ?? 0, 0, ',', '.') }}</strong>
                                            @if ($event->early_bird_price && $event->early_bird_deadline && now() < $event->early_bird_deadline)
                                                <small class="text-success">Early Bird:
                                                    Rp{{ number_format($event->early_bird_price, 0, ',', '.') }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'draft' => ['bg-secondary', 'Draft'],
                                                'published' => ['bg-success', 'Published'],
                                                'active' => ['bg-primary', 'Aktif'],
                                                'completed' => ['bg-info', 'Selesai'],
                                                'cancelled' => ['bg-danger', 'Dibatalkan'],
                                            ];
                                            $config = $statusConfig[$event->status] ?? [
                                                'bg-dark',
                                                ucfirst($event->status),
                                            ];
                                        @endphp
                                        <span class="badge {{ $config[0] }}">{{ $config[1] }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#" class="btn btn-outline-primary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-info" title="Lihat Peserta">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#">
                                                            <i class="fas fa-edit me-2"></i>Edit Event
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#">
                                                            <i class="fas fa-copy me-2"></i>Duplikat
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item" href="#">
                                                            <i class="fas fa-chart-bar me-2"></i>Statistik
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#">
                                                            <i class="fas fa-download me-2"></i>Export Data
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    @if ($event->status == 'active')
                                                        <li><a class="dropdown-item text-warning" href="#">
                                                                <i class="fas fa-pause me-2"></i>Pause Event
                                                            </a></li>
                                                    @endif
                                                    @if ($event->status != 'cancelled')
                                                        <li><a class="dropdown-item text-danger" href="#">
                                                                <i class="fas fa-ban me-2"></i>Cancel Event
                                                            </a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($events->hasPages())
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Menampilkan {{ $events->firstItem() }} sampai {{ $events->lastItem() }}
                                dari {{ $events->total() }} events
                            </div>
                            <div>
                                {{ $events->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted mb-2">Belum Ada Events</h5>
                    <p class="text-muted mb-3">Event organizer ini belum memiliki event yang terdaftar.</p>
                    @if (request('search') || request('status') != 'all')
                        <a href="{{ route('event-organizer.events', $organizer->uuid) }}"
                            class="btn btn-outline-primary">
                            <i class="fas fa-refresh me-1"></i> Reset Filter
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Activity (Optional) -->
    @if ($events->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-history text-secondary me-2"></i>
                            Events Terbaru
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($events->take(3) as $event)
                                <div class="col-md-4 mb-3">
                                    <div class="card border h-100">
                                        @if ($event->banner_image)
                                            <img src="{{ asset('storage/' . $event->banner_image) }}"
                                                class="card-img-top" style="height: 150px; object-fit: cover;"
                                                alt="Event Banner">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                                style="height: 150px;">
                                                <i class="fas fa-image text-muted fs-1"></i>
                                            </div>
                                        @endif
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2">{{ Str::limit($event->title, 50) }}</h6>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : '-' }}
                                                </small>
                                                <span
                                                    class="badge bg-{{ $statusConfig[$event->status][0] ?? 'dark' }} badge-sm">
                                                    {{ $statusConfig[$event->status][1] ?? ucfirst($event->status) }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1"></i>
                                                    {{ $event->registrations_count ?? 0 }} peserta
                                                </small>
                                                <small class="fw-bold text-{{ $event->is_free ? 'success' : 'primary' }}">
                                                    {{ $event->is_free ? 'GRATIS' : 'Rp' . number_format($event->price ?? 0, 0, ',', '.') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        // Auto refresh every 5 minutes for real-time updates
        setTimeout(function() {
            location.reload();
        }, 300000);

        // Tooltip initialization
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
@endpush
