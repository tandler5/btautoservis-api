<?php

namespace App\GraphQL\Mutations;

use App\Models\Car;
use App\Models\CarCustomer;
use App\Models\Customer;
use App\Models\CustomerNotificationTokens;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;

class SetNotificationToken
{
    public function __invoke($_, array $args,GraphQLContext $context)
    {
    
        DB::beginTransaction();

        try {

        if(!isset( $args['token'])){
            throw new CustomException(
                'No Token Provided allowed',
            );
        }
        $user = $context->user;
        $email = $user->getAttributes()['email'];
        $customerId = Customer::where('email',$email )->pluck('id')->first();

        $newToken = CustomerNotificationTokens::firstOrCreate([
            'object' => $customerId,
            'token' => $args['token'],
        ]);

        DB::commit();
        
        return $newToken;
       

        
        }catch (\Exception $e) {
            // Pokud dojde k chybě, provede se rollback transakce
            DB::rollBack();
            // Vrátí chybu nebo provede další akce podle potřeby
            throw $e;
        }
    }
}