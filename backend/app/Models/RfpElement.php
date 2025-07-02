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
        
        // 🆕 동적 스펙 관리 필드들
        'total_quantity',
        'base_quantity',
        'use_variants',
        'spec_fields',
        'spec_variants',
    ];

    protected $casts = [
<<<<<<< Updated upstream
        'details' => 'array', // JSONB 필드를 배열로 자동 캐스팅
=======
        'details' => 'array', // JSONB 필드를 배열로 자동 캐스팅 (하위 호환성)
        'prepayment_due_date' => 'datetime',
        'balance_due_date' => 'datetime',
        
        // 🆕 동적 스펙 캐스팅
        'spec_fields' => 'array',
        'spec_variants' => 'array',
        'use_variants' => 'boolean',
>>>>>>> Stashed changes
    ];

    public function rfp()
    {
        return $this->belongsTo(Rfp::class);
    }
<<<<<<< Updated upstream
=======

    public function elementDefinition()
    {
        return $this->belongsTo(ElementDefinition::class);
    }
    
    // 🆕 스펙 값 접근자
    public function getSpecValue(string $fieldName): mixed
    {
        $field = collect($this->spec_fields)->firstWhere('name', $fieldName);
        return $field['value'] ?? null;
    }

    // 🆕 변형별 스펙 값 접근자  
    public function getVariantSpecValue(string $variantId, string $fieldName): mixed
    {
        $variant = collect($this->spec_variants)->firstWhere('id', $variantId);
        return $variant['spec_values'][$fieldName] ?? $this->getSpecValue($fieldName);
    }
    
    // 🆕 수량 검증 헬퍼
    public function validateQuantities(): array
    {
        $errors = [];
        
        $variantTotal = collect($this->spec_variants)->sum('quantity');
        $calculatedTotal = $this->base_quantity + $variantTotal;
        
        if ($calculatedTotal !== $this->total_quantity) {
            $errors[] = "총 수량({$this->total_quantity})과 실제 수량({$calculatedTotal})이 일치하지 않습니다.";
        }
        
        if ($this->base_quantity < 0) {
            $errors[] = "기본 스펙 수량은 0 이상이어야 합니다.";
        }
        
        return $errors;
    }
    
    // 🆕 스펙 필드 검증 헬퍼
    public function validateSpecFields(): array
    {
        $errors = [];
        
        foreach ($this->spec_fields as $field) {
            if (!empty($field['required']) && (empty($field['value']) && $field['value'] !== 0)) {
                $errors[] = "{$field['name']}은(는) 필수 입력 항목입니다.";
            }
            
            if ($field['type'] === 'number' && !empty($field['validation'])) {
                $numValue = (float) $field['value'];
                if (!empty($field['validation']['min']) && $numValue < $field['validation']['min']) {
                    $errors[] = "{$field['name']}은(는) {$field['validation']['min']} 이상이어야 합니다.";
                }
                if (!empty($field['validation']['max']) && $numValue > $field['validation']['max']) {
                    $errors[] = "{$field['name']}은(는) {$field['validation']['max']} 이하여야 합니다.";
                }
            }
        }
        
        return $errors;
    }
>>>>>>> Stashed changes
}
