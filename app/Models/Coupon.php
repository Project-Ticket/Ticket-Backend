<?php

namespace App\Models;

use App\Trait\HasUUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasUUID, HasFactory;

    protected $fillable = [
        'uuid',
        'organizer_id',
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'usage_limit_per_user',
        'valid_from',
        'valid_until',
        'applicable_to',
        'applicable_events',
        'applicable_merchandise',
        'is_active'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'applicable_events' => 'array',
        'applicable_merchandise' => 'array',
        'is_active' => 'boolean'
    ];

    public function eventOrganizer()
    {
        return $this->belongsTo(EventOrganizer::class, 'organizer_id');
    }
}
