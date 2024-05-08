<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarBody extends Model
{
    protected $table = 'wp_latepoint_cars_bodies';
    protected $fillable = [ 'name'];
}