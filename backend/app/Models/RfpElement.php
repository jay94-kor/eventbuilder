<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // HasUuids 트레이트 추가

class RfpElement extends Model
{
    use HasFactory, HasUuids; // HasFactory와 HasUuids 트레이트 사용

    protected $fillable = [
        'rfp_id',
        'element_type',
        'details',
        'allocated_budget',
        'prepayment_ratio',
        'prepayment_due_date',
        'balance_ratio',
        'balance_due_date',
    ];

    protected $casts = [
        'details' => 'array', // JSONB 필드를 배열로 자동 캐스팅
    ];

    public function rfp()
    {
        return $this->belongsTo(Rfp::class);
    }
}
