<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarGear extends Model
{
    use HasFactory;

    protected $table = 'wp_latepoint_cars_gears';
    protected $fillable = ['name'];

    // Nastavení relace, pokud máte v databázi definovaný cizí klíč gear_id v tabulce wp_latepoint_cars
    public function cars()
    {
        return $this->hasMany(Car::class, 'gear');
    }
}