<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class CustomerNotificationTokens extends Model
{
    protected $table = 'wp_latepoint_customers_tokens';
    protected $fillable = ['object','token'];

    public function object()
    {
        return $this->belongsTo(Customer::class, 'object');
    }

}
