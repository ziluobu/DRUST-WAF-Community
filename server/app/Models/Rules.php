<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\Rules
 *
 * @property int $id
 * @property int $web_id 域名
 * @property string|null $request_uri 请求uri
 * @property string|null $request_method 请求方式
 * @property int|null $param_site 参数位置
 * @property string|null $param_content 请求参数
 * @property string $describe
 * @property int $is_black 是否加入黑名单
 * @property int|null $black_type
 * @property int|null $black_num
 * @property int|null $black_time
 * @property int $type_id 类型
 * @property string|null $header_content
 * @property string|null $rule_content 完整的规则
 * @property int|null $status
 * @property int|null $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Rules newModelQuery()
 * @method static Builder|Rules newQuery()
 * @method static Builder|Rules query()
 * @method static Builder|Rules whereAdminId($value)
 * @method static Builder|Rules whereBlackNum($value)
 * @method static Builder|Rules whereBlackTime($value)
 * @method static Builder|Rules whereBlackType($value)
 * @method static Builder|Rules whereCreatedAt($value)
 * @method static Builder|Rules whereDescribe($value)
 * @method static Builder|Rules whereHeaderContent($value)
 * @method static Builder|Rules whereId($value)
 * @method static Builder|Rules whereIsBlack($value)
 * @method static Builder|Rules whereParamContent($value)
 * @method static Builder|Rules whereParamSite($value)
 * @method static Builder|Rules whereRequestMethod($value)
 * @method static Builder|Rules whereRequestUri($value)
 * @method static Builder|Rules whereRuleContent($value)
 * @method static Builder|Rules whereStatus($value)
 * @method static Builder|Rules whereTypeId($value)
 * @method static Builder|Rules whereUpdatedAt($value)
 * @method static Builder|Rules whereWebId($value)
 * @mixin \Eloquent
 */
class Rules extends Model
{
    use HasFactory;

    protected $table = 'rules';
    protected $guarded = [];
    public static $request_method = ['GET', 'POST', 'OPTIONS', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'CONNECT'];
    public static $param_site = [
        0 => '无',
        1 => '请求行',
        2 => '请求体',
        3 => 'header'
    ];
    public static $status = ['禁用', '阻断', '告警'];
    public static $black_types = ['永久', '小时', '天'];
    public static $headers = [
        'Connection'   => 'Connection',
        'Content-Type' => 'Content-Type',
        'User-Agent'   => 'User-Agent',
        'Referer'      => 'Referer',
        'Cookie'       => 'Cookie',
        'Origin'       => 'Origin'
    ];

    public static $Operators = [
        '@contains'     => '包含',
        '!@contains'    => '不包含',
        'containsWord'  => '包含(词)',
        '!containsWord' => '不包含(词)',
        '@eq'           => '等于',
        '!@eq'          => '不等于',
        '@rx'           => '正则',
    ];

    protected $hidden = ['header_content'];

    public function setRequestMethodAttribute($value)
    {
        if (!is_array($value)) {
            $value = [];
        }
        $value                              = array_intersect($value, self::$request_method);
        $this->attributes['request_method'] = json_encode($value);
    }

    public function getRequestMethodAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setParamContentAttribute($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (
                    !array_key_exists('key', $v) ||
                    !array_key_exists('operator', $v) ||
                    !array_key_exists('value', $v)
                ) {
                    unset($value[$k]);
                }
            }
        } else {
            $value = [];
        }
        $this->attributes['param_content'] = json_encode(array_values($value));
    }

    public function getWeb()
    {
        return $this->belongsTo(Web::class, 'web_id', 'id');
    }

    public function getParamContentAttribute($value)
    {
        return json_decode($value, true);
    }

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
        $group_id = request()->input('user_group_id');
        if ($group_id > 0 && request()->input('user_id') != 1) {
            $web_ids = Web::where('group_id', $group_id)->pluck('id')->toArray();
            static::addGlobalScope('web_id', function (Builder $builder) use ($web_ids) {
                $builder->whereIn('web_id', $web_ids);
            });
        }
        static::saved(function ($rules) {
            addRedisRuleType($rules->id + 440000, $rules->type_id);
            if ($rules->is_black == 1) {
                addBlackTime($rules->id + 440000, $rules->black_time);
            } else {
                delBlackTime($rules->id + 440000);
            }
            if ($rules->status != 0) {
                $rules->rule_content = generaWebRuleContent($rules);
            } else {
                $rules->rule_content = '';
            }
            $rules->saveQuietly();
        });
        static::deleted(function ($rules) {
            delRedisRuleType($rules->id + 440000);
            delBlackTime($rules->id + 440000);
        });
    }

}
