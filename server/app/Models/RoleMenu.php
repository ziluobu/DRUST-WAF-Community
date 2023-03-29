<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RoleMenu
 *
 * @property int $role_id 角色ID
 * @property int $menu_id 菜单ID
 * @method static \Illuminate\Database\Eloquent\Builder|RoleMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleMenu whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleMenu whereRoleId($value)
 * @mixin \Eloquent
 */
class RoleMenu extends Model
{
    protected $table = 'role_menu';
    public $timestamps = false;
    public $incrementing = false;
    public $guarded = [];
}
