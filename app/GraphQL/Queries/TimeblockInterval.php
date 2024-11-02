<?php

namespace App\GraphQL\Queries;

use App\Models\Setting;

final class TimeblockInterval
{
    public function __invoke(): int
    {
        return Setting::where('name', 'timeblock_interval')->value('value');
    }
}
