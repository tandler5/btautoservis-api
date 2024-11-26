<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $table = 'wp_latepoint_cars_models';
    protected $fillable = ['slug', 'name', 'brand_id'];

    use HasFactory;

    public function brand()
    {
        return $this->belongsTo(CarBrand::class);
    }
}
