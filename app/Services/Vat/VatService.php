<?php

namespace App\Services\Vat;

use App\Models\Setting;

class VatService
{
    private static $instance = null;
    private $vats;

    private function __construct()
    {
        $vatsString = Setting::where('name', 'taxes')->value('value');
        $this->vats = json_decode($vatsString, true);
    }

    public static function getInstance(): VatService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getServiceWithVat(int $serviceChargeAmount): int
    {
        $servicePrice = $serviceChargeAmount;

        foreach ($this->vats as $vat) {
            if ($vat['type'] === 'percentage') {
                $servicePrice = round($servicePrice * (1 + $vat['value'] / 100));
            } else {
                $servicePrice = $servicePrice + $vat['value'];
            }
        }

        return $servicePrice;
    }
}