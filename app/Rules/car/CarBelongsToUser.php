<?php

namespace App\Rules\Car;

use App\Models\CarCustomer;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CarBelongsToUser implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return CarCustomer::where('customer', Auth::id())->where('car', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Nejste vlastníkem vozidla';
    }
}
