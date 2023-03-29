<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\Loginlog
 *
 * @property int $id 访问ID
 * @property string|null $login_name 登录账号
 * @property string|null $ipaddr 登录IP地址
 * @property string|null $login_location 登录地点
 * @property string|null $browser 浏览器类型
 * @property string|null $os 操作系统
 * @property string|null $net
 * @property string|null $status 登录状态（1成功 0失败）
 * @property string|null $msg 提示消息
 * @property \Illuminate\Support\Carbon|null $created_at 访问时间
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Loginlog newModelQuery()
 * @method static Builder|Loginlog newQuery()
 * @method static Builder|Loginlog query()
 * @method static Builder|Loginlog whereBrowser($value)
 * @method static Builder|Loginlog whereCreatedAt($value)
 * @method static Builder|Loginlog whereId($value)
 * @method static Builder|Loginlog whereIpaddr($value)
 * @method static Builder|Loginlog whereLoginLocation($value)
 * @method static Builder|Loginlog whereLoginName($value)
 * @method static Builder|Loginlog whereMsg($value)
 * @method static Builder|Loginlog whereNet($value)
 * @method static Builder|Loginlog whereOs($value)
 * @method static Builder|Loginlog whereStatus($value)
 * @method static Builder|Loginlog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Loginlog extends Model
{
    use HasFactory;

    protected $table = 'loginlog';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 模型的 "booted" 方法
     *
     * @return void
     */
    protected static function booted()
    {
        $user_id  = request()->input('user_id');
        if ($user_id > 1) {
            $username = request()->input('loginUsername');
            static::addGlobalScope('login_name', function (Builder $builder) use ($username) {
                $builder->where('login_name', $username);
            });
        }

    }
}
