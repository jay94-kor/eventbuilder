<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Proposal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'announcement_id',
        'vendor_id',
        'proposed_price',
        'proposal_text',
        'proposal_file_path',
        'status',
        'reserve_rank',
        'evaluation_process_status',
        'presentation_order',
        'presentation_scheduled_at',
        'presentation_duration_minutes',
    ];

    protected $casts = [
        'proposed_price' => 'decimal:2', // 금액은 소수점 2자리까지
        'evaluation_process_status' => 'array',
        'presentation_scheduled_at' => 'datetime',
    ];

    /**
     * 이 제안서가 속한 공고.
     */
    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * 이 제안서를 제출한 용역사.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * 이 제안서에 대한 평가들.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * 이 제안서와 연결된 계약 (낙찰된 경우).
     */
    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
}