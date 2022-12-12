<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
        ApiException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e)
    {
        $e = $this->mapException($e);

        if ($this->shouldntReport($e)) {
            return;
        }
        //todo 注释此处可以关闭系统日志
        /*if(config('app.debug')){
            parent::report($e);
        }*/
        Log::error($this->parseLog($e));
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if (!$e instanceof ApiException) {
            $newException = $this->prepareException($this->mapException($e));
            if ($newException instanceof HttpException) {
                $StatusCode = $newException->getStatusCode();
            } else {
                $StatusCode = 500;
            }
            if (config('app.debug')) {
                $msg = $e->getMessage();
            } elseif (\Str::startsWith($StatusCode, '4')) {
                $msg = $StatusCode;
            } else {
                $msg = '系统异常,请联系管理员';
            }
            return response()->json([
                'msg'  => $msg,
                'code' => $StatusCode,
                'data' => []
            ], $StatusCode)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }
        return parent::render($request, $e);
    }

    /**
     * 解析日志
     * @access protected
     * @param  array  $info  日志信息
     * @return string
     */
    protected function parseLog($e)
    {
        $info = [
            request()->ip().' '.request()->method().' '.request()->url(),
            $e->getMessage(),
            $e->getFile().':'.$e->getLine(),
            "[param :]".json_encode(request()->all(), JSON_UNESCAPED_UNICODE),
            str_repeat('-', 80)
        ];
        return implode("\r\n", $info);
    }
}
