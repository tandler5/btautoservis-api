<?php

namespace App\GraphQL\Mutations;

use App\Models\Car;
use App\Models\CarCustomer;

class CreateCar
{
    public function __invoke($_, array $args)
    {
        $car = new Car();
        $car->fill($args);
      
        $car->save();
        var_dump($_);
        $carCustomer = new CarCustomer();
        $carCustomer->car = $car->id;
        $carCustomer->customer = 109;
        $carCustomer->save();

        return $car;
    }
}
	