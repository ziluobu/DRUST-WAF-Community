<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckExt implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $type;

    public function __construct($type)
    {
        //
        $this->type = $type;
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
        if ($this->type == 2) {
            return $value->getClientOriginalExtension() === 'key';
        } else {
            return $value->getClientOriginalExtension() === 'crt';
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->type == 2) {
            return '请上传key文件';
        } else {
            return '请上传crt文件';
        }
    }
}
