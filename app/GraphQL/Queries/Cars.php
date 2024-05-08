<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use App\Models\Car;
use App\Models\Customer;
use App\Models\CarCustomer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Cars
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user;
        $email = $user->getAttributes()['email'];
        $customerId = Customer::where('email', $email)->value('id');
        
        // Nalezení všech řádků v tabulce CarCustomer, kde je zákazník přidružen k autu
        $carIds = CarCustomer::where('customer', $customerId)->whereNull('deleted_at')->pluck('car');

        return Car::whereIn('id', $carIds)->get();
    }
}		