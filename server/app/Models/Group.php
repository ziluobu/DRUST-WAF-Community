<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property string $group_name 所属分类
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group query()
 * @method static Builder|Group whereCreatedAt($value)
 * @method static Builder|Group whereGroupName($value)
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Group extends Model
{
    use HasFactory;

    protected $table = 'group';
    // protected $primaryKey = 'id';

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
            static::addGlobalScope('id', function (Builder $builder) use ($group_id) {
                $builder->where('id', $group_id);
            });
        }

    }
}
