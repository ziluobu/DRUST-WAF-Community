<?php

namespace App\Models;

use App\Events\SynIptablesActionEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\IpAllow
 *
 * @property int $id
 * @property string $ip ip
 * @property int $admin_id 操作人id
 * @property string|null $reason 原因
 * @property int $expire_time 过期时间戳
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow query()
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow whereExpireTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpAllow whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IpAllow extends Model
{
    use HasFactory;

    protected $table = 'ip_allow';
    protected $guarded = [];

    protected static function booted()
    {
        //todo 测试环境下不开启
        if (env('APP_ENV') == 'production') {
            static::saved(function ($data) {
                event(new SynIptablesActionEvent(2, [$data->ip => 1]));
            });
            static::deleted(function ($data) {
                event(new SynIptablesActionEvent(3, [$data->ip => 1]));
            });
        }
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
