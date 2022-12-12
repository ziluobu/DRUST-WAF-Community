<?php

namespace App\Models;

use App\Events\SynIptablesActionEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\IpBlack
 *
 * @property int $id
 * @property string $ip ip
 * @property int $admin_id 操作人id
 * @property int|null $type 0 管理员 1导入 2触发规则
 * @property int $black_type 0永久，1其他
 * @property int $expire_time 过期时间戳 0 永久
 * @property int $rule_id
 * @property string|null $reason 原因
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack query()
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereBlackType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereExpireTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpBlack whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IpBlack extends Model
{
    use HasFactory;

    protected $table = 'ip_black';
    protected $guarded = [];
    public static $types = ['管理员添加', 'execl导入', '规则封禁', '触发蜜罐'];

    protected $hidden = [
        // 'type'
    ];

    protected static function booted()
    {
        //todo 测试环境下不开启
        if (env('APP_ENV') == 'production') {
            static::saved(function ($data) {
                event(new SynIptablesActionEvent(0, [$data->ip => 1]));
            });
            static::deleted(function ($data) {
                event(new SynIptablesActionEvent(1, [$data->ip => 1]));
            });
        }
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
