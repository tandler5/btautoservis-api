<?php

namespace App\Misc;

use App\Models\Booking;

class BookedPeriod extends BlockedPeriod
{
    public int $buffer_before = 0;
    public int $buffer_after = 0;
    public int $total_attendies = 1;


    public static function create_from_booking_model(Booking $booking): BookedPeriod
    {
        return new BookedPeriod(['start_date' => $booking->start_date,
            'end_date' => $booking->end_date,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'buffer_before' => $booking->buffer_before,
            'buffer_after' => $booking->buffer_after,
            'total_attendies' => $booking->total_attendies,
            'agent_id' => $booking->agent_id,
            'service_id' => $booking->service_id,
            'location_id' => $booking->location_id
        ]);
    }

    function start_time_with_buffer(): int
    {
        return $this->start_time - $this->buffer_before;
    }

    function end_time_with_buffer(): int
    {
        return $this->end_time + $this->buffer_after;
    }

    public static function allowed_props(): array
    {
        return ['start_date',
            'end_date',
            'start_time',
            'end_time',
            'buffer_before',
            'buffer_after',
            'total_attendies',
            'service_id',
            'agent_id',
            'location_id'];
    }
}
