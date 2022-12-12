<?php

namespace App\Http\Controllers\Api;

use App\Events\SynCmdEvent;
use App\Models\RuleType;
use App\Models\RulesSys;
use Illuminate\Http\Request;

class RuleSysController extends BaseApiController
{
    //系统规则列表
    public function index(Request $request)
    {
        // 分页、筛选数据
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $id            = $request->input('id');
        $type_id       = $request->input('type_id');
        $is_black      = $request->input('is_black');
        //请求数据对象object
        $Rules = RulesSys::when($id, function ($query) use ($id) {
            return $query->where('id', $id);
        })->when(isset($type_id), function ($query) use ($type_id) {
            return $query->where('type_id', $type_id);
        })->when(isset($is_black), function ($query) use ($is_black) {
            return $query->where('is_black', $is_black);
        });
        $type  = RuleType::pluck('name','id')->toArray();
        $Query = clone $Rules;
        //获取数据进行排序 offset设置从第几个开始，limit规定取多少个达到分页效果
        $list = $Rules->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();

        foreach ($list as $k => $v) {
            $list[$k]['type_name'] = $type[$v['type_id']] ?? '-';
        }
        return $this->success(['list' => $list, 'count' => $Query->count('id')]);
    }

    public function searchList()
    {
        $list = RulesSys::pluck('id')->toArray();
        return $this->success($list);
    }

    //查询详情
    public function show(Request $request, RulesSys $Rules)
    {
        $Rules = $Rules->toArray();

        return $this->success($Rules);
    }

    //更新
    public function update(Request $request, RulesSys $Rules)
    {
        $rules            = [
            'type_id'    => 'required|exists:rule_type,id|bail',
            'is_black'   => 'required|in:0,1|bail',
            'black_type' => 'required_if:is_black,1|in:0,1,2|bail',
            'black_num'  => 'required_if:black_type,1,2|numeric|min:0|bail',
        ];
        $customAttributes = [
            'is_black'   => '是否加黑',
            'black_type' => '黑名单类型',
            'black_num'  => '时间',
            'type_id'    => '策略类型',
        ];

        $this->validator($rules, $customAttributes);
        $Rules->type_id  = $request->input('type_id');
        $Rules->is_black = $request->input('is_black');
        if ($request->input('is_black') == 0) {
            $Rules->black_type        = 0;
            $Rules->black_num         = 0;
            $Rules->black_time        = 0;
            $Rules->black_append_rule = '';
        } else {
            $black_type               = $request->input('black_type', 0);
            $Rules->black_type        = $black_type;
            $black_num                = $request->input('black_num', 0);
            $Rules->black_num         = $black_num;
            $Rules->black_time        = transTime($black_type, $black_num);
            $id                       = $Rules->id;
            $lua_path                 = base_path('sh/black.lua');
            $rule_str                 = "\"exec:$lua_path\"";
            $Rules->black_append_rule = "SecRuleUpdateActionById $id $rule_str";
        }
        $Rules->save();
        return $this->success();
    }

    public function syncConfig()
    {
        reset_sys_rules();
        event(new SynCmdEvent(6));
        return $this->success();
    }
}
