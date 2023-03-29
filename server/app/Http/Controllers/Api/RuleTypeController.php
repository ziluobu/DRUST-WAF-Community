<?php

namespace App\Http\Controllers\Api;

use App\Models\RuleType;
use Illuminate\Http\Request;

class RuleTypeController extends BaseApiController
{
    //攻击类型列表
    public function index(Request $request)
    {
        // 分页、筛选数据
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $name          = $request->input('name');
        //请求数据对象object
        $RuleType = RuleType::when($name, function ($query) use ($name) {
            return $query->where('name', 'like', "%$name%");
        });
        $Query    = clone $RuleType;
        //获取数据进行排序 offset设置从第几个开始，limit规定取多少个达到分页效果
        $list = $RuleType->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        return $this->success(['list' => $list, 'count' => $Query->count('id')]);
    }

    public function searchList()
    {
        $list = RuleType::get(['id', 'name'])->toArray();
        return $this->success($list);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $rules = [
            'name'     => 'required|unique:rule_type,name|bail',
            'describe' => 'required|bail',
        ];

        $customAttributes = ['name' => '策略名称', 'describe' => '策略描述'];
        $this->validator($rules, $customAttributes);
        $RuleType           = new RuleType();
        $RuleType->name     = $request->input('name');
        $RuleType->describe = $request->input('describe', '');
        $RuleType->save();
        return $this->success();
    }

    public function update(Request $request, RuleType $ruleType)
    {
        if ($ruleType->name != $request->input('name')) {
            $rules            = [
                'name' => 'required|unique:rule_type,name|bail',
            ];
            $customAttributes = ['name' => '策略名称', 'describe' => '策略描述'];
            $this->validator($rules, $customAttributes);
        }

        $ruleType->name     = $request->input('name');
        $ruleType->describe = $request->input('describe', '');
        $ruleType->save();
        return $this->success();
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $ids = explode(',', $id);
        $ids = array_filter($ids, function ($v) {
            if (is_numeric($v)) {
                return true;
            }
            return false;
        });
        if (is_array($ids)) {
            RuleType::destroy($ids);
        }
        return $this->success();
    }
}
