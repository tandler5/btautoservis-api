<?php

namespace App\Http\Controllers;

use App\Models\CustomerNotificationTokens;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class InvoiceController extends Controller
{

    // Routy jsou nasměrovány na tuto funkci a podobné
    public function getInvoice(Request $request)
    {

        $customer = new Buyer([
            'name'          => 'John Doe',
            'custom_fields' => [
                'email' => 'test@example.com',
            ],
        ]);

        $item = InvoiceItem::make('Service 1')->pricePerUnit(2);

        $invoice = Invoice::make()
            ->buyer($customer)
            ->discountByPercent(10)
            ->taxRate(15)
            ->shipping(1.99)
            ->addItem($item);

        return $invoice->stream();
        return "a";
    }
}
