@extends('layouts.admin.main')

@push('styles')
    <style>
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .required {
            color: #dc3545;
        }

        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fafbfc;
        }

        .file-upload-area:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
            transform: translateY(-2px);
        }

        .file-upload-area.dragover {
            border-color: #667eea;
            background-color: #f0f2ff;
            transform: scale(1.02);
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 12px;
            margin-top: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 500;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .section-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1.5px solid #e3e6f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            transform: translateY(-1px);
        }

        .input-group-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 500;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .alert-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border: none;
            border-radius: 12px;
            color: #1565c0;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            margin: 0;
        }

        .section-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }
    </style>
@endpush

@push('page-header')
    <div class="page-header text-center">
        <div class="container">
            <h2 class="fw-bold mb-2">
                <i class="fas fa-user-plus me-3"></i>
                Edit Event Organizer
            </h2>
            <p class="mb-0 opacity-90">Create a comprehensive event organizer profile</p>
        </div>
    </div>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="main-card card">
                    <div class="card-body p-0">
                        <form action="{{ route('user-management.user-event-organizer.update', $user->uuid) }}" method="POST"
                            enctype="multipart/form-data" id="organizerForm" novalidate>
                            @csrf
                            @method('PUT')
                            <div class="p-4">
                                <div class="section-card card mt-4">
                                    <div class="card-body">
                                        <div class="section-header">
                                            <div class="section-icon">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div>
                                                <h5 class="section-title">Organization Information</h5>
                                                <p class="section-subtitle">Details about the organization or company</p>
                                            </div>
                                        </div>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="organization_name" class="form-label">Organization Name</label>
                                                <input type="text" class="form-control" name="organization_name"
                                                    id="organization_name"
                                                    value="{{ old('organization_name', $user->eventOrganizer->organization_name) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="organization_slug" class="form-label">Slug</label>
                                                <input type="text" class="form-control" name="organization_slug"
                                                    id="organization_slug"
                                                    value="{{ old('organization_slug', $user->eventOrganizer->organization_slug) }}">
                                            </div>
                                            <div class="col-12">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" name="description" id="description"
                                                    rows="4">{{ old('description', $user->eventOrganizer->description) }}</textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="logo" class="form-label">Logo</label>
                                                <input type="file" name="logo" class="form-control">
                                                @if($user->eventOrganizer->logo)
                                                    <img src="{{ asset('storage/' . $user->eventOrganizer->logo) }}" alt="Logo"
                                                        class="img-thumbnail mt-2" style="max-height: 100px;">
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label for="banner" class="form-label">Banner</label>
                                                <input type="file" name="banner" class="form-control">
                                                @if($user->eventOrganizer->banner)
                                                    <img src="{{ asset('storage/' . $user->eventOrganizer->banner) }}"
                                                        alt="Banner" class="img-thumbnail mt-2" style="max-height: 100px;">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-card card mt-4">
                                    <div class="card-body">
                                        <div class="section-header">
                                            <div class="section-icon">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            <div>
                                                <h5 class="section-title">Media & Branding</h5>
                                                <p class="section-subtitle">Upload logo and banner for your organization</p>
                                            </div>
                                        </div>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="contact_person" class="form-label">Contact Person</label>
                                                <input type="text" class="form-control" name="contact_person"
                                                    id="contact_person"
                                                    value="{{ old('contact_person', $user->eventOrganizer->contact_person) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                                <input type="text" class="form-control" name="contact_phone"
                                                    id="contact_phone"
                                                    value="{{ old('contact_phone', $user->eventOrganizer->contact_phone) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="contact_email" class="form-label">Contact Email</label>
                                                <input type="email" class="form-control" name="contact_email"
                                                    id="contact_email"
                                                    value="{{ old('contact_email', $user->eventOrganizer->contact_email) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="website" class="form-label">Website</label>
                                                <input type="url" class="form-control" name="website" id="website"
                                                    value="{{ old('website', $user->eventOrganizer->website) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="instagram" class="form-label">Instagram</label>
                                                <input type="text" class="form-control" name="instagram" id="instagram"
                                                    value="{{ old('instagram', $user->eventOrganizer->instagram) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="twitter" class="form-label">Twitter</label>
                                                <input type="text" class="form-control" name="twitter" id="twitter"
                                                    value="{{ old('twitter', $user->eventOrganizer->twitter) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="facebook" class="form-label">Facebook</label>
                                                <input type="text" class="form-control" name="facebook" id="facebook"
                                                    value="{{ old('facebook', $user->eventOrganizer->facebook) }}">
                                            </div>
                                            <div class="col-12">
                                                <label for="address" class="form-label">Address</label>
                                                <textarea class="form-control" name="address" id="address"
                                                    rows="3">{{ old('address', $user->eventOrganizer->address) }}</textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="city" class="form-label">City</label>
                                                <input type="text" class="form-control" name="city" id="city"
                                                    value="{{ old('city', $user->eventOrganizer->city) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="province" class="form-label">Province</label>
                                                <input type="text" class="form-control" name="province" id="province"
                                                    value="{{ old('province', $user->eventOrganizer->province) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="postal_code" class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" name="postal_code" id="postal_code"
                                                    value="{{ old('postal_code', $user->eventOrganizer->postal_code) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-3 mt-4">
                                    <a href="{{ route('user-management.user-event-organizer') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Update Event Organizer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection