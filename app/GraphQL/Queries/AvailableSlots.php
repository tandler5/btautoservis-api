<?php

namespace App\GraphQL\Queries;

use App\Helpers\AvailableSlots as AvailableSlotsGenerator;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Setting;
use App\Models\WorkPeriod;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class AvailableSlots
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $serviceId = $args['service_id'];
        $agentId = $args['agent_id'] ?? null;
        $locationId = $args['location_id'] ?? null;
        $month = $args['month'] ?? null;
        $page = $args['page'] ?? 1;
        $year = ($args['year'] ?? null) + ($page - 1);
        
        $disabledPeriods = [];
        $weekDaysPeriod = new \stdClass();

        $service = Service::find($serviceId);
        $serviceAgents = $service->agents->pluck('id') ?? [];

        $agentsBookings = new \stdClass();

        // Změna dotazu tak, aby zahrnoval celý rok
        $bookings = Booking::where(function ($query) use ($month, $year) {
            $query->whereYear('start_date', $year) // Zahrnout celý rok
            ->where(function ($query) use ($month) {
                $query->whereMonth('start_date', '>=', $month)
                    ->orWhereMonth('start_date', '<', $month);
            });
        })
            ->whereIn('agent_id', $serviceAgents)
            ->get()->toArray();

        $serviceAgents->each(function ($agent) use (&$agentsBookings) {
            $agentsBookings->{$agent} = new \stdClass();
        });

        foreach ($bookings as $item) {
            // Zkontrolujeme, zda existuje agent a datum v agentsBookings
            if (isset($agentsBookings->{$item['agent_id']}->{$item['start_date']})) {
                // Pokud existuje, přidáme nový booking
                $agentsBookings->{$item['agent_id']}->{$item['start_date']}[] = [
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                ];
            } else {
                // Pokud neexistuje, vytvoříme novou položku
                $agentsBookings->{$item['agent_id']}->{$item['start_date']} = [
                    [
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time'],
                    ]
                ];
            }
        }

        $timeBlockInterval = (int)Setting::where('name', 'timeblock_interval')->pluck('value')->first();

        // Změna dotazu na pracovní období tak, aby zahrnoval celý rok
        $specificDates = WorkPeriod::where(function ($query) use ($month, $year) {
            $query->whereYear('custom_date', $year)
                ->whereMonth('custom_date', '>=', $month)
                ->orWhere(function ($query) use ($month, $year) {
                    $query->whereYear('custom_date', $year + 1)
                        ->whereMonth('custom_date', '<', $month);
                });
        })
            ->orWhereNull('custom_date')
            ->get()->toArray();

        // Globální provozní doba
        foreach ($specificDates as $item) {
            if($item['custom_date'] == null && $item['agent_id'] == null && $item['service_id'] == null && $item['location_id'] == null){
                $weekDaysPeriod->{$item['week_day']} = $item;
            }
        }

        // TODO: Služba má specifickou provozní dobu
        // $serviceSpecificDates = $specificDates->filter(function ($item) use ($serviceId) {
        //     return $item->service_id == $serviceId;
        // });

        // Zaměstnanec / Zaměstnanci má/mají specifickou provozní dobu
        $specificAgentPeriods = array_filter($specificDates, function ($item) use ($agentId, $serviceAgents, $serviceId){
            return ($agentId ? $item['agent_id'] == $agentId : $serviceAgents->contains($item['agent_id'])) && $item['service_id'] == 0 || $item['service_id'] == $serviceId;
        });

        $slotsGenerator = new AvailableSlotsGenerator($timeBlockInterval, $service->duration, $weekDaysPeriod, $specificAgentPeriods, $serviceAgents, $agentsBookings);
        return [];

        $slots = $slotsGenerator->generateCalendar($year, $month);

        return $slots;
    }
}
