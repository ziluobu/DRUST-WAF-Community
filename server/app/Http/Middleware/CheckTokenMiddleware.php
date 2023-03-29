<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Models\Admin;
use Closure;

class CheckTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('token');
        if (!$token) {
            throw new ApiException('token错误', 4100);
        }
        $data = token_decode($token);
        if (
            $data['ip'] != $request->ip() ||
            $data['user-agent'] != $request->userAgent()
        ) {
            throw new ApiException('异地登录', 4101);
        }

        $check_ip   = getConfig('check_ip');
        $expireTime = getConfig('token_expire_time');
        if ((int)$expireTime <= 0) {
            $expireTime = 3600;
        }
        if (time() - $data['time'] > 3600 * 12) {
            throw new ApiException('登录已过期', 4102);
        }
        $Redis = app('redis')->connection('token');
        if ($check_ip) {
            $redisToken = $Redis->get($data['user_id']);
            if (!$redisToken) {
                throw new ApiException('登录已过期', 4102);
            }
            if ($redisToken != $token) {
                throw new ApiException('异地登录', 4102);
            }
            $Redis->expire($data['user_id'], $expireTime);
        } else {
            if (!$Redis->exists($token)) {
                throw new ApiException('异地登录', 4102);
            }
            $Redis->expire($token, $expireTime);
        }

        $admin = Admin::withoutGlobalScopes()->where('id', $data['user_id'])->first();
        if ($admin->status != 1) {
            throw new ApiException('账号已被冻结', 4103);
        }

        $request->merge([
            'user_id'       => $data['user_id'],
            'user_group_id' => $admin->group_id,
            'loginUsername' => $admin->username,
        ]);
        return $next($request);
    }
}
