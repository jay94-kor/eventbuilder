<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ScheduleLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array', // JSONB 컬럼을 배열로 캐스팅
        'new_values' => 'array', // JSONB 컬럼을 배열로 캐스팅
    ];

    /**
     * 이 로그가 속한 스케줄.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * 로그를 생성한 사용자.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
