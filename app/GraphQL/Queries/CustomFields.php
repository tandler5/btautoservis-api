<?php

namespace App\GraphQL\Queries;

use App\Models\Setting;

final class CustomFields
{
    public function __invoke()
    {
        $valueString = Setting::where('name', 'custom_fields_for_booking')->value('value');
        return json_decode($valueString);
    }
}
