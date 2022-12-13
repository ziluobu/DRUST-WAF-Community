<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends BaseApiController
{
    private $customAttributes = [
        'name'      => '菜单名称',
        'permit'    => '后端权限字符',
        'perms'     => '前端权限字符',
        'parent_id' => '上级菜单',
        'listsort'  => '显示排序',
        'is_frame'  => '是否外链',
        'is_super'  => '超级管理员权限',
        'component' => '组件路径',
        'path'      => '路由地址',
        'type'      => '菜单类型',
        'status'    => '菜单状态',
        'visible'   => '显示状态',
        'icon'      => '图标',
    ];

    public function index(Request $request)
    {
        $name   = $request->input('name');
        $status = $request->input('status');
        $list   = Menu::when($name, function ($query) use ($name) {
            return $query->where('name', 'like', "%$name%");
        })->when(isset($status), function ($query) use ($status) {
            return $query->where('status', $status);
        })->get()->toArray();
        return $this->success(['list' => $list]);
    }

    public function searchList()
    {
        $list               = Menu::where('status', 1)->whereIn('type', ['M', 'C'])->get()->toArray();
        $list               = Menu::getSearchTree($list);
        $new[0]['name']     = '根目录';
        $new[0]['id']       = '0';
        $new[0]['children'] = $list;
        return $this->success($new);
    }

    public function store(Request $request)
    {
        $rules = [
            'name'      => 'required|bail',
            //            'permit'    => 'required_with:api_path',
            //            'perms'     => 'required_with:api_path',
            'parent_id' => 'required|min:0|bail|numeric',
            'listsort'  => 'required|bail|numeric|between:0,99',
            'is_frame'  => 'required_if:type,M,C|bail|in:0,1',
            //'component'      => 'required|bail|in:0,1',
            'path'      => 'exclude_unless:is_frame,1|required|url',
            'type'      => 'required|bail|in:M,F,C',
            'status'    => 'required|bail|in:0,1',
            'visible'   => 'sometimes|bail|in:0,1',
            'is_super'  => 'sometimes|bail|in:0,1',
            //'icon'           => 'required|bail|in:0,1',
            //'note'           => 'required|bail,
        ];
        if ($request->input('parent_id') > 0) {
            $rules['parent_id'] = 'required|exists:menu,id';
        }

        $this->validator($rules, $this->customAttributes);
        $menu            = new Menu();
        $menu->name      = $request->input('name');
        $menu->permit    = $request->input('permit', '');
        $menu->perms     = $request->input('perms', '');
        $menu->parent_id = $request->input('parent_id');
        $menu->listsort  = $request->input('listsort');
        $menu->is_frame  = $request->input('is_frame', 0) ?? 0;
        $menu->component = $request->input('component', '');
        $menu->path      = $request->input('path', '');
        $menu->type      = $request->input('type');
        $menu->status    = $request->input('status', 1);
        $menu->visible   = $request->input('visible', 1);
        $menu->is_super  = $request->input('is_super', 0);
        $menu->icon      = $request->input('icon', '');
        $menu->save();
        if ($menu->parent_id == 0) {
            $menu->level = '0,' . $menu->id;
        } else {
            $menu->level = Menu::where('id', $menu->parent_id)->value('level') . ',' . $menu->id;
        }
        $menu->save();
        return $this->success();
    }

    public function show(Request $request, Menu $menu)
    {
        return $this->success($menu->toArray());
    }

    public function update(Request $request, Menu $menu)
    {
        $rules     = [
            'name'      => 'required|bail',
            //            'permit'    => 'required_with:api_path',
            //            'perms'     => 'required_with:api_path',
            'parent_id' => 'required|min:0|bail|numeric',
            'listsort'  => 'required|bail|numeric|between:0,99',
            'is_frame'  => 'required_if:type,M,C|bail|in:0,1',
            //'component'      => 'required|bail|in:0,1',
            'path'      => 'exclude_unless:is_frame,1|required|url',
            'type'      => 'required|bail|in:M,F,C',
            'status'    => 'required|bail|in:0,1',
            'visible'   => 'sometimes|bail|in:0,1',
            'is_super'  => 'sometimes|bail|in:0,1',
            //'icon'           => 'required|bail|in:0,1',
            //'note'           => 'required|bail,
        ];
        $parent_id = $request->input('parent_id');
        if ($parent_id > 0) {
            $rules['parent_id'] = "required|not_in:{$menu->id}|exists:menu,id";
        }

        $this->validator($rules, $this->customAttributes);

        if ($parent_id > 0) {
            $parentLevel = Menu::where('id', $parent_id)->value('level');
            if (stripos($parentLevel, $menu->level . ',') !== false) {
                throw new ApiException('不允许修改为自己的下级子菜单');
            }
            $newLevel = $parentLevel . ',' . $menu->id;
        } else {
            $newLevel = '0,' . $menu->id;
        }
        if ($parent_id != $menu->parent_id) {
            //查找自己所有的子级 包括自己
            $childMenus = Menu::whereRaw("FIND_IN_SET('$menu->id', `level`)")->get();
            if ($childMenus) {
                foreach ($childMenus as $childMenu) {
                    if ($childMenu->id != $menu->id) {
                        $childLevel       = str_replace([$menu->level . ','], $newLevel . ',', $childMenu->level);
                        $childMenu->level = $childLevel;
                        $childMenu->save();
                    }
                }
            }
        }
        $menu->name      = $request->input('name');
        $menu->permit    = $request->input('permit', '');
        $menu->perms     = $request->input('perms', '');
        $menu->parent_id = $request->input('parent_id');
        $menu->listsort  = $request->input('listsort');
        $menu->is_frame  = $request->input('is_frame', 0) ?? 0;
        $menu->component = $request->input('component', '');
        $menu->path      = $request->input('path', '');
        $menu->type      = $request->input('type');
        $menu->status    = $request->input('status', 1);
        $menu->visible   = $request->input('visible', 1);
        $menu->is_super  = $request->input('is_super', 0);
        $menu->icon      = $request->input('icon', '');
        $menu->level     = $newLevel;
        $menu->save();
        return $this->success();
    }

    public function destroy(Menu $menu)
    {
        $childMenus = Menu::whereRaw("FIND_IN_SET('$menu->id', `level`)")->where('id', '!=', $menu->id)->first();
        if ($childMenus) {
            throw new ApiException('不能删除含有子菜单的菜单');
        }
        $menu->roles()->detach();
        $menu->delete();
        return $this->success();
    }
}
