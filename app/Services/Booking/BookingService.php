<?php

namespace App\Services\Booking;


use App\Helpers\AvailableSlots as AvailableSlotsGenerator;
use App\Models\Booking;
use App\Models\BookingMeta;
use App\Models\Customer;
use App\Models\Service;
use App\Services\Car\CarService;
use App\Services\Vat\VatService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;



class BookingService
{

    public function __construct(
        private Booking $model,
        private readonly VatService $vatService,
        private readonly CarService $carService,
    )
    {
    }

    public function create(
        Service $service,
        array $data,
        int $orderId,
        Customer $customer,
    ): ?Booking
    {

        return $this->upsert(
            service: $service,
            data: $data,
            orderId: $orderId,
            customer: $customer,
        );
    }

    private function upsert(
        Service $service,
        array $data,
        int $orderId,
        Customer $customer,
    ): ?Booking
    {
        $date = $data['date']->format('Y-m-d');
        $time = $data['time'];

        $agentId = $this->isSlotAvaible($service, $date, $time);
        if(!$agentId){
            throw ValidationException::withMessages([
                'date' => ['The provided date or time is not available'],
                'time' => ['The provided date or time is not available']
            ]);
        }

        $car = $this->carService->findOrCreate($data['car'], $customer);
        
        $bookingData = [
            'customer_id' => $customer->id,
            'car_id' => $car?->id,
            'agent_id' => $agentId,
            'location_id' => 1,
            'service_id' => $service->id,
            'start_date' => $date,
            'start_time' => $time,
            'end_date' => $date,
            'end_time' => $time + $service->duration,
            'status' => 'přijato',
            'buffer_before' => $service->buffer_before,
            'buffer_after' => $service->buffer_after,
            'duration' => $service->duration,
            'total_attendees' => 1,
            // Only for booking
            'price' => $this->vatService->getServiceWithVat($service->charge_amount),
            'ip_address' => Request::ip(),
            'payment_status' => 'not_paid',
        ];

        return DB::transaction(function () use ($bookingData, $car) {
            $booking = Booking::create($bookingData);
    
            $carMeta = $booking->car->fillCarMeta();
    
            $bookingMetas = array_map(function($key, $value) use ($booking) {
                return [
                    'object_id' => $booking->id,
                    'meta_key'=> $key,
                    'meta_value'=> $value,
                ];
            }, array_keys($carMeta), $carMeta);
    
            BookingMeta::insert($bookingMetas);
    
            return $booking;
        });
    }

    private function isSlotAvaible(
        Service $service,
        string $date,
        int $time,
    ): ?int
    {   
        $slotsGenerator = new AvailableSlotsGenerator($service);

        $avaibleEmployes = $slotsGenerator->isTimeAvaible($date, $time);

        if (count($avaibleEmployes)) {
          return $avaibleEmployes[0];
        }
    }
}
