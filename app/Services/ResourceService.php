<?php

namespace App\Services;

use App\Models\Resource;
use App\Utilities\TimePeriod;
use Carbon\Carbon;

class ResourceService
{
    public function get_resources_grouped_by_day($booking_request, Carbon $date_from, Carbon $date_to = null, array $settings = []): array
    {
        // Zde by měla být logika pro získání zdrojů seskupených podle dne
        // Tento kód je pouze ilustrativní a je možné, že bude potřeba další úpravy a testování
        $resources = Resource::where('booking_request', $booking_request)
            ->whereBetween('date', [$date_from, $date_to])
            ->get();

        return $resources->groupBy(function ($resource) {
            return Carbon::parse($resource->date)->format('Y-m-d');
        });
    }

    /**
     * @param BookingResBookingResourceource[] $resources
     * @return array
     */
    public static function get_ordered_booking_slots_from_resources(array $resources): array{
        $booking_slots = [];
        foreach($resources as $resource){
            $booking_slots = array_merge($booking_slots, $resource->slots);
        }

        usort($booking_slots, function($first,$second){
            return $first->start_time <=> $second->start_time;
        });

        if(count($resources) > 1){
            $squashed_booking_slots = [];
            $last_added_slot = false;
            foreach($booking_slots as $booking_slot){
                if($last_added_slot && ($last_added_slot->start_time == $booking_slot->start_time)){
                    if($last_added_slot->available_capacity() < $booking_slot->available_capacity()){
                        $squashed_booking_slots[count($squashed_booking_slots) - 1] = $booking_slot;
                        $last_added_slot = $booking_slot;
                    }
                }else{
                    $squashed_booking_slots[] = $booking_slot;
                    $last_added_slot = $booking_slot;
                }
            }
            $booking_slots = $squashed_booking_slots;
        }

        return $booking_slots;
    }

    public static function get_work_boundaries_for_resources($resources): TimePeriod{
        $times = [];
        foreach($resources as $resource){
            foreach($resource->work_time_periods as $work_time_period){
                $times[] = $work_time_period->start_time;
                $times[] = $work_time_period->end_time;
            }
            foreach($resource->booked_time_periods as $booked_time_period){
                if($booked_time_period->start_date == $booked_time_period->end_date){
                    // same day event
                    $times[] = $booked_time_period->start_time;
                    $times[] = $booked_time_period->end_time;
                }else{
                    // event spans mutiple days, expand boundaries to a full day
                    $times[] = 0;
                    $times[] = 24*60;
                }
            }
        }
        if($times){
            $boundary_time_period = new TimePeriod(['start_time' => min($times), 'end_time' => max($times)]);
        }else{
            $boundary_time_period = new TimePeriod(['start_time' => 0, 'end_time' => 0]);
        }
        return $boundary_time_period;
    }

    public function get_work_boundaries_for_groups_of_resources(array $groups_of_resources): array
    {
// Zde by měla být logika pro získání pracovních hranic pro skupiny zdrojů
// Tento kód je pouze ilustrativní a je možné, že bude potřeba další úpravy a testování
        $times = [];
        foreach ($groups_of_resources as $resources) {
            $boundary_time_period = $this->get_work_boundaries_for_resources($resources);
            $times[] = $boundary_time_period['start_time'];
            $times[] = $boundary_time_period['end_time'];
        }

        return ['start_time' => min($times), 'end_time' => max($times)];
    }
}
