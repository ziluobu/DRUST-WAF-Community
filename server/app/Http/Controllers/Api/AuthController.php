<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\Admin;
use App\Models\Loginlog;
use App\Rules\CheckPwd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Facades\Agent;

class AuthController extends BaseApiController
{
    //
    public function login(Request $request)
    {
        $errorKey = $request->ip() . $request->userAgent();
        $errorNum = Cache::get($errorKey, 0);
        $loginNum = getConfig('login_num');
        if ($loginNum > 0) {
            if ($errorNum >= $loginNum) {
                throw new ApiException('登录错误次数过多', 4004, ['loginNum' => 0]);
            }
        } else {
            $loginNum = 999;
        }

        $rules = [
            'username' => 'required|alpha_dash|bail',
            'password' => 'required|bail',
            //todo 暂时注释
            //'key'      => 'required|bail',
            //'captcha'  => 'required|captcha_api:'.$request->input('key')
        ];
        $this->validator($rules);
        $param = $request->all();
        $admin = Admin::where('username', $param['username'])->withoutGlobalScopes()->first();
        $ip    = $request->ip();
        if (!$admin) {
            $this->loginlog($ip, $param['username'], '用户名错误');
            Cache::put($errorKey, $errorNum + 1, 600);
            throw new ApiException('用户名/密码错误', 4002, ['loginNum' => [$loginNum - 1 - $errorNum]]);
        }
        //检测密码
        $param['password'] = decrypt_cbc($param['password']);
        if (!\Hash::check($param['password'], $admin->password)) {
            $this->loginlog($ip, $param['username'], '密码错误');
            Cache::put($errorKey, $errorNum + 1, 600);
            throw new ApiException('用户名/密码错误', 4002, ['loginNum' => [$loginNum - 1 - $errorNum]]);
        }
        //检测用户状态
        if ($admin->status == 0) {
            $this->loginlog($ip, $param['username'], '账号已被冻结');
            throw new ApiException('账号已被冻结', 4003);
        }
        Cache::forget($errorKey);
        $this->loginlog($ip, $param['username'], '登录成功', 1);
        $admin->last_ip   = $ip;
        $admin->last_time = date('Y-m-d H:i:s');
        $admin->save();

        $msg = '登录成功';
        $reg = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,15}$/";
        if (!preg_match($reg, $param['password']) || $param['username'] == $param['password'] || 'DXyf@2022' == $param['password']) {
            $msg = '当前密码强度不足，请及时修改密码';
        }

        return $this->success(['token' => token_encode($admin->id)], $msg);
    }

    public function logout(Request $request)
    {
        $this->refreshToken($request);
        // $id = $request->input('user_id');
        // app('redis')->connection('token')->del([$id]);
        return $this->success();
    }

    public function updatePwd(Request $request)
    {
        $rules            = [
            'old_password' => 'bail|required|between:5,15',
            'password'     => ['required', 'bail', 'confirmed', new CheckPwd()],
            // 'password'     => 'bail|required|confirmed|between:5,15',
        ];
        $customAttributes = [
            'old_password' => '原密码',
            'password'     => '新密码',
        ];
        $this->validator($rules, $customAttributes);
        $admin = Admin::where('id', $request->input('user_id'))->withoutGlobalScopes()->first();
        if (!\Hash::check($request->input('old_password'), $admin->password)) {
            throw new ApiException('密码错误');
        }
        $admin->password = \Hash::make($request->input('password'));
        $admin->save();
        $this->refreshToken($request);
        return $this->success();
    }

    private function refreshToken(Request $request)
    {
        $check_ip = getConfig('check_ip');
        if ($check_ip) {
            app('redis')->connection('token')->del([$request->input('user_id')]);
        } else {
            app('redis')->connection('token')->del([$request->header('token')]);
        }
    }

    private function loginlog($ip, $login_name, $msg, $status = 0)
    {
        $os                       = Agent::platform() . ' ' . Agent::version(Agent::platform());
        $browser                  = Agent::browser() . ' ' . Agent::version(Agent::browser());
        $ipInfo                   = getIpInfo($ip);
        $loginlog                 = new Loginlog();
        $loginlog->login_name     = $login_name;
        $loginlog->ipaddr         = $ip;
        $loginlog->login_location = $ipInfo['country'] . $ipInfo['province'] . $ipInfo['city'] . $ipInfo['county'];
        $loginlog->browser        = $browser;
        $loginlog->os             = $os;
        $loginlog->net            = $ipInfo['isp'];
        $loginlog->status         = $status;
        $loginlog->msg            = $msg;
        $loginlog->save();
    }
}
