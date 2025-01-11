<?php

namespace App\Services\Order;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Setting;
use App\Services\Booking\BookingService;
use App\Services\Vat\VatService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;


class OrderService
{

    public function __construct(
        private Booking $model,
        private readonly VatService $vatService,
        private readonly BookingService $bookingService,
    )
    {
    }

    public function create(
        array $bookings,
        Customer $customer,
    ): ?array
    {

        return $this->upsert(
            bookings: $bookings,
            customer: $customer,
        );

        return null;
    }

    private function upsert(
        array $bookings,
        Customer $customer,
    ): ?array
    {

        $services = array_unique(array_map(function ($booking) {
            return $booking['service'];
        }, $bookings));

        $servicesModels = Service::whereIn('id', $services)->get();

        $totalWithoutVats = $sum = array_reduce($bookings, function($total, $item) use ($servicesModels) {
            $service = $servicesModels->where('id', $item['service'])->first();
            return $total + $service->charge_amount;
        }, 0);

        $totalWithVats = array_reduce($bookings, function($total, $item) use ($servicesModels) {
            $service = $servicesModels->where('id', $item['service'])->first();
            
            $servicePrice = $this->vatService->getServiceWithVat($service->charge_amount);

            return $total + $servicePrice;
        }, 0);

        $totalVat = $totalWithVats -  $totalWithoutVats;

        return DB::transaction(function () use ($servicesModels, $bookings, $totalWithoutVats, $totalWithVats, $totalVat, $customer) {
            $order = Order::create([
                'subtotal' => $totalWithoutVats,
                'total' => $totalWithVats,
                'status' => 'open',
                'fulfillment_status' => 'not_fulfilled',
                'payment_status' => 'not_paid',
                'tax_total' => $totalVat,
                'ip_address' => Request::ip(),
                'customer_id' => $customer->id,
            ]);

            $allBookingsModels = [];

            foreach ($bookings as $bookingData) {
                $service = $servicesModels->where('id',$bookingData['service'])->first();

                $booking = $this->bookingService->create(
                    $service,
                    $bookingData,
                    $order->id,
                    $customer,
                );

                $bookingsData = array_merge($booking->toOrderItemData(), [
                    'car' => $booking->car->id,
                    'custom_fields' => $booking->car->fillCarMeta(),
                ]);
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'variant' => 'booking',
                    'subtotal' => $service->charge_amount,
                    'item_data' => json_encode($bookingsData),
                    'total' => $this->vatService->getServiceWithVat($service->charge_amount),
                ]);

                $booking->order_item_id = $orderItem->id;
                $booking->save();
                $allBookingsModels[] = $booking;
            }


            return [
                'bookings' => $allBookingsModels,
                'order' => $order,
            ];
        });
    }
}
