<?php

namespace App\GraphQL\Queries;

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Service;
use App\Models\WorkPeriod;
use Carbon\Carbon;

class AvailableSlotsQuery
{
    public function __invoke($_, array $args)
    {
        $serviceId = $args['service_id'];
        $agentId = $args['agent_id'] ?? null;
        $locationId = $args['location_id'];
        $month = $args['month'];
        $year = $args['year'];

        // 1. Získání agentů poskytujících danou službu v dané lokalitě
        $agentsQuery = Agent::whereHas('services', function ($query) use ($serviceId) {
            $query->where('service_id', $serviceId);
        });

        if ($agentId) {
            $agentsQuery->where('id', $agentId);
        }

        if ($locationId) {
            $agentsQuery->where('location_id', $locationId);
        }

        $agents = $agentsQuery->get();

        // 2. Získání otevírací doby pro dané agenty a měsíc
        $agentIds = $agents->pluck('id');

        $workPeriods = WorkPeriod::whereIn('agent_id', $agentIds)
            ->where(function ($query) use ($month, $year) {
                $query->whereNull('custom_date')
                    ->orWhereBetween('custom_date', ["$year-$month-01", "$year-$month-31"]);
            })
            ->where(function ($query) use ($serviceId) {
                $query->whereNull('service_id')
                    ->orWhere('service_id', $serviceId);
            })
            ->get();

        // 3. Získání rezervací pro dané agenty a měsíc
        $bookings = Booking::whereIn('agent_id', $agentIds)
            ->whereBetween('start_date', ["$year-$month-01", "$year-$month-31"])
            ->get();

        // 4. Získání délky služby
        $service = Service::find($serviceId);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        $duration = $service->duration;

        // 5. Vytvoření časových slotů pro každý den v měsíci
        $availableSlots = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day)->toDateString();

            foreach ($agents as $agent) {
                $dailyWorkPeriods = $workPeriods->filter(function ($workPeriod) use ($date, $agent) {
                    return (is_null($workPeriod->custom_date) || $workPeriod->custom_date == $date) && $workPeriod->agent_id == $agent->id;
                });

                $dailyBookings = $bookings->filter(function ($booking) use ($date, $agent) {
                    return $booking->start_date <= $date && $booking->end_date >= $date && $booking->agent_id == $agent->id;
                });

                foreach ($dailyWorkPeriods as $workPeriod) {
                    $start = $workPeriod->start_time;
                    $end = $workPeriod->end_time;
                    while ($start + $duration <= $end) {
                        // Kontrola, zda tento časový slot již existuje pro dané datum, začátek a konec
                        $slotKey = $date . '_' . $start . '_' . ($start + $duration);
                        if (!isset($availableSlots[$slotKey])) {
                            // Pokud neexistuje, vytvoříme nový časový slot a přidáme ID agenta
                            $availableSlots[$slotKey] = [
                                'date' => $date,
                                'start_time' => $start,
                                'end_time' => $start + $duration,
                                'agents' => [],
                            ];
                        }

                        // Přidání ID agenta pouze pokud ještě není v poli
                        if (!in_array($agent->id, $availableSlots[$slotKey]['agents'])) {
                            $availableSlots[$slotKey]['agents'][] = $agent->id;
                        }

                        $start += $duration;
                    }
                }
            }
        }

        // Přeměna asociačního pole na indexované pole pro výstup
        $availableSlots = array_values($availableSlots);

        return response()->json($availableSlots);
    }
}
