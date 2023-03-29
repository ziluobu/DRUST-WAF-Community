<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * App\Models\MonthReport
 *
 * @property string $id
 * @property string|null $reportname
 * @property string|null $path
 * @property int $group_id
 * @property string $begin_time
 * @property string $end_time
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|MonthReport newModelQuery()
 * @method static Builder|MonthReport newQuery()
 * @method static Builder|MonthReport query()
 * @method static Builder|MonthReport whereBeginTime($value)
 * @method static Builder|MonthReport whereCreatedAt($value)
 * @method static Builder|MonthReport whereEndTime($value)
 * @method static Builder|MonthReport whereGroupId($value)
 * @method static Builder|MonthReport whereId($value)
 * @method static Builder|MonthReport wherePath($value)
 * @method static Builder|MonthReport whereReportname($value)
 * @method static Builder|MonthReport whereStatus($value)
 * @method static Builder|MonthReport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MonthReport extends Model
{
    use HasFactory;

    protected $table = 'month_report';
    public $incrementing = false;
    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /*public function getPathAttribute($value)
    {
        $path = '';
        if ($value) {
            $path = \Storage::url($value);
        }
        return $path;
    }*/
    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string)\Str::uuid();
            }
        });

        $group_id = request()->input('user_group_id');
        if ($group_id > 0 && request()->input('user_id') != 1) {
            static::addGlobalScope('group_id', function (Builder $builder) use ($group_id) {
                $builder->where('group_id', $group_id);
            });
        }

    }
}
