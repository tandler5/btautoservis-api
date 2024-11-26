<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'wp_latepoint_bookings';

    protected $fillable = ['car_id','start_date', 'end_date', 'start_time','end_time','buffer_before', 'buffer_after', 'duration', 'price', 'status', 'customer_id', 'service_id', 'agent_id', 'location_id', 'total_attendies', 'payment_method', 'payment_portion', 'ip_address', 'coupon_code', 'customer_comment', 'coupon_discount', 'booking_code', 'subtotal', 'payment_status', 'order_item_id', 'total_attendees'];

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function toOrderItemData()
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'agent_id' => $this->agent_id,
            'location_id' => $this->location_id,
            'service_id' => $this->service_id,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'end_date' => $this->end_date,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'buffer_before' => $this->buffer_before,
            'buffer_after' => $this->buffer_after,
            'duration' => $this->duration,
            'total_attendees' => $this->total_attendees
        ];
    }
}
