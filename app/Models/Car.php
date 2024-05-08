<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Car extends Model
{
    protected $table = 'wp_latepoint_cars';
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['model', 'year', 'color','vin', 'spz','fuel','doors','seats','axle','body','performance','gear'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Nastaví výchozí hodnoty na null pro všechny atributy
        $this->attributes['gear'] = null;
        $this->attributes['axle'] = null;
        $this->attributes['body'] = null;
        $this->attributes['fuel'] = null;
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'model');
    }
    public function gear()
    {
        return $this->belongsTo(CarGear::class, 'gear');
    }
    public function fuel()
    {
        return $this->belongsTo(CarFuel::class, 'fuel');
    }
    public function body()
    {
        return $this->belongsTo(CarBody::class, 'body');
    }
    public function axle()
    { 
        return $this->belongsTo(CarAxle::class, 'axle');
    }
}
