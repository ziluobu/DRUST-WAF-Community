<?php

namespace App\Http\Controllers\Api;

use App\Imports\IpBlackImport;
use App\Models\Admin;
use App\Models\IpBlack;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IpBlackController extends BaseApiController
{
    private $customAttributes = [
        'ip'          => 'IP',
        'black_type'  => '封禁类型',
        'reason'      => '封禁原因',
        'expire_time' => '过期时间',
    ];

    //黑名单列表
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
        //请求数据对象object
        $IpBlackQuery = IpBlack::when($ip, function ($query) use ($ip) {
            return $query->where('ip', 'like', "%$ip%");
        })->when($reason, function ($query) use ($reason) {
            return $query->where('reason', 'like', "%$reason%");
        })->when($beginTime && $endTime, function ($query) use ($beginTime, $endTime) {
            return $query->whereBetween('created_at', [$beginTime, $endTime]);
        });
        $Query        = clone $IpBlackQuery;
        //获取数据进行排序 offset设置从第几个开始，limit规定取多少个达到分页效果
        $list   = $IpBlackQuery->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        $admins = Admin::withoutGlobalScopes()->pluck('username', 'id')->toArray();
        foreach ($list as $k => $v) {
            if ($v['black_type'] == 1) {
                $list[$k]['expire_time'] = date('Y-m-d H:i:s', $v['expire_time']);
            }
            $list[$k]['admin_id']    = $admins[$v['admin_id']] ?? '-';
        }
        return $this->success(['list' => $list, 'count' => $Query->count('id')]);
    }

    //删除
    public function destroy($id)
    {
        $this->validator();
        $ids = explode(',', $id);
        $ids = array_filter($ids, function ($v) {
            if (is_numeric($v)) {
                return true;
            }
            return false;
        });
        if (is_array($ids)) {
            IpBlack::destroy($ids);
        }
        return $this->success();
    }

    //查询详情
    public function show(Request $request, IpBlack $ipblack)
    {
        $ipblack = $ipblack->toArray();
        return $this->success($ipblack);
    }

    //更新
    public function update(Request $request, IpBlack $ipblack)
    {
        $rules       = [
            'reason' => 'required|bail',
        ];
        $expire_time = $request->input('expire_time', 0);
        if ($expire_time) {
            $rules['expire_time'] = ['required', function ($attribute, $value, $fail) {
                if (strlen($value) == 13) {
                    $value /= 1000;
                }
                if (intval($value) <= time() && intval($value) != 0) {
                    $fail('时间错误');
                }
            }];
        }
        $this->validator($rules, $this->customAttributes);
        $ipblack->reason = $request->input('reason', '');
        if ($expire_time) {
            if (strlen($expire_time) == 13) {
                $expire_time /= 1000;
            }
            $ipblack->expire_time = $expire_time;
            $ipblack->black_type  = 1;
        } else {
            $ipblack->expire_time = 0;
            $ipblack->black_type  = 0;
        }
        $ipblack->admin_id = $request->input('user_id', 0);
        $ipblack->save();
        return $this->success();
    }

    //新增
    public function store(Request $request)
    {
        $rules       = [
            'ip'     => ['required', 'regex:/^([0,1]?\d{1,2}|2([0-4][0-9]|5[0-5]))(\.([0,1]?\d{1,2}|2([0-4][0-9]|5[0-5]))){3}(\/\d{1,2})?$/'],
            'reason' => 'required|bail',
        ];
        $expire_time = $request->input('expire_time', 0);
        if ($expire_time) {
            $rules['expire_time'] = ['required', function ($attribute, $value, $fail) {
                if (strlen($value) == 13) {
                    $value /= 1000;
                }
                if (intval($value) <= time() && intval($value) != 0) {
                    $fail('时间错误');
                }
            }];
        }

        $this->validator($rules, $this->customAttributes);
        $ipblack         = IpBlack::firstOrNew(['ip' => $request->input('ip')]);
        $ipblack->reason = $request->input('reason', '');

        if ($expire_time) {
            if (strlen($expire_time) == 13) {
                $expire_time /= 1000;
            }
            $ipblack->expire_time = $expire_time;
            $ipblack->black_type  = 1;
        } else {
            $ipblack->expire_time = 0;
            $ipblack->black_type  = 0;
        }

        $ipblack->admin_id = $request->input('user_id', 0);
        $ipblack->type     = 0;
        $ipblack->save();
        return $this->success();
    }

    public function import(Request $request)
    {
        $rules            = [
            'file' => 'required|bail|mimes:xlsx'
        ];
        $customAttributes = [
            'file' => '文件',
        ];
        $this->validator($rules, $customAttributes);
        Excel::import(new IpBlackImport(), $request->file('file'), 'public');
        return $this->success();
    }

    public function syncConfig()
    {
        return $this->success();
    }
}
