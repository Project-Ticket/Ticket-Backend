<div class="modal-header bg-primary text-white">
    <h5 class="modal-title d-flex align-items-center" id="participantsModalLabel">
        <i class="fas fa-users me-2"></i>
        <div>
            <div>Daftar Peserta Event</div>
            <small class="fw-normal opacity-75">{{ $event->title }}</small>
        </div>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-0">
    <!-- Stats Card -->
    <div class="bg-light border-bottom p-3">
        <div class="row text-center">
            <div class="col-4">
                <div class="h5 text-primary mb-1">{{ $event->registrations->count() }}</div>
                <small class="text-muted">Total Peserta</small>
            </div>
            <div class="col-4">
                <div class="h5 text-success mb-1">{{ $event->max_participants ?? '∞' }}</div>
                <small class="text-muted">Kapasitas</small>
            </div>
            <div class="col-4">
                <div class="h5 text-info mb-1">
                    @if($event->max_participants)
                        {{ $event->max_participants - $event->registrations->count() }}
                    @else
                        ∞
                    @endif
                </div>
                <small class="text-muted">Sisa Slot</small>
            </div>
        </div>
    </div>

    <!-- Participants Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="border-0">
                        <i class="fas fa-hashtag me-1"></i> No
                    </th>
                    <th class="border-0">
                        <i class="fas fa-user me-1"></i> Nama Peserta
                    </th>
                    <th class="border-0">
                        <i class="fas fa-envelope me-1"></i> Email
                    </th>
                    <th class="border-0">
                        <i class="fas fa-phone me-1"></i> Telepon
                    </th>
                    <th class="border-0">
                        <i class="fas fa-calendar me-1"></i> Terdaftar
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($event->registrations as $index => $registration)
                    <tr>
                        <td class="align-middle">
                            <span class="badge bg-primary rounded-pill">{{ $index + 1 }}</span>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                    style="width: 32px; height: 32px;">
                                    <i class="fas fa-user text-white small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $registration->user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <a href="mailto:{{ $registration->attendee_email }}" class="text-decoration-none">
                                {{ $registration->attendee_email }}
                            </a>
                        </td>
                        <td class="align-middle">
                            <a href="tel:{{ $registration->attendee_phone }}" class="text-decoration-none">
                                {{ $registration->attendee_phone }}
                            </a>
                        </td>
                        <td class="align-middle">
                            <small class="text-muted">
                                {{ $registration->created_at->format('d/m/Y H:i') }}
                            </small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                                <h6>Belum Ada Peserta</h6>
                                <p class="mb-0">Belum ada peserta yang mendaftar untuk event ini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer bg-light">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i> Tutup
    </button>
    <button type="button" class="btn btn-primary">
        <i class="fas fa-download me-1"></i> Export Data
    </button>
</div>