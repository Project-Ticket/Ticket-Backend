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
                Register New Event Organizer
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
                        <form action="{{ route('user-management.user-event-organizer.store') }}" method="POST"
                            enctype="multipart/form-data" id="organizerForm" novalidate>
                            @csrf

                            <div class="p-4">
                                <!-- Personal Information Section -->
                                <div class="section-card card">
                                    <div class="card-body">
                                        <div class="section-header">
                                            <div class="section-icon">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h5 class="section-title">Personal Information</h5>
                                                <p class="section-subtitle">Basic personal details for the account holder
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="user_name" class="form-label">
                                                    Full Name <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="user[name]" id="user_name"
                                                    placeholder="Enter full name" required value="{{ old('user.name') }}">
                                                <div class="form-text">This will be used as the primary account name</div>
                                                @error('user.name')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="user_email" class="form-label">
                                                    Email Address <span class="required">*</span>
                                                </label>
                                                <input type="email" class="form-control" name="user[email]" id="user_email"
                                                    placeholder="Enter email address" required
                                                    value="{{ old('user.email') }}">
                                                <div class="form-text">Used for login and communication</div>
                                                @error('user.email')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="user_password" class="form-label">
                                                    Password <span class="required">*</span>
                                                </label>
                                                <input type="password" class="form-control" name="user[password]"
                                                    id="user_password" placeholder="Enter secure password" required>
                                                <div class="form-text">Minimum 8 characters with mixed case and numbers
                                                </div>
                                                @error('user.password')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="user_phone" class="form-label">Phone Number</label>
                                                <input type="text" class="form-control" name="user[phone]" id="user_phone"
                                                    placeholder="e.g., +62 812 3456 7890" value="{{ old('user.phone') }}">
                                                @error('user.phone')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="user_birth_date" class="form-label">Birth Date</label>
                                                <input type="date" class="form-control" name="user[birth_date]"
                                                    id="user_birth_date" value="{{ old('user.birth_date') }}">
                                                @error('user.birth_date')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="user_gender" class="form-label">Gender</label>
                                                <select name="user[gender]" id="user_gender" class="form-select">
                                                    <option value="">Choose gender...</option>
                                                    <option value="male" {{ old('user.gender') == 'male' ? 'selected' : '' }}>
                                                        Male</option>
                                                    <option value="female" {{ old('user.gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                    <option value="other" {{ old('user.gender') == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('user.gender')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label for="user_address" class="form-label">Personal Address</label>
                                                <textarea class="form-control" name="user[address]" id="user_address"
                                                    rows="3"
                                                    placeholder="Enter complete address">{{ old('user.address') }}</textarea>
                                                @error('user.address')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="user_province" class="form-label">Province</label>
                                                <select name="user[province]" class="form-select mb-2" id="province">
                                                    <option value="">Pilih Provinsi</option>
                                                </select>
                                                @error('user.province')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="user_city" class="form-label">City</label>
                                                <select name="user[city]" class="form-select mb-2" id="regency" disabled>
                                                    <option value="">Pilih Kabupaten/Kota</option>
                                                </select>
                                                @error('user.city')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="user_postal_code" class="form-label">Postal Code</label>
                                                <input type="text" class="form-control" name="user[postal_code]"
                                                    id="user_postal_code" placeholder="Enter postal code"
                                                    value="{{ old('user.postal_code') }}">
                                                @error('user.postal_code')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Organization Information Section -->
                                <div class="section-card card">
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
                                            <div class="col-md-12">
                                                <label for="organization_name" class="form-label">
                                                    Organization Name <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="organization_name"
                                                    id="organization_name" placeholder="Enter organization name" required
                                                    value="{{ old('organization_name') }}">
                                                @error('organization_name')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label for="description" class="form-label">Organization Description</label>
                                                <textarea class="form-control" name="description" id="description" rows="4"
                                                    placeholder="Describe your organization, its mission, and activities">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Media & Branding Section -->
                                <div class="section-card card">
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
                                                <label for="logo" class="form-label">Organization Logo</label>
                                                <div class="file-upload-area"
                                                    onclick="document.getElementById('logo').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                                                    <h6 class="mb-2">Click to upload logo</h6>
                                                    <small class="text-muted">PNG, JPG up to 2MB</small>
                                                    <input type="file" class="d-none" name="logo" id="logo"
                                                        accept="image/*">
                                                </div>
                                                <div id="logo-preview"></div>
                                                @error('logo')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="banner" class="form-label">Organization Banner</label>
                                                <div class="file-upload-area"
                                                    onclick="document.getElementById('banner').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                                                    <h6 class="mb-2">Click to upload banner</h6>
                                                    <small class="text-muted">PNG, JPG up to 5MB</small>
                                                    <input type="file" class="d-none" name="banner" id="banner"
                                                        accept="image/*">
                                                </div>
                                                <div id="banner-preview"></div>
                                                @error('banner')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information Section -->
                                <div class="section-card card">
                                    <div class="card-body">
                                        <div class="section-header">
                                            <div class="section-icon">
                                                <i class="fas fa-address-book"></i>
                                            </div>
                                            <div>
                                                <h5 class="section-title">Contact Information</h5>
                                                <p class="section-subtitle">Business contact details and social media</p>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="contact_person" class="form-label">
                                                    Contact Person <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="contact_person"
                                                    id="contact_person" placeholder="Person in charge" required
                                                    value="{{ old('contact_person') }}">
                                                @error('contact_person')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="contact_phone" class="form-label">
                                                    Contact Phone <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="contact_phone"
                                                    id="contact_phone" placeholder="Business phone number" required
                                                    value="{{ old('contact_phone') }}">
                                                @error('contact_phone')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="contact_email" class="form-label">
                                                    Contact Email <span class="required">*</span>
                                                </label>
                                                <input type="email" class="form-control" name="contact_email"
                                                    id="contact_email" placeholder="Business email address" required
                                                    value="{{ old('contact_email') }}">
                                                @error('contact_email')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="website" class="form-label">Website</label>
                                                <input type="url" class="form-control" name="website" id="website"
                                                    placeholder="https://yourwebsite.com" value="{{ old('website') }}">
                                                @error('website')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <hr class="my-4">
                                        <h6 class="mb-3">
                                            <i class="fab fa-instagram me-2 text-primary"></i>
                                            Social Media Links
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="instagram" class="form-label">Instagram</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                                    <input type="text" class="form-control" name="instagram" id="instagram"
                                                        placeholder="username" value="{{ old('instagram') }}">
                                                </div>
                                                @error('instagram')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="twitter" class="form-label">Twitter</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                                    <input type="text" class="form-control" name="twitter" id="twitter"
                                                        placeholder="username" value="{{ old('twitter') }}">
                                                </div>
                                                @error('twitter')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="facebook" class="form-label">Facebook</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                                                    <input type="text" class="form-control" name="facebook" id="facebook"
                                                        placeholder="Page name or URL" value="{{ old('facebook') }}">
                                                </div>
                                                @error('facebook')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address & Banking Section -->
                                <div class="section-card card">
                                    <div class="card-body">
                                        <div class="section-header">
                                            <div class="section-icon">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            <div>
                                                <h5 class="section-title">Business Address</h5>
                                                <p class="section-subtitle">Complete business location information</p>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-12">
                                                <label for="address" class="form-label">
                                                    Business Address <span class="required">*</span>
                                                </label>
                                                <textarea class="form-control" name="address" id="address" rows="3"
                                                    placeholder="Complete business address"
                                                    required>{{ old('address') }}</textarea>
                                                @error('address')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="city" class="form-label">
                                                    City <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="city" id="city"
                                                    placeholder="Business city" required value="{{ old('city') }}">
                                                @error('city')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="province" class="form-label">
                                                    Province <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="province" id="province"
                                                    placeholder="Business province" required value="{{ old('province') }}">
                                                @error('province')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="postal_code" class="form-label">
                                                    Postal Code <span class="required">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="postal_code" id="postal_code"
                                                    placeholder="Business postal code" required
                                                    value="{{ old('postal_code') }}">
                                                @error('postal_code')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <hr class="my-4">
                                        <div class="section-header">
                                            <div class="section-icon">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <div>
                                                <h6 class="section-title">Banking Information</h6>
                                                <p class="section-subtitle">Optional - Can be added later</p>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="bank_name" class="form-label">Bank Name</label>
                                                <input type="text" class="form-control" name="bank_name" id="bank_name"
                                                    placeholder="e.g., Bank Mandiri" value="{{ old('bank_name') }}">
                                                @error('bank_name')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="bank_account_number" class="form-label">Account Number</label>
                                                <input type="text" class="form-control" name="bank_account_number"
                                                    id="bank_account_number" placeholder="Account number"
                                                    value="{{ old('bank_account_number') }}">
                                                @error('bank_account_number')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="bank_account_name" class="form-label">Account Name</label>
                                                <input type="text" class="form-control" name="bank_account_name"
                                                    id="bank_account_name" placeholder="Account holder name"
                                                    value="{{ old('bank_account_name') }}">
                                                @error('bank_account_name')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Banking information is used for payment processing and can be added later
                                            through the profile settings.
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-end gap-3 mt-4">
                                    <a href="{{ url('event-organizers.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times me-2"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-check me-2"></i>
                                        Create Event Organizer
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
@push('scripts')
    <script>
        $('#organizerForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = $('#submitBtn');
            const originalBtnHtml = submitBtn.html();

            // Reset error styling
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.text-danger.small.mt-1').remove();

            // Buat FormData untuk file upload
            const formData = new FormData(this);

            // Disable tombol dan tampilkan loading
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                success: function (response) {
                    Swal.fire({
                        title: 'Success',
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('user-management.user-event-organizer') }}";
                    });
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function (key, messages) {
                            const input = form.find(`[name="${key}"], [name="${key.replace(/\./g, '\\.')}"]`);
                            if (input.length > 0) {
                                input.addClass('is-invalid');
                                input.after(`<div class="text-danger small mt-1">${messages[0]}</div>`);
                            }
                        });
                        Swal.fire('Validation Error', 'Please check your input.', 'warning');
                    } else {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                complete: function () {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });

        // Slug Generator
        $('#organization_name').on('input', function () {
            const slug = $(this).val()
                .toLowerCase()
                .replace(/\s+/g, '-')       // Replace spaces with -
                .replace(/[^a-z0-9\-]/g, '') // Remove non-alphanumeric
                .replace(/\-+/g, '-')        // Replace multiple - with single -
                .replace(/^-+|-+$/g, '');    // Trim - from start & end
            $('#organization_slug').val(slug);
        });

        // Preview Logo & Banner
        function previewImage(input, previewId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $(`#${previewId}`).html(
                        `<img src="${e.target.result}" class="img-fluid mt-2 rounded border" style="max-height: 200px;">`
                    );
                }
                reader.readAsDataURL(file);
            }
        }

        $('#logo').on('change', function () {
            previewImage(this, 'logo-preview');
        });

        $('#banner').on('change', function () {
            previewImage(this, 'banner-preview');
        });

        // Opsional: toggle password (jika pakai icon toggle)
        function togglePassword() {
            const input = document.getElementById('user_password');
            const icon = document.getElementById('toggleIcon');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
@endpush