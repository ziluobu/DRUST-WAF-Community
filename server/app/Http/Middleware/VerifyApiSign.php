<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Carbon\Carbon;
use Closure;

/**
 * 接口签名
 *
 */
class VerifyApiSign
{
    // 忽略列表
    protected $except = [
        //
        'api/*upload'
    ];

    // 时间误差
    protected $timeError = 60;

    // 密钥
    protected $secretKey = '';

    // 签名字段
    protected $signField = 'sign';

    public function __construct()
    {
        $this->secretKey = config('api.sign_secret_key', '');
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!getConfig('check_sign')) {
            return $next($request);
        }
        if (
            $this->inExceptArray($request)
            || ($this->allowTimestamp($request) && $this->signMatch($request))
        ) {
            return $next($request);
        }
        throw new ApiException('Signature error', 3001);
    }

    /**
     * 判断当前请求是否在忽略列表中
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     *
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断用户请求是否在对应时间范围
     *
     * @param \Illuminate\Http\Request $request
     * @return boolean
     *
     */
    protected function allowTimestamp($request)
    {
        $queryTime = Carbon::createFromTimestamp($request->get('timestamp', 0));
        $lfTime    = Carbon::now()->subSeconds($this->timeError);
        $rfTime    = Carbon::now()->addSeconds($this->timeError);
        if (!$queryTime->between($lfTime, $rfTime, true)) {
            throw new  ApiException('时间异常');
        }
        return true;
    }

    /**
     * 签名验证
     *
     * @param \Illuminate\Http\Request $request
     * @return boolean
     *
     */
    protected function signMatch($request)
    {
        if (!getConfig('check_sign')) {
            return true;
        }
        $data = $request->except('file');
        // 移除sign字段
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        $data = array_filter($data);
        ksort($data);

        $sign = '';
        foreach ($data as $k => $v) {
            if ($this->signField !== $k) {
                $sign .= $k . $v;
            }
        }

        if (md5(urlencode($sign . $this->secretKey)) === $request->get($this->signField, null)) {
            return true;
        }
        return false;
    }
}

