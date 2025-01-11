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

        $service = Service::find($serviceId);
        
        $slotsGenerator = new AvailableSlotsGenerator($service);

        $slots = $slotsGenerator->generateCalendar($year, $month, $agentId);

        return $slots;
    }
}
