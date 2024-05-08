<?php

namespace App\GraphQL\Mutations;

use App\Models\Customer;
use App\Models\CarCustomer;
use App\Models\Car;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class DeleteCar
{
    public function __invoke($_, array $args, GraphQLContext $context)
    {
        $user = $context->user;
        $email = $user->getAttributes()['email'];
        $customerId = Customer::where('email', $email)->value('id');
        $customerCar = CarCustomer::where('customer', $customerId)->where('car', $args['carId'])->first();
      
        if(!$customerCar){
            throw new \Exception('Žádný záznam nenalezen', 404);
        }

        $customerCar->delete();
        
        $deletedCar = Car::find($customerCar->getAttributes()['car']);
        return $deletedCar;
    }
}
