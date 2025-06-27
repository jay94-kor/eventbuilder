<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Schedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'schedulable_id',
        'schedulable_type',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'location',
        'status',
        'type',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'status' => 'string',
        'type' => 'string',
    ];

    /**
     * 스케줄이 속한 엔티티 (프로젝트 또는 공고)의 다형적 관계.
     */
    public function schedulable()
    {
        return $this->morphTo();
    }

    /**
     * 이 스케줄의 변경 이력들.
     */
    public function logs()
    {
        return $this->hasMany(ScheduleLog::class);
    }

    /**
     * 이 스케줄에 연결된 첨부 파일들.
     */
    public function attachments()
    {
        return $this->hasMany(ScheduleAttachment::class);
    }
}
