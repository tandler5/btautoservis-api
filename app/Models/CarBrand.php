<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarBrand extends Model
{
    protected $table = 'wp_latepoint_cars_brands';
    protected $fillable = ['slug', 'name'];

    use HasFactory;
}
