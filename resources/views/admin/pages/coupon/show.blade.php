@php
    use Carbon\Carbon;
@endphp
<div class="modal-header" style="background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);">
    <h5 class="modal-title fw-bold text-white">
        <i class="fas fa-ticket-alt me-2"></i>Coupon Details
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body" style="background-color: #f4f6f9;">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-3" style="background-color: rgba(37, 117, 252, 0.1);">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <strong class="me-2 text-primary">Coupon Code:</strong>
                                <span class="badge bg-primary">{{ $coupon->code }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <strong class="me-2 text-primary">Coupon Name:</strong>
                                <span class="text-dark">{{ $coupon->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="d-flex justify-content-md-end align-items-center mb-2">
                                <strong class="me-2 text-primary">Status:</strong>
                                <span class="badge {{ $isActive ? 'bg-success' : 'bg-danger' }}">
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-md-end align-items-center mb-2">
                                <strong class="me-2 text-primary">Organizer:</strong>
                                <span
                                    class="text-dark">{{ $coupon->organizer ? $coupon->organizer->organization_name : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3" style="background-color: #e9f3ff;">
                <div class="card-header" style="background-color: #2575fc; color: white;">
                    <h6 class="card-title mb-0 d-flex align-items-center text-white">
                        <i class="fas fa-info-circle me-2"></i>Coupon Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong class="text-primary">Type:</strong>
                        <span class="text-dark ms-2">{{ ucfirst(str_replace('_', ' ', $coupon->type)) }}</span>
                    </div>
                    <div class="mb-2">
                        <strong class="text-primary">Value:</strong>
                        <span class="text-dark ms-2">
                            {{ $coupon->type === 'percentage' ? $coupon->value . '%' : 'Rp ' . number_format($coupon->value, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong class="text-primary">Applicable To:</strong>
                        <span class="text-dark ms-2">{{ ucfirst($coupon->applicable_to) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3" style="background-color: #e9f3ff;">
                <div class="card-header" style="background-color: #2575fc; color: white;">
                    <h6 class="card-title mb-0 d-flex align-items-center text-white">
                        <i class="fas fa-calendar-alt me-2"></i>Validity
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong class="text-primary">Valid From:</strong>
                        <span
                            class="text-dark ms-2">{{ Carbon::parse($coupon->valid_from)->format('d M Y H:i') }}</span>
                    </div>
                    <div class="mb-2">
                        <strong class="text-primary">Valid Until:</strong>
                        <span
                            class="text-dark ms-2">{{ Carbon::parse($coupon->valid_until)->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3" style="background-color: #e9f3ff;">
                <div class="card-header" style="background-color: #2575fc; color: white;">
                    <h6 class="card-title mb-0 d-flex align-items-center text-white">
                        <i class="fas fa-calendar me-2"></i>Applicable Events
                    </h6>
                </div>
                <div class="card-body">
                    @if($applicableEvents)
                        <ul class="list-unstyled">
                            @foreach($applicableEvents as $event)
                                <li class="mb-1">
                                    <i class="fas fa-check-circle me-2 text-success"></i>
                                    <span class="text-dark">{{ $event }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-exclamation-circle me-2 text-warning"></i>
                            No events specified
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3" style="background-color: #e9f3ff;">
                <div class="card-header" style="background-color: #2575fc; color: white;">
                    <h6 class="card-title mb-0 d-flex align-items-center text-white">
                        <i class="fas fa-tag me-2"></i>Applicable Merchandise
                    </h6>
                </div>
                <div class="card-body">
                    @if($applicableMerchandise)
                        <ul class="list-unstyled">
                            @foreach($applicableMerchandise as $merchandise)
                                <li class="mb-1">
                                    <i class="fas fa-check-circle me-2 text-success"></i>
                                    <span class="text-dark">{{ $merchandise }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-exclamation-circle me-2 text-warning"></i>
                            No merchandise specified
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer" style="background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);">
    <button type="button" class="btn btn-light text-primary" data-bs-dismiss="modal">Close</button>
</div>