<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'is_active'];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_tags')->withTimestamps();
    }
}
