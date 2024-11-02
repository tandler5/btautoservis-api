<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

namespace App\Helpers;

use App\Misc\BookingResource;
use App\Misc\Filter;
use App\Misc\TimePeriod;

class OsResourceHelper
{

    /**
     * @param \LatePoint\Misc\BookingRequest $booking_request
     * @param DateTime $date_from
     * @param DateTime|null $date_to
     * @param array $settings
     *
     * @return array
     *
     *
     * Returns an array of work periods, grouped by days that were requested in the filter.
     * example: ['2022-02-24' => [], '2022-02-25' => [], ...]
     *
     *  | Agent   | Service | Location | Date          |       Hours       | Weight
     *  | -----------------------------------------------------------------------------
     *  | 1       | 1       | 1        | 2022-01-15    |   7:00  - 18:00   |  7
     *  | 1       | 0       | 1        | 2022-01-15    |   8:00  - 14:00   |  6
     *  | 1       | 0       | 0        | 2022-01-15    |   8:00  - 14:00   |  5
     *  | 0       | 0       | 1        | 2022-01-15    |   11:00 - 12:00   |  5
     *  | 0       | 0       | 0        | 2022-01-15    |   11:00 - 12:00   |  4
     *  | 1       | 0       | 1        | NULL          |   0:00  - 0:00    |  2
     *  | 1       | 0       | 0        | NULL          |   9:00  - 12:00   |  1
     *  | 0       | 0       | 0        | NULL          |   11:00 - 17:00   |  0
     *
     */
    public static function get_resources_grouped_by_day($booking_request, \DateTime $date_from, \DateTime $date_to = null, array $settings = []): array
    {
        dump($booking_request);
        return [];
    }

    /**
	 *
	 * @return array
	 */
	public static function get_ordered_booking_slots_from_resources( array $resources ): array {
		$booking_slots = [];
		foreach ( $resources as $resource ) {
			$booking_slots = array_merge( $booking_slots, $resource->slots );
		}

		usort( $booking_slots, function ( $first, $second ) {
			return $first->start_time <=> $second->start_time;
		} );

		if ( count( $resources ) > 1 ) {
			$squashed_booking_slots = [];
			$last_added_slot        = false;
			foreach ( $booking_slots as $booking_slot ) {
				if ( $last_added_slot && ( $last_added_slot->start_time == $booking_slot->start_time ) ) {
					if ( $last_added_slot->available_capacity() < $booking_slot->available_capacity() ) {
						$squashed_booking_slots[ count( $squashed_booking_slots ) - 1 ] = $booking_slot;
						$last_added_slot                                                = $booking_slot;
					}
				} else {
					$squashed_booking_slots[] = $booking_slot;
					$last_added_slot          = $booking_slot;
				}
			}
			$booking_slots = $squashed_booking_slots;
		}

		return $booking_slots;
	}

    public static function get_work_boundaries_for_resources( $resources ): TimePeriod {
		$times = [];
		foreach ( $resources as $resource ) {
			foreach ( $resource->work_time_periods as $work_time_period ) {
				$times[] = $work_time_period->start_time;
				$times[] = $work_time_period->end_time;
			}
			foreach ( $resource->booked_time_periods as $booked_time_period ) {
				if ( $booked_time_period->start_date == $booked_time_period->end_date ) {
					// same day event
					$times[] = $booked_time_period->start_time;
					$times[] = $booked_time_period->end_time;
				} else {
					// event spans mutiple days, expand boundaries to a full day
					$times[] = 0;
					$times[] = 24 * 60;
				}
			}
		}
		if ( $times ) {
			$boundary_time_period = new TimePeriod( [
				'start_time' => min( $times ),
				'end_time'   => max( $times )
			] );
		} else {
			$boundary_time_period = new TimePeriod( [ 'start_time' => 0, 'end_time' => 0 ] );
		}

		return $boundary_time_period;
	}


    /**
     * @param array $groups_of_resources
     *
     * @return \LatePoint\Misc\TimePeriod
     */
    public static function get_work_boundaries_for_groups_of_resources(array $groups_of_resources): \LatePoint\Misc\TimePeriod
    {
        $times = [];
        foreach ($groups_of_resources as $resources) {
            $time_period = self::get_work_boundaries_for_resources($resources);
            if ($time_period->start_time || $time_period->end_time) {
                $times[] = $time_period->start_time;
                $times[] = $time_period->end_time;
            }
        }
        if ($times) {
            $boundary_time_period = new \LatePoint\Misc\TimePeriod([
                'start_time' => min($times),
                'end_time' => max($times)
            ]);
        } else {
            $boundary_time_period = new \LatePoint\Misc\TimePeriod(['start_time' => 0, 'end_time' => 0]);
        }

        return $boundary_time_period;
    }


}
