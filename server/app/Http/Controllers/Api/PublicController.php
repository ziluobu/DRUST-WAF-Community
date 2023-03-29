<?php

namespace App\Http\Controllers\Api;

use App\Models\Config;
use App\Models\Group;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Rules;
use App\Models\RulesGlobal;
use App\Models\RulesSys;
use App\Models\Web;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MonthReportMail;

class PublicController extends BaseApiController
{
    public function captcha()
    {
        $data = app('captcha')->create('default', true);
        return $this->success($data);
    }

    public function getRouters(Request $request)
    {
        $list['routes']      = Menu::getRouteList();
        $list['permissions'] = Menu::getPermissions();
        return $this->success($list);
    }

    public function getConfig()
    {
        // dd(getgeoIpInfo('1.192.213.76'));
        $list = Config::pluck('value', 'key')->toArray();
        return $this->success($list);
    }

    public function mail()
    {
        $message = (new MonthReportMail())->onQueue('month-report');
        Mail::to('657340528@qq.com')->queue($message);
        return $this->success();
    }

    public function userInfo(Request $request)
    {
        $user_group_id = $request->input('user_group_id');
        $group_name    = '星海安全实验室';
        if ($user_group_id) {
            $group_name = Group::where('id', $user_group_id)->value('group_name');
        }

        $data = [
            'username'   => $request->input('loginUsername'),
            'group_name' => $group_name,
        ];
        return $this->success($data);
    }

    /**
     * 刷新缓存
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshCache()
    {
        //配置缓存
        $configs = Config::pluck('value', 'key')->toArray();
        app('redis')->connection('cache')->del(['config']);
        app('redis')->connection('cache')->hmset('config', $configs);

        //站点缓存
        $webs     = Web::select(['id', 'web_name', 'web_port'])->get()->toArray();
        $cacheWeb = [];
        foreach ($webs as $web) {
            if ($web['web_port'] != 80) {
                $cacheWeb[$web['web_name'] . ':' . $web['web_port']] = $web['id'];
            } else {
                $cacheWeb[$web['web_name']] = $web['id'];
            }
        }
        app('redis')->connection('host')->flushdb();
        app('redis')->connection('host')->mset($cacheWeb);

        //加黑规则
        $black_rule        = Rules::where('status', 1)
            ->where('is_black', 1)
            ->select(['id', 'black_type', 'black_time'])->get()
            ->toArray();
        $black_global_rule = RulesGlobal::where('status', 1)
            ->where('is_black', 1)
            ->select(['id', 'black_type', 'black_time'])->get()
            ->toArray();
        $black_sys_rule    = RulesSys::where('is_black', 1)
            ->select(['id', 'black_type', 'black_time'])->get()
            ->toArray();
        $black_time        = [];
        foreach ($black_rule as $v) {
            $black_time[$v['id'] + 440000] = $v['black_type'] == 0 ? 0 : $v['black_time'];
        }
        foreach ($black_global_rule as $v) {
            $black_time[$v['id'] + 450000] = $v['black_type'] == 0 ? 0 : $v['black_time'];
        }
        foreach ($black_sys_rule as $v) {
            $black_time[$v['id']] = $v['black_type'] == 0 ? 0 : $v['black_time'];
        }
        if ($black_time) {
            app('redis')->connection('black_time')->mset($black_time);
        }

        //规则分类
        $sysdata    = RulesSys::pluck('type_id', 'id')->toArray();
        $webdata    = Rules::pluck('type_id', 'id')->toArray();
        $globaldata = RulesGlobal::pluck('type_id', 'id')->toArray();
        $new_data   = $this->getNewData($webdata, $globaldata, $sysdata);
        app('redis')->connection('rules-type')->mset($new_data);

        //权限
        $list    = Role::with('menus:permit')->get(['id'])->toArray();
        $permits = [];
        foreach ($list as $v) {
            if ($v['menus']) {
                $permits[$v['id']] = json_encode(array_filter(array_column($v['menus'], 'permit')));
            }
        }
        if ($permits) {
            app('redis')->connection('cache')->del('permitList');
            app('redis')->connection('cache')->hmset('permitList', $permits);
        }
        return $this->success();
    }

    private function getNewData($webdata, $globaldata, $sysdata)
    {
        $newData = [];
        foreach ($webdata as $k => $v) {
            $newData[$k + 440000] = is_numeric($v) ? $v : 0;
        }
        foreach ($globaldata as $k => $v) {
            $newData[$k + 450000] = is_numeric($v) ? $v : 0;
        }
        foreach ($sysdata as $k => $v) {
            $newData[$k] = is_numeric($v) ? $v : 0;
        }
        $newData[590000] = '590000';
        $newData[590002] = '590002';
        return $newData;
    }

}
