<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ElementDefinition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'element_type',
        'display_name',
        'description',
        'input_schema',
        'default_details_template',
<<<<<<< Updated upstream
        'recommended_elements',
=======
        'complexity_level',
        'event_types',
        
        // 🆕 동적 스펙 템플릿 관리 필드들
        'default_spec_template',
        'quantity_config',
        'variant_rules',
>>>>>>> Stashed changes
    ];

    protected $casts = [
        'input_schema' => 'array',
        'default_details_template' => 'array',
        'recommended_elements' => 'array',
<<<<<<< Updated upstream
    ];
=======
        'event_types' => 'array',
        'popularity_score' => 'integer',
        
        // 🆕 동적 스펙 템플릿 캐스팅
        'default_spec_template' => 'array',
        'quantity_config' => 'array',
        'variant_rules' => 'array',
    ];

    /**
     * 이 요소가 속한 카테고리
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 이 요소가 추천하는 다른 요소들 (source → recommended)
     */
    public function recommendedElements()
    {
        return $this->belongsToMany(ElementDefinition::class, 'element_recommendations', 'source_element_id', 'recommended_element_id')
            ->withPivot(['recommendation_type', 'strength', 'reason'])
            ->withTimestamps();
    }

    /**
     * 이 요소를 추천하는 다른 요소들 (recommended ← source)
     */
    public function sourceElements()
    {
        return $this->belongsToMany(ElementDefinition::class, 'element_recommendations', 'recommended_element_id', 'source_element_id')
            ->withPivot(['recommendation_type', 'strength', 'reason'])
            ->withTimestamps();
    }

    // 🆕 스펙 템플릿 생성 헬퍼
    public function createSpecFields(): array
    {
        if (empty($this->default_spec_template)) {
            return [];
        }
        
        return collect($this->default_spec_template)->map(function ($template) {
            return [
                'id' => \Str::uuid(),
                'name' => $template['name'],
                'unit' => $template['unit'] ?? null,
                'value' => $template['default_value'] ?? '',
                'type' => $template['type'],
                'options' => $template['options'] ?? null,
                'required' => $template['required'] ?? false,
                'validation' => $template['validation'] ?? null,
            ];
        })->toArray();
    }
    
    // 🆕 수량 설정 정보 접근자
    public function getQuantityUnit(): string
    {
        return $this->quantity_config['unit'] ?? '개';
    }
    
    public function getTypicalQuantity(): int
    {
        return $this->quantity_config['typical'] ?? 1;
    }
    
    public function getQuantityRange(): array
    {
        return [
            'min' => $this->quantity_config['min'] ?? 1,
            'max' => $this->quantity_config['max'] ?? 10,
        ];
    }
    
    public function allowsVariants(): bool
    {
        return $this->quantity_config['allow_variants'] ?? false;
    }
    
    // 🆕 변형 규칙 접근자
    public function getAllowedVariantFields(): array
    {
        return $this->variant_rules['allowed_fields'] ?? [];
    }
    
    public function getMaxVariants(): int
    {
        return $this->variant_rules['max_variants'] ?? 5;
    }
    
    public function requiresVariantName(): bool
    {
        return $this->variant_rules['require_name'] ?? false;
    }
>>>>>>> Stashed changes
}