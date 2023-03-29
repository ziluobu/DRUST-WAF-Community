<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\IpAllow;
use Illuminate\Http\Request;

class IpAllowController extends BaseApiController
{
    //白名单列表
    public function index(Request $request)
    {
        // 分页、筛选数据
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $ip            = $request->input('ip');
        $reason        = $request->input('reason');
        $beginTime     = $request->input('beginTime');
        $endTime       = $request->input('endTime');
        $IpAllowQuery  = IpAllow::when($ip, function ($query) use ($ip) {
            return $query->where('ip', 'like', "%$ip%");
        })->when($reason, function ($query) use ($reason) {
            return $query->where('reason', 'like', "%$reason%");
        })->when($beginTime && $endTime, function ($query) use ($beginTime, $endTime) {
            return $query->whereBetween('created_at', [$beginTime, $endTime]);
        });
        $Query         = clone $IpAllowQuery;
        //获取数据进行排序 offset设置从第几个开始，limit规定取多少个达到分页效果
        $list   = $IpAllowQuery->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        $admins = Admin::withoutGlobalScopes()->pluck('username', 'id')->toArray();
        foreach ($list as $k => $v) {
            $list[$k]['expire_time'] = date('Y-m-d H:i:s', $v['expire_time']);
            $list[$k]['admin_id']    = $admins[$v['admin_id']] ?? '-';
        }
        return $this->success(['list' => $list, 'count' => $Query->count('id')]);
    }

    //新增白名单
    public function store(Request $request)
    {
        $rules            = [
            'ip'          => ['required', 'regex:/^([0,1]?\d{1,2}|2([0-4][0-9]|5[0-5]))(\.([0,1]?\d{1,2}|2([0-4][0-9]|5[0-5]))){3}(\/\d{1,2})?$/'],
            'reason'      => 'required|bail',
            'expire_time' => ['required', function ($attribute, $value, $fail) {
                if (strlen($value) == 13) {
                    $value /= 1000;
                }
                if (intval($value) <= time()) {
                    $fail('过期时间错误');
                }
            }],
        ];
        $customAttributes = [
            'ip'          => 'IP',
            'reason'      => '原因',
            'expire_time' => '过期时间',
        ];
        $this->validator($rules, $customAttributes);
        $ipallow = IpAllow::firstOrNew(['ip' => $request->input('ip')]);
        //$ipallow->ip    = $request->input('ip');
        $ipallow->reason = $request->input('reason');
        $expire_time     = $request->input('expire_time', 0);
        if (strlen($expire_time) == 13) {
            $expire_time /= 1000;
        }
        $ipallow->expire_time = $expire_time;
        $ipallow->admin_id    = $request->input('user_id', 0);
        $ipallow->save();
        return $this->success();
    }

    //查询详情
    public function show(Request $request, IpAllow $ipallow)
    {
        $ipallow = $ipallow->toArray();
        return $this->success($ipallow);
    }

    //删除
    public function destroy(IpAllow $ipallow)
    {
        $ipallow->delete();
        return $this->success();
    }

    //更新白名单
    public function update(Request $request, IpAllow $ipallow)
    {
        $rules            = [
            'reason'      => 'required|bail',
            'expire_time' => ['required', function ($attribute, $value, $fail) {
                if (strlen($value) == 13) {
                    $value /= 1000;
                }
                if (intval($value) <= time()) {
                    $fail('过期时间错误');
                }
            }],
        ];
        $customAttributes = [
            'reason'      => '原因',
            'expire_time' => '过期时间',
        ];
        $this->validator($rules, $customAttributes);
        $ipallow->reason = $request->input('reason');
        $expire_time     = $request->input('expire_time', 0);
        if (strlen($expire_time) == 13) {
            $expire_time /= 1000;
        }
        $ipallow->expire_time = $expire_time;
        $ipallow->save();
        return $this->success();
    }

    public function syncConfig()
    {
        return $this->success();
    }
}
