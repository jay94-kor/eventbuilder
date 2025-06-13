<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'budget_allocation',
        'internal_resource_flag',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'budget_allocation' => 'boolean',
        'internal_resource_flag' => 'boolean',
    ];

    /**
     * FeatureCategory는 여러 개의 Feature를 가질 수 있습니다.
     */
    public function features()
    {
        return $this->hasMany(Feature::class, 'category_id')->where('is_active', true)->orderBy('sort_order');
    }
} 