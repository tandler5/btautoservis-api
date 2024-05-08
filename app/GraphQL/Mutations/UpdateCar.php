<?php

namespace App\GraphQL\Mutations;

use App\Models\Car;
use App\Models\CarCustomer;
use App\Models\Customer;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;

class UpdateCar
{
    public function __invoke($_, array $args,GraphQLContext $context)
    {
    
        DB::beginTransaction();

        try {

        $user = $context->user;
        $email = $user->getAttributes()['email'];
        $customerId = Customer::where('email',$email )->pluck('id')->first();
        $customerHasCar = CarCustomer::where( ['customer' => $customerId, 'car' => $args['carId']])->exists();
        if($customerHasCar === true){
            $car = Car::find($args['carId']);
            $car->fill($args);
            $car->save();
            DB::commit();
            return $car;
        }
      
        throw new CustomException(
            'Not allowed',
            'You are not authorized to modify this car'
        );
        
        }catch (\Exception $e) {
            // Pokud dojde k chybě, provede se rollback transakce
            DB::rollBack();
            // Vrátí chybu nebo provede další akce podle potřeby
            throw $e;
        }
    }
}