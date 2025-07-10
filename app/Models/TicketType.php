<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $guarded = ['id'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            Event::class,
            'id',           // Foreign key on events table
            'event_id',     // Foreign key on orders table
            'event_id',     // Local key on ticket_types table
            'id'            // Local key on events table
        );
    }
}
