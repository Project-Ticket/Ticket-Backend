<?php

namespace App\Models;

use App\Trait\HasUUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Order extends Model
{
    use HasFactory, HasUUID;
    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'event_id',
        'subtotal',
        'admin_fee',
        'payment_fee',
        'discount_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'expired_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'payment_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(substr(Uuid::uuid4()->toString(), 0, 8));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket_type_id()
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
