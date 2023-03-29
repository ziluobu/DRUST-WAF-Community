<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\RulesGlobal
 *
 * @property int $id
 * @property string|null $request_uri 请求uri
 * @property string|null $request_method 请求方式
 * @property int|null $param_site 参数位置
 * @property string|null $param_content 请求参数
 * @property string $describe 描述
 * @property int|null $status
 * @property int|null $admin_id
 * @property int $is_black
 * @property int|null $black_type
 * @property int|null $black_num
 * @property int|null $black_time
 * @property int $type_id 类型
 * @property string|null $header_content
 * @property string|null $rule_content 完整的规则
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal query()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereBlackNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereBlackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereBlackType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereDescribe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereHeaderContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereIsBlack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereParamContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereParamSite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereRequestMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereRequestUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereRuleContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesGlobal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RulesGlobal extends Model
{
    use HasFactory;

    protected $table = 'rules_global';
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
        static::saved(function ($rules) {
            addRedisRuleType($rules->id + 450000, $rules->type_id);
            if ($rules->is_black == 1) {
                addBlackTime($rules->id + 450000, $rules->black_time);
            } else {
                delBlackTime($rules->id + 450000);
            }
            if ($rules->status != 0) {
                $rules->rule_content = generaGlobalRuleContent($rules);
            } else {
                $rules->rule_content = '';
            }
            $rules->saveQuietly();


        });
        static::deleted(function ($rules) {
            delRedisRuleType($rules->id + 450000);
            delBlackTime($rules->id + 450000);
        });
    }

}
