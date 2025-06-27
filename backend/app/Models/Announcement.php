<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // HasUuids 트레이트 추가

class Announcement extends Model
{
    use HasFactory, HasUuids; // HasUuids 트레이트 사용

    protected $fillable = [
        'rfp_id',
        'rfp_element_id',
        'agency_id',
        'title',
        'description',
        'estimated_price',
        'closing_at',
        'channel_type',
        'contact_info_private',
        'published_at',
        'status',
        'evaluation_criteria',
    ];

    protected $casts = [
        'closing_at' => 'datetime',
        'published_at' => 'datetime',
        'contact_info_private' => 'boolean',
        'evaluation_criteria' => 'array',
    ];

    /**
     * 공고와 연결된 RFP.
     */
    public function rfp()
    {
        return $this->belongsTo(Rfp::class);
    }

    /**
     * 공고와 연결된 RFP 요소 (분리 발주 시).
     */
    public function rfpElement()
    {
        return $this->belongsTo(RfpElement::class, 'rfp_element_id');
    }

    /**
     * 공고를 발행한 대행사.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * 이 공고에 제출된 제안서들.
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * 이 공고에 배정된 심사위원들.
     */
    public function evaluators()
    {
        return $this->hasMany(AnnouncementEvaluator::class);
    }

    /**
     * 이 공고의 제안서들에 대한 모든 평가.
     */
    public function evaluations()
    {
        return $this->hasManyThrough(Evaluation::class, Proposal::class);
    }

    /**
     * 이 공고에 연결된 스케줄들 (다형적 관계).
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }
}
