<?php

namespace App\Models;

use App\Jobs\ZdyReportJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\ZdyReport
 *
 * @property string $id
 * @property string $username
 * @property string $reportname
 * @property string|null $path
 * @property int $admin_id
 * @property string $web_ids
 * @property int $group_id
 * @property string $begin_time
 * @property string $end_time
 * @property string|null $note
 * @property int $status 0生成中，1已生成，2生成失败
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|ZdyReport newModelQuery()
 * @method static Builder|ZdyReport newQuery()
 * @method static Builder|ZdyReport query()
 * @method static Builder|ZdyReport whereAdminId($value)
 * @method static Builder|ZdyReport whereBeginTime($value)
 * @method static Builder|ZdyReport whereCreatedAt($value)
 * @method static Builder|ZdyReport whereEndTime($value)
 * @method static Builder|ZdyReport whereGroupId($value)
 * @method static Builder|ZdyReport whereId($value)
 * @method static Builder|ZdyReport whereNote($value)
 * @method static Builder|ZdyReport wherePath($value)
 * @method static Builder|ZdyReport whereReportname($value)
 * @method static Builder|ZdyReport whereStatus($value)
 * @method static Builder|ZdyReport whereUpdatedAt($value)
 * @method static Builder|ZdyReport whereUsername($value)
 * @method static Builder|ZdyReport whereWebIds($value)
 * @mixin \Eloquent
 */
class ZdyReport extends Model
{
    use HasFactory;

    protected $table = 'zdy_report';
    public $incrementing = false;
    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getWebIdsAttribute($value)
    {
        return json_decode($value, true);
    }

    /*public function getPathAttribute($value)
    {
        $path = '';
        if ($value) {
            $path = \Storage::url($value);
        }
        return $path;
    }*/

    public function setWebIdsAttribute($value)
    {
        $this->attributes['web_ids'] = json_encode(array_values($value));
    }

    /**
     * 模型的 "booted" 方法
     *
     * @return void
     */
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
        static::created(function ($report) {
            ZdyReportJob::dispatch($report->toArray())->onQueue('zdyreport')->delay(now()->addMinutes());
        });
    }
}
