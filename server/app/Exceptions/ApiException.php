<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class ApiException extends Exception
{
    //

    public $data;

    public function __construct(string $message, int $code = 3000, $data = [], Throwable $previous = null)
    {
        parent::__construct($message, 200, $previous);
        $this->code = $code;
        $this->data = $data;
    }

    public function render(Request $request)
    {
        return response()->json([
            'msg'  => $this->message,
            'code' => $this->code,
            'data' => recursive_str($this->data),
        ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
