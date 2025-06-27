<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ScheduleAttachment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    /**
     * 이 첨부 파일이 속한 스케줄.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * 파일을 업로드한 사용자.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
