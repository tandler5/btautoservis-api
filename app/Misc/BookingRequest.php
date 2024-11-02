<?php


namespace App\Misc;

class BookingRequest
{
    public ?string $start_date;
    public ?string $end_date;
    public ?int $start_time = 0;
    public ?int $end_time = 0;
    public ?int $duration = 0;
    public ?int $buffer_before = 0;
    public ?int $buffer_after = 0;
    public ?int $total_attendies = 1;
    public ?int $service_id = 0;
    public ?int $agent_id = 0;
    public ?int $location_id = 0;

    function __construct($args = [])
    {
        $allowed_props = self::allowed_props();
        foreach ($args as $key => $arg) {
            if (in_array($key, $allowed_props)) $this->$key = $arg;
        }
    }

    public function get_start_time_with_buffer(): int
    {
        return $this->start_time - $this->buffer_before;
    }

    public function get_end_time_with_buffer(): int
    {
        return $this->end_time + $this->buffer_after;
    }


    public static function allowed_props(): array
    {
        return ['start_date',
            'end_date',
            'start_time',
            'end_time',
            'duration',
            'buffer_before',
            'buffer_after',
            'total_attendies',
            'agent_id',
            'service_id',
            'location_id'];
    }
}
