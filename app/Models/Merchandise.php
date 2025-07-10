<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchandise extends Model
{
    const STATUS_PENDING = 1;
    const STATUS_PAID = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_SHIPPED = 4;
    const STATUS_DELIVERED = 5;
    const STATUS_CANCELED = 6;
    const STATUS_TAKEITPLACE = 7;
    protected $guarded = ['id'];
}
