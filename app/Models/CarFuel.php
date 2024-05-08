<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarFuel extends Model
{
    protected $table = 'wp_latepoint_cars_fuels';
    protected $fillable = [ 'name'];
}