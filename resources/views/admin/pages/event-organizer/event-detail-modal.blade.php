<div class="modal-header bg-success text-white">
    <h5 class="modal-title d-flex align-items-center" id="eventDetailsModalLabel">
        <i class="fas fa-calendar-alt me-2"></i>
        <div>
            <div>Detail Event</div>
            <small class="fw-normal opacity-75">{{ $event->title }}</small>
        </div>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <!-- Event Banner/Status -->
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            <strong>Status:</strong>
            @if($event->start_datetime > now())
                <span class="badge bg-warning">Akan Datang</span>
            @elseif($event->end_datetime < now())
                <span class="badge bg-secondary">Selesai</span>
            @else
                <span class="badge bg-success">Sedang Berlangsung</span>
            @endif
        </div>
    </div>

    <!-- Event Details Cards -->
    <div class="row g-3">
        <!-- Description Card -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-alt me-2"></i> Deskripsi Event
                </div>
                <div class="card-body">
                    <p class="card-text mb-0">{{ $event->description }}</p>
                </div>
            </div>
        </div>

        <!-- Date & Time Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-clock me-2"></i> Waktu Event
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Mulai</small>
                        <div class="fw-semibold text-success">
                            <i class="fas fa-calendar-check me-1"></i>
                            {{ $event->start_datetime->format('d M Y') }}
                        </div>
                        <div class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            {{ $event->start_datetime->format('H:i') }} WIB
                        </div>
                    </div>
                    <div>
                        <small class="text-muted d-block">Selesai</small>
                        <div class="fw-semibold text-danger">
                            <i class="fas fa-calendar-times me-1"></i>
                            {{ $event->end_datetime->format('d M Y') }}
                        </div>
                        <div class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            {{ $event->end_datetime->format('H:i') }} WIB
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-map-marker-alt me-2"></i> Lokasi Event
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="fw-semibold">
                            {{ $event->venue_name ?? 'Event Online' }}
                        </div>
                        @if($event->venue_address)
                            <small class="text-muted">{{ $event->venue_address }}</small>
                        @endif
                    </div>
                    @if ($event->online_link)
                        <div class="mt-3">
                            <small class="text-muted d-block">Link Meeting</small>
                            <a href="{{ $event->online_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i> Bergabung
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Category & Additional Info -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-tags me-2"></i> Kategori
                </div>
                <div class="card-body">
                    <span class="badge bg-primary fs-6">{{ $event->category->name }}</span>
                </div>
            </div>
        </div>

        <!-- Participants Info -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-users me-2"></i> Peserta
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h6 mb-0">{{ $event->registrations->count() }} Terdaftar</div>
                            <small class="text-muted">
                                @if($event->max_participants)
                                    dari {{ $event->max_participants }} maksimal
                                @else
                                    Tidak terbatas
                                @endif
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="progress" style="width: 60px; height: 8px;">
                                @php
                                    $percentage = $event->max_participants ?
                                        ($event->registrations->count() / $event->max_participants) * 100 : 0;
                                @endphp
                                <div class="progress-bar" role="progressbar" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer bg-light">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i> Tutup
    </button>
    <button type="button" class="btn btn-success">
        <i class="fas fa-share-alt me-1"></i> Bagikan Event
    </button>
</div>
