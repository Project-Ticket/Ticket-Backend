<?php

namespace App\Models;

use App\Trait\HasUUID;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    const STATUS_INACTIVE = 1;
    const STATUS_PENDING_PAYMENT = 2;
    const STATUS_ACTIVE = 3;
    const STATUS_USED = 4;
    const STATUS_CANCELLED = 5;
    const STATUS_TRANSFERED = 6;
    use HasUUID;

    protected $guarded = ['id'];

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
