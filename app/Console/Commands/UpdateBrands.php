<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CarBrand;

class UpdateBrands extends Command
{
    protected $signature = 'update:brands';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $url = 'https://cdn.imagin.studio/getCarListing?customer=flexcar';
        $response = file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data !== null) {
                foreach ($data['make'] as $brand) {
                    // Zkontrolujte, zda je značka již přítomna v tabulce
                    if (!CarBrand::where('slug', $brand)->exists()) {
                        // Vložení značky, pokud ještě neexistuje
                        CarBrand::create(['slug' => $brand, 'name' => ucfirst($brand)]);
                    }
                }
                $this->info("a");
            } else {
                $this->info("Chyba při dekódování JSON.");
            }
        } else {
            $this->info("Chyba při provádění HTTP požadavku.");
        }
    }
}
