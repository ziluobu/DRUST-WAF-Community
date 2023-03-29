<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckPwd implements Rule
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
        $reg = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,15}$/";
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
        return '密码必须包含大小写字母、数字和特殊字符，长度在8-15之间';
    }
}
