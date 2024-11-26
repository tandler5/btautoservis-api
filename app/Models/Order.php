<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'wp_latepoint_orders';
    protected $fillable = ['subtotal', 'total', 'status', 'fulfillment_status', 'payment_status', 'ip_address', 'customer_id', 'customer_comment', 'confirmation_code', 'price_breakdown', 'coupon_code', 'coupon_discount', 'tax_total'];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
