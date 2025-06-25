<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'fee_percentage',
        'fee_fixed',
        'minimum_fee',
        'maximum_fee',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'fee_fixed' => 'decimal:2',
        'minimum_fee' => 'decimal:2',
        'maximum_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Methods
    public function calculateFee($amount)
    {
        $percentageFee = ($amount * $this->fee_percentage) / 100;
        $totalFee = $percentageFee + $this->fee_fixed;

        // Apply minimum fee
        if ($totalFee < $this->minimum_fee) {
            $totalFee = $this->minimum_fee;
        }

        // Apply maximum fee if set
        if ($this->maximum_fee && $totalFee > $this->maximum_fee) {
            $totalFee = $this->maximum_fee;
        }

        return round($totalFee, 2);
    }

    public function isAvailable()
    {
        return $this->is_active;
    }
}
