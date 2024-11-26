<?php

namespace App\GraphQL\Mutations;

use App\Models\Customer;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MakeReservation
{
    public function __construct(
        private readonly OrderService $service,
    )
    {
    }

    public function __invoke($_, array $args, GraphQLContext $context)
    {
        $bookings = $args['reservationData'];

        $customerId = Auth::id();

        $customerData = isset($args['customer']) ? $args['customer'] : null;

        return DB::transaction(function () use ($customerId, $customerData, $bookings){

            $customer = $customerId ? Customer::find($customerId) : Customer::create([
                'first_name' => $customerData['firstName'],
                'last_name' => $customerData['lastName'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'status'=> 'pending_verification',
            ]);

            return $this->service->create($bookings, $customer);
        });

    }
}
