<?php

namespace App\GraphQL\Mutations;

use App\Models\Car;
use App\Models\CarCustomer;
use App\Models\Customer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Support\Facades\DB;


class CreateCar
{
    
    public function __invoke($_, array $args,GraphQLContext $context)
    {
        DB::beginTransaction();

        try {
        $car = new Car();
        $car->fill($args);
        $car->save();

        $user = $context->user;
        $email = $user->getAttributes()['email'];
        $customerId = Customer::where('email',$email )->pluck('id')->first();
    
        $carCustomer = new CarCustomer();
        $carCustomer->car = $car->id;
        $carCustomer->customer = $customerId;
        $carCustomer->save();
        
        DB::commit();
        }catch (\Exception $e) {
            // Pokud dojde k chybě, provede se rollback transakce
            DB::rollBack();

            // Vrátí chybu nebo provede další akce podle potřeby
            throw $e;
        }

        return $car;
    }
}
	