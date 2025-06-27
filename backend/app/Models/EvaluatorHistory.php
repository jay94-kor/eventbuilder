<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EvaluatorHistory extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'evaluator_user_id',
        'announcement_id',
        'proposal_id',
        'element_type',
        'project_id',
        'project_name',
        'evaluation_score',
        'evaluation_completed',
        'evaluation_completed_at',
        'evaluation_notes',
    ];

    protected $casts = [
        'id' => 'string',
        'evaluator_user_id' => 'string',
        'announcement_id' => 'string',
        'proposal_id' => 'string',
        'project_id' => 'string',
        'evaluation_score' => 'decimal:2',
        'evaluation_completed' => 'boolean',
        'evaluation_completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    // 관계 정의
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // 스코프 메서드들
    public function scopeByElement($query, $elementType)
    {
        return $query->where('element_type', $elementType);
    }

    public function scopeCompleted($query)
    {
        return $query->where('evaluation_completed', true);
    }

    public function scopeByEvaluator($query, $userId)
    {
        return $query->where('evaluator_user_id', $userId);
    }

    // 통계 메서드들
    public static function getTopEvaluatorsByElement($elementType, $limit = 5)
    {
        return self::select('evaluator_user_id')
            ->selectRaw('COUNT(*) as evaluation_count')
            ->selectRaw('AVG(evaluation_score) as avg_score')
            ->where('element_type', $elementType)
            ->where('evaluation_completed', true)
            ->groupBy('evaluator_user_id')
            ->orderByDesc('evaluation_count')
            ->orderByDesc('avg_score')
            ->with('evaluator:id,name,email')
            ->limit($limit)
            ->get();
    }

    public static function getEvaluatorExpertise($userId)
    {
        return self::select('element_type')
            ->selectRaw('COUNT(*) as evaluation_count')
            ->selectRaw('AVG(evaluation_score) as avg_score')
            ->selectRaw('MAX(evaluation_completed_at) as last_evaluation_at')
            ->where('evaluator_user_id', $userId)
            ->where('evaluation_completed', true)
            ->groupBy('element_type')
            ->orderByDesc('evaluation_count')
            ->get();
    }
} 