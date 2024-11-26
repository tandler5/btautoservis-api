<?php

namespace App\Services\Car;

use App\Models\Car;
use App\Models\Customer;
use App\Models\CarCustomer;
use App\Services\Booking\BookingService;
use App\Services\Vat\VatService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;


class CarService
{

    public function __construct(
    )
    {
    }

    public function findOrCreate(
        array $carData,
        Customer $customer,
    ): ?Car
    {
        if(isset($carData['id'])){
            return Car::with('model')->findOrFail((int) $carData['id']);
        }

        return $this->upsert(
            data: $carData,
            customer: $customer,
        );
    }

    private function upsert(
        array $data,
        ?Customer $customer,
    ): ?Car
    {

       return DB::transaction(function () use ($data, $customer){

            $car = Car::create([
                'model_id'=> $data['model'],
                'year' => $data['year'],
                'performance' => $data['performance'],
                'vin' => $data['vin'],
            ]);
    
            if(isset($customer)){
                CarCustomer::create([
                    'customer' => $customer->id,
                    'car' => $car->id,
                ]);
            }
            return $car;
        });

    }
}
