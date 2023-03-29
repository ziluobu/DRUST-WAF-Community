<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckRuleMsg implements Rule
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
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        $reg = "/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]+$/u";
        if (preg_match($reg, $value)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '描述不能含有符号';
    }
}
