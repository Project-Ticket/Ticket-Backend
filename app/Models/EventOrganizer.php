<?php

namespace App\Models;

use App\Trait\HasUUID;
use Illuminate\Database\Eloquent\Model;

class EventOrganizer extends Model
{

    const STATUS_PENDING = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_INACTIVE = 3;
    const STATUS_SUSPENDED = 4;
    use HasUUID;
    protected $fillable = [
        'uuid',
        'user_id',
        'organization_name',
        'organization_slug',
        'description',
        'logo',
        'banner',
        'website',
        'instagram',
        'twitter',
        'facebook',
        'address',
        'city',
        'province',
        'postal_code',
        'contact_person',
        'contact_phone',
        'contact_email',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'verification_status',
        'verification_notes',
        'verified_at',
        'application_status',
        'application_fee',
        'security_deposit',
        'required_documents',
        'uploaded_documents',
        'rejection_reason',
        'application_submitted_at',
        'reviewed_by',
        'reviewed_at',
        'status',
        'payment_reference',
        'payment_status',
        'paid_at',
        'payment_method',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'uploaded_documents' => 'array',
        'verified_at' => 'datetime',
        'application_submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $hidden = [
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'verification_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id', 'id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }
}
