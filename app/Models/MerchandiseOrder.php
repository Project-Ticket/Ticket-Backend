<?php

namespace App\Models;

use App\Trait\HasUUID;
use Illuminate\Database\Eloquent\Model;

class MerchandiseOrder extends Model
{
    use HasUUID;
    protected $guarded = ['id'];
}
