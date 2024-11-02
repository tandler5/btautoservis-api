<?php

namespace App\GraphQL\Queries;

use App\Models\Setting;

final class Taxes
{
    public function __invoke()
    {
        $taxesString = Setting::where('name', 'taxes')->value('value');
        $taxesJson = json_decode($taxesString);
        return get_object_vars($taxesJson);
    }
}
