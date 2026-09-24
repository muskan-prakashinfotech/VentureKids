<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DomainValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Ensure the value does not contain original domain
        if (strpos($value, config("tenancy.sub_domain")) !== false) {
            return false;
        }

        // Ensure the value is a valid subdomain
        return preg_match('/^[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid subdomain and should not contain the base domain.';
    }
}
