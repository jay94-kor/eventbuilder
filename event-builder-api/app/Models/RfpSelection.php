<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfpSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfp_id',
        'feature_id',
        'details',
        'allocated_budget',
        'is_budget_undecided',
    ];

    protected $casts = [
        'details' => 'array',
        'allocated_budget' => 'integer',
        'is_budget_undecided' => 'boolean',
    ];

    protected $hidden = [
        'rfp', // rfp 관계를 숨김
    ];

    /**
     * RfpSelection은 하나의 Rfp에 속합니다.
     */
    public function rfp()
    {
        return $this->belongsTo(Rfp::class);
    }

    /**
     * RfpSelection은 하나의 Feature에 속합니다.
     */
    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
} 