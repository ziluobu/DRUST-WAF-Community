<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AdminRole
 *
 * @property int $admin_id 用户ID
 * @property int $role_id 角色ID
 * @method static \Illuminate\Database\Eloquent\Builder|AdminRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminRole whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminRole whereRoleId($value)
 * @mixin \Eloquent
 */
class AdminRole extends Model
{
    protected $table = 'admin_role';
    public $incrementing = false;
    public $timestamps = false;
    public $guarded = [];
}
