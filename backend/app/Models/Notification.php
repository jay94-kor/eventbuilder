<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'notification_type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 알림을 받을 사용자
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 읽음 처리
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * 읽지 않은 알림만 조회
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * 특정 타입의 알림만 조회
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }
} 