<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\AdminRole;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends BaseApiController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     */
    public function index(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $name          = $request->input('name');
        $beginTime     = $request->input('beginTime');
        $endTime       = $request->input('endTime');
        $rules         = [
            'beginTime' => 'required_with:endTime|date|before:endTime|bail',
            'endTime'   => 'required_with:beginTime|date|after:beginTime|bail',
        ];
        $this->validator($rules);
        $Query      = Role::when($name, function ($query) use ($name) {
            return $query->where('name', 'like', "%$name%");
        })->when($beginTime && $endTime, function ($query) use ($beginTime, $endTime) {
            return $query->whereBetween('created_at', [$beginTime, $endTime]);
        });
        $countQuery = clone $Query;
        $list       = $Query->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();

        return $this->success(['list' => $list, 'count' => $countQuery->count('id')]);
    }

    public function searchList()
    {
        $list = Role::get(['id', 'name'])->toArray();
        return $this->success($list);
    }

    public function menuTree()
    {
        $list = Menu::where('status', 1)->where('is_super', 0)->get()->toArray();
        $list = Menu::getSearchTree($list);
        return $this->success($list);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        $rules            = [
            'name'     => 'required|unique:role,name|bail',
            'listsort' => 'required|bail|numeric|between:0,99',
            'menuIds'  => 'sometimes|required|bail',
        ];
        $customAttributes = [
            'name'    => '角色名称',
            'menuIds' => '菜单',
        ];
        $this->validator($rules, $customAttributes);
        \DB::transaction(function () use ($request) {
            $role           = new  Role();
            $role->name     = $request->input('name');
            $role->listsort = $request->input('listsort');
            $role->note     = $request->input('note', '');
            $role->save();
            $menuIds = $request->input('menuIds', []);
            if (is_array($menuIds)) {
                $list = Menu::whereIn('id', $menuIds)
                    // ->where('permit', '!=', '')
                    // ->whereNotNull('permit')
                    ->pluck('permit', 'id')->toArray();
                $role->menus()->sync(array_keys($list));
                app('redis')->connection('cache')->hset('permitList', $role->id, json_encode($list));
            }
        }, 2);

        return $this->success();
    }

    /**
     * Display the specified resource.
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Role $role)
    {
        $role->menuIds = array_column($role->roleMenus->toArray(), 'menu_id');
        $role          = $role->toArray();
        unset($role['role_menus']);
        return $this->success($role);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiException
     * @throws \Throwable
     */
    public function update(Request $request, Role $role)
    {
        $rules = [
            'name'     => 'required|unique:role,name|bail',
            'listsort' => 'required|bail|numeric|between:0,99',
            //            'menuIds'  => 'sometimes|required|bail',
        ];
        if ($role->name == $request->input('name')) {
            unset($rules['name']);
        }

        $customAttributes = [
            'name'    => '角色名称',
            'menuIds' => '菜单',
        ];
        $this->validator($rules, $customAttributes);
        \DB::transaction(function () use ($request, $role) {
            $role->name     = $request->input('name');
            $role->listsort = $request->input('listsort');
            $role->note     = $request->input('note', '');
            $role->save();
            $menuIds = $request->input('menuIds', []);
            $permit  = [];
            if (is_array($menuIds)) {
                $list = Menu::whereIn('id', $menuIds)
                    // ->where('permit', '!=', '')
                    // ->whereNotNull('permit')
                    ->pluck('permit', 'id')->toArray();
                $role->menus()->sync(array_keys($list));
                $permit = $list;
            }
            if ($permit) {
                app('redis')->connection('cache')->hset('permitList', $role->id, json_encode($permit));
            } else {
                app('redis')->connection('cache')->hdel('permitList', [$role->id]);
            }
        }, 2);

        return $this->success();
    }

    /**
     * Remove the specified resource from storage.
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function destroy(Role $role)
    {
        $admin = AdminRole::where('role_id', $role->id)->first();
        if ($admin) {
            throw new ApiException('不能删除分配有管理员的角色');
        }
        \DB::transaction(function () use ($role) {
            $role->menus()->detach();
            $role->delete();
            app('redis')->connection('cache')->hdel('permitList', [$role->id]);
        });
        return $this->success();
    }
}
