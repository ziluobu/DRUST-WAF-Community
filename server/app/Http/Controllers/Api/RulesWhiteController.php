<?php

namespace App\Http\Controllers\Api;

use App\Events\SynCmdEvent;
use App\Models\Admin;
use App\Models\RulesWhite;
use App\Rules\CheckRuleMsg;
use Illuminate\Http\Request;

class RulesWhiteController extends BaseApiController
{
    private $customAttributes = [
        'web_id'            => '域名',
        'request_uri'       => '请求url',
        'request_method'    => '请求方法',
        'param_content'     => '请求参数',
        'describe'          => '策略描述',
        'is_black'          => '是否加黑',
        'black_type'        => '黑名单类型',
        'black_num'         => '时间',
        'type_id'           => '策略类型',
        'param_site'        => '参数位置',
        'status'            => '策略状态',
        'remove_sysrule_id' => '系统规则',
    ];

    //系统白名单列表
    public function index(Request $request)
    {
        // 分页、筛选数据
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $web_id        = $request->input('web_id');
        $id            = $request->input('id');
        //请求数据对象object
        $Rules  = RulesWhite::when($id, function ($query) use ($id) {
            return $query->where('id', $id);
        })->when(isset($web_id), function ($query) use ($web_id) {
            return $query->where('web_id', $web_id);
        });
        $Query  = clone $Rules;
        //获取数据进行排序 offset设置从第几个开始，limit规定取多少个达到分页效果
        $list = $Rules->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        $admins = Admin::withoutGlobalScopes()->pluck('username', 'id')->toArray();
        foreach ($list as $k => $v) {
            $list[$k]['admin_id'] = $admins[$v['admin_id']] ?? '-';
        }
        return $this->success(['list' => $list, 'count' => $Query->count('id')]);
    }

    //查询详情
    public function show(Request $request, RulesWhite $Rules)
    {
        $Rules = $Rules->toArray();
        return $this->success($Rules);
    }

    //新增
    public function store(Request $request)
    {
        $remove_sysrule_id = $request->input('remove_sysrule_id', 0);
        $rules             = [
            'web_id'            => 'required_without:request_uri|exists:web,id|bail',
            'remove_sysrule_id' => 'required|exists:rules_sys,id|bail',
            'request_uri'       => 'required_without:web_id|starts_with:/|bail',
            'request_method'    => 'required|array|bail',
            'describe'          => ['required', new CheckRuleMsg(), 'bail'],
            'status'            => 'required|in:0,1|bail',
        ];
        if ($request->$remove_sysrule_id == 0) {
            $rules['remove_sysrule_id'] = 'required|bail';
        }
        $this->validator($rules, $this->customAttributes);
        $Rules                    = new RulesWhite();
        $Rules->remove_sysrule_id = $remove_sysrule_id;
        $Rules->web_id            = $request->input('web_id');
        $Rules->request_uri       = $request->input('request_uri');
        $Rules->request_method    = $request->input('request_method', []);
        $Rules->describe          = formatDes($request->input('describe'));
        //待添加
        // $Rules->rule_content = $request->input('rule_content');
        $Rules->status   = $request->input('status');
        $Rules->admin_id = $request->input('user_id');
        $Rules->save();
        return $this->success();
    }

    //新增
    public function update(Request $request, RulesWhite $Rules)
    {
        $remove_sysrule_id = $request->input('remove_sysrule_id', 0);
        $rules             = [
            'web_id'            => 'required_without:request_uri|exists:web,id|bail',
            'remove_sysrule_id' => 'required|exists:rules_sys,id|bail',
            'request_uri'       => 'required_without:web_id|starts_with:/|bail',
            'request_method'    => 'required|array|bail',
            'describe'          => ['required', new CheckRuleMsg(), 'bail'],
            'status'            => 'required|in:0,1|bail',
        ];
        if ($request->$remove_sysrule_id == 0) {
            $rules['remove_sysrule_id'] = 'required|bail';
        }
        $this->validator($rules, $this->customAttributes);
        $Rules->remove_sysrule_id = $remove_sysrule_id;
        $Rules->web_id            = $request->input('web_id');
        $Rules->request_uri       = $request->input('request_uri');
        $Rules->request_method    = $request->input('request_method', []);
        $Rules->describe          = formatDes($request->input('describe'));
        // $Rules->rule_content = $request->input('rule_content');
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
            RulesWhite::destroy($ids);
        }
        return $this->success();
    }

    public function syncConfig()
    {
        reset_white_sysrules();
        event(new SynCmdEvent(7));
        return $this->success();
    }
}
