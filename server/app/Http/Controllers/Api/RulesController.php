<?php

namespace App\Http\Controllers\Api;

use App\Events\SynCmdEvent;
use App\Models\Admin;
use App\Models\Rules;
use App\Rules\CheckRuleMsg;
use Illuminate\Http\Request;

class RulesController extends BaseApiController
{
    private $customAttributes = [
        'web_id'         => '域名',
        'request_uri'    => '请求url',
        'request_method' => '请求方法',
        'param_content'  => '请求参数',
        'describe'       => '策略描述',
        'is_black'       => '是否加黑',
        'black_type'     => '黑名单类型',
        'black_num'      => '时间',
        'type_id'        => '策略类型',
        'param_site'     => '参数位置',
        'status'         => '策略状态',
    ];

    //网站规则列表
    public function index(Request $request)
    {
        // 分页、筛选数据
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $web_id        = $request->input('web_id');
        $id            = $request->input('id');
        $type_id       = $request->input('type_id');
        $status        = $request->input('status');
        //请求数据对象object
        $Rules = Rules::when($id, function ($query) use ($id) {
            return $query->where('id', $id);
        })->when($type_id, function ($query) use ($type_id) {
            return $query->where('type_id', $type_id);
        })->when($web_id, function ($query) use ($web_id) {
            return $query->where('web_id', $web_id);
        })->when(isset($status), function ($query) use ($status) {
            return $query->where('status', $status);
        });
        $Query = clone $Rules;
        //获取数据进行排序 offset设置从第几个开始，limit规定取多少个达到分页效果
        $list   = $Rules->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        $admins = Admin::withoutGlobalScopes()->pluck('username', 'id')->toArray();
        foreach ($list as $k => $v) {
            $list[$k]['admin_id'] = $admins[$v['admin_id']] ?? '-';
        }
        return $this->success(['list' => $list, 'count' => $Query->count('id')]);
    }

    //查询详情
    public function show(Request $request, Rules $Rules)
    {
        $Rules = $Rules->toArray();
        // $type                    = RuleType::GetTypeName();
        // $Rules['type_rule']   = $type[$Rules['type_rule']];
        // $Rules['param_site']  = Rules::$param_site[$Rules['param_site']];
        // $Rules['rule_status'] = Rules::$rule_status[$Rules['rule_status']];
        return $this->success($Rules);
    }

    //新增
    public function store(Request $request)
    {
        $rules = [
            'web_id'        => 'required|exists:web,id|bail',
            'request_uri'   => 'sometimes|required|starts_with:/|bail',
            // 'request_method' => 'required|array|bail',
            'param_site'    => 'required|in:0,1,2,3|bail',
            'param_content' => 'required_unless:param_site,0|array|bail',
            'describe'      => ['required', new CheckRuleMsg(), 'bail'],
            'is_black'      => 'required|in:0,1|bail',
            'black_type'    => 'required_if:is_black,1|in:0,1,2|bail',
            'black_num'     => 'required_if:black_type,1,2|numeric|min:0|bail',
            'type_id'       => 'required|exists:rule_type,id|bail',
            'status'        => 'required|in:0,1,2|bail',
        ];

        $this->validator($rules, $this->customAttributes);
        $Rules                 = new Rules();
        $Rules->web_id         = $request->input('web_id');
        $Rules->request_uri    = $request->input('request_uri', '');
        $Rules->request_method = $request->input('request_method', []);
        $Rules->param_site     = $request->input('param_site');
        $Rules->param_content  = $request->input('param_content', []);
        $Rules->describe       = formatDes($request->input('describe'));
        $Rules->is_black       = $request->input('is_black');
        if ($request->input('is_black') == 0) {
            $Rules->black_type = 0;
            $Rules->black_num  = 0;
            $Rules->black_time = 0;
        } else {
            $black_type        = $request->input('black_type', 0);
            $Rules->black_type = $black_type;
            $black_num         = $request->input('black_num', 0);
            $Rules->black_num  = $black_num;
            $Rules->black_time = transTime($black_type, $black_num);
        }
        $Rules->type_id  = $request->input('type_id');
        $Rules->status   = $request->input('status');
        $Rules->admin_id = $request->input('user_id');
        $Rules->save();
        return $this->success();
    }

    //更新
    public function update(Request $request, Rules $Rules)
    {
        $rules = [
            'web_id'         => 'required|exists:web,id|bail',
            'request_uri'    => 'sometimes|required|starts_with:/|bail',
            'request_method' => 'required|array|bail',
            'param_site'     => 'required|in:0,1,2,3|bail',
            'param_content'  => 'required_unless:param_site,0|array|bail',
            'describe'       => ['required', new CheckRuleMsg(), 'bail'],
            'is_black'       => 'required|in:0,1|bail',
            'black_type'     => 'required_if:is_black,1|in:0,1,2|bail',
            'black_num'      => 'required_if:black_type,1,2|numeric|min:0|bail',
            'type_id'        => 'required|exists:rule_type,id|bail',
            'status'         => 'required|in:0,1,2|bail',
        ];

        $this->validator($rules, $this->customAttributes);
        $Rules->web_id         = $request->input('web_id');
        $Rules->request_uri    = $request->input('request_uri', '');
        $Rules->request_method = $request->input('request_method', []);
        $Rules->param_site     = $request->input('param_site');
        $Rules->param_content  = $request->input('param_content', []);
        $Rules->describe       = formatDes($request->input('describe'));
        $Rules->is_black       = $request->input('is_black');
        if ($request->input('is_black') == 0) {
            $Rules->black_type = 0;
            $Rules->black_num  = 0;
            $Rules->black_time = 0;
        } else {
            $black_type        = $request->input('black_type', 0);
            $Rules->black_type = $black_type;
            $black_num         = $request->input('black_num', 0);
            $Rules->black_num  = $black_num;
            $Rules->black_time = transTime($black_type, $black_num);
        }
        $Rules->type_id  = $request->input('type_id');
        $Rules->status   = $request->input('status');
        $Rules->admin_id = $request->input('user_id');
        $Rules->save();
        return $this->success();
    }

    public function destroy(Request $request, $id)
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
            Rules::destroy($ids);
        }
        return $this->success();
    }

    public function changeStatus(Request $request)
    {
        $rules = [
            'id'     => 'required|array|bail',
            'status' => 'required|in:0,1,2|bail',
        ];
        $this->validator($rules, $this->customAttributes);
        $Rules = Rules::whereIn('id', $request->input('id'))->get();
        foreach ($Rules as $rule) {
            $rule->status = $request->input('status');
            $rule->save();
        }
        return $this->success();
    }

    public function syncConfig()
    {
        reset_web_rules();
        event(new SynCmdEvent(3));
        return $this->success();
    }
}
