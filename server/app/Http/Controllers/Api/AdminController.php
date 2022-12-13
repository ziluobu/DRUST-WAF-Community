<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\Role;
use App\Rules\CheckPwd;
use Illuminate\Http\Request;

class AdminController extends BaseApiController
{

    private $customAttributes = [
        'status'   => '状态',
        'realname' => '姓名',
        'roleIds'  => '角色',
        'id'       => '用户',
        'group_id' => '单位',
    ];

    public function index(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $username      = $request->input('username');
        $status        = $request->input('status');
        $beginTime     = $request->input('beginTime');
        $endTime       = $request->input('endTime');
        $rules         = [
            'beginTime' => 'required_with:endTime|date|before:endTime|bail',
            'endTime'   => 'required_with:beginTime|date|after:beginTime|bail',
            'status'    => 'sometimes|required|bail|in:0,1',
        ];
        $this->validator($rules);

        $Query      = Admin::when($username, function ($query) use ($username) {
            return $query->where('username', 'like', "%$username%");
        })->when(isset($status), function ($query) use ($status) {
            return $query->where('status', $status);
        })->when($beginTime && $endTime, function ($query) use ($beginTime, $endTime) {
            return $query->whereBetween('created_at', [$beginTime, $endTime]);
        });
        $countQuery = clone $Query;
        $list       = $Query->with('adminRole')->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        foreach ($list as $k => $v) {
            $list[$k]['roleIds'] = array_column($v['admin_role'], 'role_id');
            unset($list[$k]['admin_role']);
        }

        return $this->success(['list' => $list, 'count' => $countQuery->count('id')]);
    }

    public function store(Request $request)
    {
        $rules    = [
            'username' => 'required|alpha_dash|unique:admin,username|bail',
            'password' => ['required','bail',new CheckPwd()],
            'realname' => 'required|bail',
            'email'    => 'sometimes|email|bail',
            'status'   => 'required|bail|in:0,1',
            //            'roleIds'  => 'sometimes|required|bail',
        ];
        $group_id = $request->input('group_id', 0);
        if ($group_id) {
            $rules['group_id'] = 'required|exists:group,id|bail';
        }
        $this->validator($rules, $this->customAttributes);
        \DB::transaction(function () use ($request, $group_id) {
            $Admin           = new Admin();
            $Admin->username = $request->input('username');
            $Admin->password = \Hash::make($request->input('password'));
            $Admin->realname = $request->input('realname', '');
            $Admin->email    = $request->input('email', '');
            $Admin->status   = $request->input('status');
            $Admin->group_id = $group_id;
            $Admin->note     = $request->input('note', '');
            $Admin->save();
            $roleIds = $request->input('roleIds', []);
            if (is_array($roleIds)) {
                $roleIds = Role::whereIn('id', $roleIds)->pluck('id');
                $Admin->roles()->sync($roleIds);
                //                $adminRoleData = [];
                //                foreach ($roles as $k => $v) {
                //                    $adminRoleData[$k]['role_id']  = $v;
                //                    $adminRoleData[$k]['admin_id'] = $Admin->id;
                //                }
                //                //一对多
                //                $Admin->adminRole()->createMany($adminRoleData);
            }
        });
        return $this->success();
    }

    public function show(Request $request, Admin $manage)
    {
        $manage->roleIds = array_column($manage->adminRole->toArray(), 'role_id');
        $manage          = $manage->toArray();
        unset($manage['admin_role']);
        return $this->success($manage);
    }

    public function update(Request $request, Admin $manage)
    {
        $rules    = [
            'status'   => 'required|bail|in:0,1',
            'realname' => 'sometimes|required',
            'email'    => 'sometimes|email|bail',
            // 'group_id' => 'sometimes|required|exists:group,id|bail',
        ];
        $group_id = $request->input('group_id', 0);
        if ($group_id) {
            $rules['group_id'] = 'required|exists:group,id|bail';
        }


        $this->validator($rules, $this->customAttributes);

        \DB::transaction(function () use ($request, $manage, $group_id) {
            $manage->realname = $request->input('realname', '');
            $manage->email    = $request->input('email', '');
            $manage->status   = $request->input('status');
            $manage->note     = $request->input('note', '');
            $manage->group_id = $group_id;
            $manage->save();
            $roleIds = $request->input('roleIds', []);
            if (is_array($roleIds)) {
                $roleIds = Role::whereIn('id', $roleIds)->pluck('id');
                $manage->roles()->sync($roleIds);
            }
        });
        return $this->success();
    }

    public function changeStatus(Request $request)
    {
        $rules = [
            'id' => 'required|exists:admin,id|bail',
        ];
        $this->validator($rules, $this->customAttributes);
        Admin::where('id', $request->input('id'))->update(['status' => \DB::raw("abs(status - 1)")]);
        return $this->success();
    }

    public function resetPwd(Request $request)
    {
        $rules = [
            'id'       => 'required|exists:admin,id|bail',
            'password' => ['required','bail',new CheckPwd()],
        ];
        $this->validator($rules, $this->customAttributes);
        $admin           = Admin::findOrFail($request->input('id'));
        $admin->password = \Hash::make($request->input('password'));
        $admin->save();
        return $this->success();
    }

    public function destroy(Admin $manage)
    {
        \DB::transaction(function () use ($manage) {
            $manage->roles()->detach();
            $manage->delete();
        });
        return $this->success();
    }

}
