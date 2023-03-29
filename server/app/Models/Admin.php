<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Admin
 *
 * @property int $id
 * @property string $username 登录名
 * @property string $password 登录密码
 * @property int|null $group_id
 * @property string $realname 人员姓名
 * @property string|null $email
 * @property string|null $avatar
 * @property string $status 状态
 * @property string|null $open_id 微信openid
 * @property string|null $note 备注
 * @property string|null $last_time 登录时间
 * @property string|null $last_ip 登录ip
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AdminRole[] $adminRole
 * @property-read int|null $admin_role_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static Builder|Admin newModelQuery()
 * @method static Builder|Admin newQuery()
 * @method static Builder|Admin query()
 * @method static Builder|Admin whereAvatar($value)
 * @method static Builder|Admin whereCreatedAt($value)
 * @method static Builder|Admin whereEmail($value)
 * @method static Builder|Admin whereGroupId($value)
 * @method static Builder|Admin whereId($value)
 * @method static Builder|Admin whereLastIp($value)
 * @method static Builder|Admin whereLastTime($value)
 * @method static Builder|Admin whereNote($value)
 * @method static Builder|Admin whereOpenId($value)
 * @method static Builder|Admin wherePassword($value)
 * @method static Builder|Admin whereRealname($value)
 * @method static Builder|Admin whereStatus($value)
 * @method static Builder|Admin whereUpdatedAt($value)
 * @method static Builder|Admin whereUsername($value)
 * @mixin \Eloquent
 */
class Admin extends Model
{
    //
    protected $table = 'admin';
    protected $primaryKey = 'id';

    protected $hidden = [
        'password',
        'open_id',
        'avatar',
    ];

    /**
     * 模型的 "booted" 方法
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('id', function (Builder $builder) {
            $builder->where('id', '>', 1);
        });
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function getUserNameById($id)
    {
        if ($id) {
            return self::where('id', $id)->withoutGlobalScopes()->value('username');
        }
        return '';
    }

    public function adminRole()
    {
        return $this->hasMany(AdminRole::class, 'admin_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, AdminRole::class, 'admin_id', 'role_id');
    }
}
