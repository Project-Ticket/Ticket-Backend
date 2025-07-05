<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    // protected $guarded = ['id'];

    protected $fillable = [
        'organizer_id',
        'category_id',
        'title',
        'slug',
        'description',
        'terms_conditions',
        'banner_image',
        'gallery_images',
        'type',
        'venue_name',
        'venue_address',
        'venue_city',
        'venue_province',
        'venue_latitude',
        'venue_longitude',
        'online_platform',
        'online_link',
        'start_datetime',
        'end_datetime',
        'registration_start',
        'registration_end',
        'min_age',
        'max_age',
        'status',
        'is_featured',
        'views_count',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'is_featured' => 'boolean',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(EventOrganizer::class, 'organizer_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'event_tags')->withTimestamps();
    }
    public function registrations()
    {
        return $this->hasManyThrough(Ticket::class, TicketType::class, 'event_id', 'ticket_type_id');
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class, 'event_id');
    }
}
