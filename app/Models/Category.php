<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'is_active', 'sort_order'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
