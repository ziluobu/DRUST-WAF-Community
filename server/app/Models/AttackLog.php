<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AttackLog
 *
 * @property int $id
 * @property string|null $web_name 域名
 * @property string|null $url
 * @property int|null $type_id
 * @property string|null $type_name
 * @property int|null $rule_id
 * @property int|null $status
 * @property string|null $attack_ip
 * @property string|null $method
 * @property string|null $Time
 * @property string|null $PartC
 * @property string|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereAttackIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog wherePartC($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttackLog whereWebName($value)
 * @mixin \Eloquent
 */
class AttackLog extends Model
{

    protected $table = 'attack_log';

    protected $primaryKey = 'id';
    protected $guarded = [];

    public $timestamps = false;
    protected $connection = 'mysql-log';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = 'attack_log_' . date('Ym');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
