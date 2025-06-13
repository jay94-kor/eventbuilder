<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'description',
        'category_id',
        'sort_order',
        'is_active',
        'is_premium',
        'config',
        'budget_allocation',
        'internal_resource_flag',
    ];

    protected $hidden = [
        // 순환 참조를 유발할 수 있는 관계를 숨김
        // 'recommendations',
        // 'recommendedBy',
        // 필요한 경우 여기에 다른 숨길 필드를 추가
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'config' => 'json',
        'budget_allocation' => 'boolean',
        'internal_resource_flag' => 'boolean',
    ];

    /**
     * Feature는 하나의 FeatureCategory에 속합니다.
     */
    public function category()
    {
        return $this->belongsTo(FeatureCategory::class, 'category_id');
    }

    /**
     * Feature는 여러 개의 RfpSelection을 가질 수 있습니다.
     */
    public function rfpSelections()
    {
        return $this->hasMany(RfpSelection::class);
    }

    /**
     * 이 기능이 추천하는 다른 기능들
     */
    public function recommendations()
    {
        return $this->belongsToMany(Feature::class, 'feature_recommendation', 'feature_id', 'recommended_feature_id')
                    ->withPivot('level', 'priority');
    }

    /**
     * 이 기능을 추천하는 다른 기능들
     */
    public function recommendedBy()
    {
        return $this->belongsToMany(Feature::class, 'feature_recommendation', 'recommended_feature_id', 'feature_id');
    }
} 