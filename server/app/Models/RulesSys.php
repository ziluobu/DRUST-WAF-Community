<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\RulesSys
 *
 * @property int $id
 * @property string|null $rule_content
 * @property string|null $rule_type
 * @property int $type_id 类型
 * @property int $is_black 是否加入黑名单
 * @property int|null $black_type
 * @property int|null $black_num
 * @property int|null $black_time
 * @property string|null $black_append_rule
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys query()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereBlackAppendRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereBlackNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereBlackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereBlackType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereIsBlack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereRuleContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereRuleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesSys whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RulesSys extends Model
{
    use HasFactory;

    protected $table = 'rules_sys';

    protected $hidden = [
        'rule_type'
    ];

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
            addRedisRuleType($rules->id, $rules->type_id);
            if ($rules->is_black == 1) {
                addBlackTime($rules->id, $rules->black_time);
            } else {
                delBlackTime($rules->id);
            }
        });
    }
}
