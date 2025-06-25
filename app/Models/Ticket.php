<?php

namespace App\Models;

use App\Trait\HasUUID;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
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
