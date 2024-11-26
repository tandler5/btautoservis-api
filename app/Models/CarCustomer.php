<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CarCustomer extends Model
{
    protected $table = 'wp_latepoint_customers_cars';
    protected $fillable = ['customer', 'car'];
    use HasFactory;
    use SoftDeletes;

    public function car()
    {
        return $this->belongsTo(Car::class, 'car');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer');
    }
    public function invoke(array $representation)
    {
       
    }
}
