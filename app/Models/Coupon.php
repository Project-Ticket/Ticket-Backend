<?php

namespace App\Models;

use App\Trait\HasUUID;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasUUID;
    protected $guarded = ['id'];
}
