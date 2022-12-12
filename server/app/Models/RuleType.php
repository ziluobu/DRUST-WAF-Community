<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\RuleType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RuleType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RuleType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RuleType query()
 * @method static \Illuminate\Database\Eloquent\Builder|RuleType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RuleType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RuleType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RuleType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RuleType extends Model
{
    use HasFactory;

    protected $table = 'rule_type';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    //获取类型
    public static function type()
    {
        return self::pluck('name', 'id')->toArray();
    }

    public static function GetTypeName($log = 0)
    {
        $list    = self::pluck('name', 'id')->toArray();
        $list[0] = 'default';
        if ($log) {
            $list[590000] = '国外IP阻断';
            $list[590002] = '黑名单';
        }
        //        $list[999] = '国外IP阻断';
        return $list;
    }
}
