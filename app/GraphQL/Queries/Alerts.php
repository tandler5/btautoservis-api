<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use App\Models\CarAlert;
use App\Models\Customer;
use App\Models\CarCustomer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Alerts
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user;
        $email = $user->getAttributes()['email'];
        $customerId = Customer::where('email', $email)->value('id');

        
        // Nalezení všech řádků v tabulce CarCustomer, kde je zákazník přidružen k autu
        $carCustomerRows = CarCustomer::where('customer', $customerId)->whereNull('deleted_at')->get();
        
        // dump($carCustomerRows);
        // Extrahování ID aut z těchto řádků
        $carIds = $carCustomerRows->pluck('car');


        // Získání dat o těchto autech z tabulky cars
        $cars = CarAlert::whereIn('car', $carIds)->get();

        return $cars;
    }
}		