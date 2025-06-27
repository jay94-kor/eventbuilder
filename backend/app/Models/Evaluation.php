<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Evaluation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'proposal_id',
        'evaluator_user_id',
        'price_score',
        'portfolio_score',
        'additional_score',
        'comment',
    ];

    protected $casts = [
        'price_score' => 'decimal:2',
        'portfolio_score' => 'decimal:2',
        'additional_score' => 'decimal:2',
    ];

    /**
     * 평가 대상 제안서
     */
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * 심사위원 (사용자)
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }
}
