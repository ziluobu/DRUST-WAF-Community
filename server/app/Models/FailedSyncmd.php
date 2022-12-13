<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FailedSyncmd
 *
 * @property int $id
 * @property int|null $type
 * @property string $name
 * @property string $ip
 * @property int $fail
 * @property string $exception
 * @property string|null $failed_time
 * @property string|null $create_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd query()
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereCreateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereException($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereFail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereFailedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FailedSyncmd whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FailedSyncmd extends Model
{
    use HasFactory;

    protected $table = 'failed_syncmd';

    protected $primaryKey = 'id';
    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
