<?php

namespace App\Utilities;

use DateTime;
use stdClass;

class Weeks
{
    public static function getWeeks($year, $month, $serviceDuration, $basePeriods, $specialPeriods, $agents, $timeBlockInterval)
    {
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $firstDayOfMonth = new DateTime("$year-$month-01");
        $lastDayOfMonth = new DateTime("$year-$month-01");
        $lastDayOfMonth->modify('last day of this month');
        $daysInMonth = $lastDayOfMonth->format('j');

        $firstDayOfWeek = $firstDayOfMonth->format('w');
        $daysFromPrevMonth = $firstDayOfWeek == 0 ? 6 : $firstDayOfWeek - 1;

        $days = [];

        if ($daysFromPrevMonth > 0) {
            $lastDayOfPrevMonth = new DateTime("$year-$month-01");
            $lastDayOfPrevMonth->modify('last day of previous month');
            $daysInPrevMonth = $lastDayOfPrevMonth->format('j');
            for ($i = $daysInPrevMonth - $daysFromPrevMonth + 1; $i <= $daysInPrevMonth; $i++) {
                $date = new DateTime("$year-" . ($month - 1) . "-$i");
                $days[] = [
                    'date' => $date,
                    'past' => $date < $today,
                    'isCurrentMonth' => false,
                    'week_day' => $date->format('N')
                ];
            }
        }

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = new DateTime("$year-$month-$i");
            $days[] = [
                'date' => $date,
                'past' => $date < $today,
                'isCurrentMonth' => true,
                'week_day' => $date->format('N')
            ];
        }

        $daysToAddFromNextMonth = count($days) <= 35 ? 35 - count($days) : 42 - count($days);
        for ($i = 1; $i <= $daysToAddFromNextMonth; $i++) {
            $nextMonth = $month + 1;
            $nextYear = $year;
            if ($nextMonth > 12) {
                $nextMonth = 1;
                $nextYear++;
            }
            $date = new DateTime("$nextYear-$nextMonth-$i");
            $days[] = [
                'date' => $date,
                'past' => $date < $today,
                'isCurrentMonth' => false,
                'week_day' => $date->format('N')
            ];
        }

        $basePeriodsMapped = $basePeriods->mapWithKeys(function ($item) {
            return [$item['week_day'] => $item];
        });

        foreach ($days as &$day) {
            $specialPeriodsForDay = $specialPeriods->filter(function ($period) use ($day) {
                return $period->custom_date == $day['date']->format('Y-m-d');
            });

            $startTime = $basePeriodsMapped[(int)$day['week_day']]->start_time;
            $endTime = $basePeriodsMapped[(int)$day['week_day']]->end_time;

            $agentsWithSlots = collect($agents)->mapWithKeys(function ($agentId) use ($timeBlockInterval, $startTime, $endTime, $serviceDuration) {
                $agent = new stdClass();
                $agent->agentId = $agentId;
                $agent->slots = [];
                $time = $startTime;
                while ($time + $timeBlockInterval <= $endTime) {
                    $slot = new stdClass();
                    $slot->start = $time;
                    $slot->end = $time + $timeBlockInterval;
                    $agent->slots[] = $slot;
                    $time += $timeBlockInterval;
                }
                return [$agentId => $agent];
            });

            $slots = [];

            foreach ($agentsWithSlots as $agentId => $agent) {
                foreach ($agent->slots as $slot) {
                    $existingSlot = null;
                    foreach ($slots as $s) {
                        if ($s->start == $slot->start && $s->end == $slot->end) {
                            $existingSlot = $s;
                            break;
                        }
                    }
                    if ($existingSlot) {
                        $existingSlot->agents[] = $agentId;
                    } else {
                        $newSlot = new stdClass();
                        $newSlot->start = $slot->start;
                        $newSlot->end = $slot->end;
                        $newSlot->agents = [$agentId];
                        $slots[] = $newSlot;
                    }
                }
            }

            $day['slots'] = $slots;

            $day['start_time'] = $startTime;
            $day['end_time'] = $endTime;

        }
        $numberOfWeeks = count($days) / 7;
        $weeks = [];
        for ($i = 0; $i < $numberOfWeeks; $i++) {
            $weeks[] = array_slice($days, $i * 7, 7);
        }

        return $weeks;
    }
}
