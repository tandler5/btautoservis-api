<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BookingMeta extends Model
{
    protected $table = 'wp_latepoint_booking_meta';
    protected $fillable = ['object_id', 'meta_key', 'meta_value'];


    public function booking()
    {
        return $this->belongsTo(Booking::class, 'object_id');
    }
}
