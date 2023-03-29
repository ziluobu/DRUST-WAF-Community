<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\Assets
 *
 * @property int $id
 * @property int|null $group_id
 * @property string $ip
 * @property string $contact
 * @property string $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Assets newModelQuery()
 * @method static Builder|Assets newQuery()
 * @method static Builder|Assets query()
 * @method static Builder|Assets whereContact($value)
 * @method static Builder|Assets whereCreatedAt($value)
 * @method static Builder|Assets whereGroupId($value)
 * @method static Builder|Assets whereId($value)
 * @method static Builder|Assets whereIp($value)
 * @method static Builder|Assets wherePhone($value)
 * @method static Builder|Assets whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Assets extends Model
{
    use HasFactory;

    protected $table = 'assets';

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
            static::addGlobalScope('group_id', function (Builder $builder) use ($group_id) {
                $builder->where('group_id', $group_id);
            });
        }

    }
}
