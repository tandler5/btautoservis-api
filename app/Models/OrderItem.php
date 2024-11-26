<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'wp_latepoint_order_items';
    protected $fillable = ['order_id', 'variant', 'item_data', 'subtotal', 'total', 'tax_total'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
