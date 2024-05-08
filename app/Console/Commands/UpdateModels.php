<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Support\Facades\Http;

class UpdateModels extends Command
{
    protected $signature = 'update:models';

    protected $description = 'Update models based on data from URL.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Získání všech značek z tabulky wp_latepoint_cars_brands
        $brands = CarBrand::all();

        // Procházení všech značek
        foreach ($brands as $brand) {
            // Sestavení URL s proměnnou slug
            $url = "https://cdn.imagin.studio/getCarListing?customer=flexcar&make={$brand->slug}";

            // Provedení HTTP GET požadavku na danou URL
            $response = file_get_contents($url);

            // Dekódování JSON odpovědi na pole
            $data = json_decode($response, true);

            // Pokud byla odpověď dekódována úspěšně a pole modelFamily existuje
            if ($data !== null && isset($data['modelFamily'])) {
                // Procházení pole modelFamily
                foreach ($data['modelFamily'] as $modelName) {
                    // Příprava dat pro vložení do tabulky wp_latepoint_cars_models
                    $modelSlug = $this->generateSlug($modelName);
                    $modelName = ucwords(str_replace('-', ' ', $modelName));

                    // Aktualizace záznamu v tabulce wp_latepoint_cars_models
                    CarModel::updateOrCreate(
                        ['slug' => $modelSlug, 'brand' => $brand->id],
                        ['name' => $modelName]
                    );
                }
            }
        }

        $this->info('Models updated successfully.');
    }

    // Metoda pro generování slugu z názvu modelu
    private function generateSlug($name)
    {
        $slug = strtolower(str_replace(' ', '-', $name));
        return $slug;
    }
}
