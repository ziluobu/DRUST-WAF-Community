<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\RulesWhite
 *
 * @property int $id
 * @property int|null $remove_sysrule_id 移除的系统规则id
 * @property int $web_id 域名
 * @property string|null $request_uri 请求uri
 * @property string|null $request_method 请求方式
 * @property string $describe
 * @property string|null $rule_content 完整的规则
 * @property int|null $status
 * @property int|null $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite query()
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereDescribe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereRemoveSysruleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereRequestMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereRequestUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereRuleContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RulesWhite whereWebId($value)
 * @mixin \Eloquent
 */
class RulesWhite extends Model
{
    use HasFactory;

    protected $table = 'rules_white';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public static $request_method = ['GET', 'POST', 'OPTIONS', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'CONNECT'];
    public static $status = ['禁用', '开启'];

    public function getWeb()
    {
        return $this->belongsTo(Web::class, 'web_id', 'id');
    }

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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

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

            if ($rules->status != 0) {
                $rules->rule_content = generaWhiteRuleContent($rules);
            } else {
                $rules->rule_content = '';
            }
            $rules->saveQuietly();

        });

    }
}
