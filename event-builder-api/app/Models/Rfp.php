<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfp extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'event_date',
        'user_id',
        'total_budget',
        'is_total_budget_undecided',
        'expected_attendees',
        'description',
    ];

    protected $hidden = [
        'user', // user 관계를 숨김
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_total_budget_undecided' => 'boolean',
        'total_budget' => 'integer', // 예산은 정수형으로 캐스팅
        'expected_attendees' => 'integer', // 예상 참가자 수도 정수형으로 캐스팅
    ];

    /**
     * Rfp는 하나의 User에 속합니다.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Rfp는 여러 개의 RfpSelection을 가질 수 있습니다.
     */
    public function selections()
    {
        return $this->hasMany(RfpSelection::class);
    }
} 