<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AnnouncementEvaluator extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'announcement_id',
        'evaluator_user_id',
        'assignment_type',
        'scope_type',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * 연결된 공고
     */
    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * 심사위원 (사용자)
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }
}
