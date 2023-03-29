<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\Menu
 *
 * @property int $id
 * @property string $name 菜单名称
 * @property string|null $permit 权限标识(后端判断)
 * @property string|null $perms 权限标识(前端判断)
 * @property int $parent_id 父菜单ID
 * @property int $listsort 显示顺序
 * @property int $is_frame 1外链 http/s开头
 * @property string|null $component 组件路径
 * @property string|null $path 路由地址
 * @property string $type 菜单类型（M目录 C菜单 F按钮）
 * @property int $status 菜单状态（1正常 0停用）
 * @property int $is_super 1超级管理员权限
 * @property int $visible 1显示，0隐藏
 * @property string|null $icon 菜单图标
 * @property string|null $note 备注
 * @property string|null $level 关系图
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RoleMenu[] $roleMenus
 * @property-read int|null $role_menus_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereComponent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereIsFrame($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereIsSuper($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereListsort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu wherePermit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu wherePerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereVisible($value)
 * @mixin \Eloquent
 */
class Menu extends Model
{
    protected $table = 'menu';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function setPermitAttribute($value)
    {
        $this->attributes['permit'] = strtolower($value);
    }

    public static function getPermissions()
    {
        $adminId = request()->input('user_id');
        if ($adminId == 1) {
            $permissions[] = '*.*';
        } else {
            $permissions = self::when($adminId > 1, function ($query) use ($adminId) {
                $roleIds = AdminRole::where('admin_id', $adminId)->pluck('role_id')->toArray();
                $menuIds = [];
                if ($roleIds) {
                    $menuList = RoleMenu::whereIn('role_id', $roleIds)
                        ->pluck('menu_id')->toArray();
                    $menuIds  = array_unique($menuList);
                }
                return $query->whereIn('id', $menuIds);
            })
                ->where('status', 1)
                ->where('is_super', 0)
                ->where('permit', '!=', '')
                ->whereNotNull('permit')
                ->orderBy('listsort')
                ->pluck('permit')
                ->toArray();
        }

        return $permissions;
    }

    public static function getRouteList()
    {
        $adminId  = request()->input('user_id');
        $menuList = self::when($adminId > 1, function ($query) use ($adminId) {
            $roleIds = AdminRole::where('admin_id', $adminId)->pluck('role_id')->toArray();
            $menuIds = [];
            //$exceptIds = self::whereIn('permit', getAdminPermit())->pluck('menu_id')->toArray();
            if ($roleIds) {
                $menuList = RoleMenu::whereIn('role_id', $roleIds)
                    ->pluck('menu_id')->toArray();
                $menuIds  = array_unique($menuList);
            }
            return $query->whereIn('id', $menuIds)->where('is_super', 0);
        })
            ->where('status', 1)
            ->where('type', '!=', 'F')
            ->orderBy('listsort')
            ->get()
            ->toArray();

        $menuTree = self::getRouteTree($menuList);
        $index    = [
            'name'      => '',
            'path'      => '',
            'hidden'    => 'true',
            'component' => '/workbench_report/index',
            'meta'      => [
                'icon'  => 'el-icon-s-home',
                'link'  => '',
                'title' => '首页',
            ],
            'children'  => [
                [
                    'name'      => '/workbenchReport',
                    'path'      => '/workbenchReport',
                    'hidden'    => 'true',
                    'component' => '/workbench_report/index',
                    'meta'      => [
                        'icon'  => 'el-icon-monitor',
                        'link'  => '',
                        'title' => '工作台',
                    ],
                    'children'  => [],
                ]
            ]
        ];
        array_unshift($menuTree, $index);
        $new[0]['name']          = '';
        $new[0]['path']          = '';
        $new[0]['hidden']        = 'false';
        $new[0]['component']     = 'layout';
        $new[0]['meta']['title'] = '主目录';
        $new[0]['meta']['icon']  = '';
        $new[0]['meta']['link']  = '';
        $new[0]['children']      = $menuTree;
        return $new;
    }

    /**
     * 将菜单转为数形无限极
     * @param $list
     * @param int $pid
     * @param int $deep
     * @return array
     */
    private static function getRouteTree($list, $pid = 0, $deep = 0)
    {
        $tree = [];
        foreach ($list as $key => $row) {
            if ($row['parent_id'] == $pid) {
                unset($list[$key]);
                //$row['children']          = self::getMenuTree($list, $row['id'], $deep + 1);
                $info['name']          = ucfirst($row['path']);
                $info['path']          = $row['path'];
                $info['hidden']        = $row['visible'] ? true : false;
                $info['component']     = $row['component'];
                $info['meta']['title'] = $row['name'];
                $info['meta']['icon']  = $row['icon'];
                $info['meta']['link']  = $row['is_frame'] ? $row['path'] : null;
                $info['children']      = self::getRouteTree($list, $row['id'], $deep + 1);
                /*if (!$info['children']) {
                    unset($info['children']);
                }*/
                $tree[] = $info;
            }
        }
        return $tree;
    }

    protected static function getSearchTree($list, $pid = 0, $deep = 0)
    {
        $tree = [];
        foreach ($list as $key => $row) {
            if ($row['parent_id'] == $pid) {
                unset($list[$key]);
                $info['name']     = $row['name'];
                $info['id']       = $row['id'];
                $info['children'] = self::getSearchTree($list, $row['id'], $deep + 1);
                /*if (!$info['children']) {
                    unset($info['children']);
                }*/
                $tree[] = $info;
            }
        }
        return $tree;
    }

    public static function getPermit($adminId)
    {
        $roleIds = AdminRole::where('admin_id', $adminId)->pluck('role_id')->toArray();
        if (!$roleIds) {
            return [];
        }
        $redis   = app('redis')->connection('cache');
        $permits = $redis->hmget('permitList', $roleIds);

        $list = [];
        foreach ($permits as $permit) {
            $permit = json_decode($permit, true);
            $list   = array_merge($list, $permit);
        }
        return array_unique($list);
    }


    public function roleMenus()
    {
        return $this->hasMany(RoleMenu::class, 'menu_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, RoleMenu::class, 'menu_id', 'role_id');
    }
}
