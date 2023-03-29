<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\Opertlog
 *
 * @property int $id 日志主键
 * @property string|null $module 模块标题
 * @property string|null $request_method 请求方式
 * @property int|null $admin_id
 * @property string|null $username 操作人员
 * @property string|null $oper_url 请求URL
 * @property string|null $oper_ip 主机地址
 * @property string|null $oper_location 操作地点
 * @property string|null $oper_param 请求参数
 * @property string|null $json_result 返回参数
 * @property int|null $status 状态码
 * @property int|null $api_status 接口状态码
 * @property \Illuminate\Support\Carbon|null $created_at 操作时间
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Opertlog newModelQuery()
 * @method static Builder|Opertlog newQuery()
 * @method static Builder|Opertlog query()
 * @method static Builder|Opertlog whereAdminId($value)
 * @method static Builder|Opertlog whereApiStatus($value)
 * @method static Builder|Opertlog whereCreatedAt($value)
 * @method static Builder|Opertlog whereId($value)
 * @method static Builder|Opertlog whereJsonResult($value)
 * @method static Builder|Opertlog whereModule($value)
 * @method static Builder|Opertlog whereOperIp($value)
 * @method static Builder|Opertlog whereOperLocation($value)
 * @method static Builder|Opertlog whereOperParam($value)
 * @method static Builder|Opertlog whereOperUrl($value)
 * @method static Builder|Opertlog whereRequestMethod($value)
 * @method static Builder|Opertlog whereStatus($value)
 * @method static Builder|Opertlog whereUpdatedAt($value)
 * @method static Builder|Opertlog whereUsername($value)
 * @mixin \Eloquent
 */
class Opertlog extends Model
{
    use HasFactory;

    protected $table = 'opertlog';
    public static $modules = [
        'manage'      => '管理员模块',
        'role'        => '角色模块',
        'menu'        => '菜单模块',
        'loginlog'    => '日志模块',
        'opertlog'    => '日志模块',
        'config'      => '配置模块',
        'web'         => '站点模块',
        'ipblack'     => '访问控制',
        'ipallow'     => '访问控制',
        'group'       => '单位模块',
        'assets'      => '资产模块',
        'ruleType'    => '策略分类',
        'webRules'    => '网站策略',
        'globalRules' => '全局策略',
        'sysRules'    => '系统策略',
        'whiteRules'  => '其他策略',
        'other'       => '其他',
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getOperParamAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getJsonResultAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 模型的 "booted" 方法
     *
     * @return void
     */
    protected static function booted()
    {
        $user_id = request()->input('user_id');
        if ($user_id > 1) {
            static::addGlobalScope('admin_id', function (Builder $builder) use ($user_id) {
                $builder->where('admin_id', $user_id);
            });
        }
    }
}
