<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\Group;
use App\Models\Web;
use Illuminate\Http\Request;

class GroupController extends BaseApiController
{
    //单位列表
    public function index(Request $request)
    {
        $list = Group::get()->toArray();
        return $this->success(['list' => $list]);
    }

    public function searchList()
    {
        $list = Group::get(['id', 'group_name'])->toArray();
        return $this->success($list);
    }

    //删除
    public function destroy(Group $group)
    {
        if (
            Web::where('group_id', $group->id)->first()
        ) {
            throw new ApiException('该单位下有资产，不允许删除');
        }
        $group->delete();
        return $this->success();
    }

    //查询详情
    public function show(Request $request, Group $group)
    {
        $group = $group->toArray();
        return $this->success($group);
    }

    //更新
    public function update(Request $request, Group $group)
    {
        if ($request->input('group_name') !== $group->group_name) {
            $rules            = [
                'group_name' => 'required|unique:group,group_name|bail',
            ];
            $customAttributes = [
                'group_name' => '单位名称',
            ];
            $this->validator($rules, $customAttributes);
            $group->group_name = $request->input('group_name');
            $group->save();
        }
        return $this->success();
    }

    //新增
    public function store(Request $request)
    {
        $rules            = [
            'group_name' => 'required|unique:group,group_name|bail',
        ];
        $customAttributes = [
            'group_name' => '单位名称',
        ];
        $this->validator($rules, $customAttributes);
        $group             = new Group();
        $group->group_name = $request->input('group_name');
        $group->save();
        return $this->success();
    }
}
