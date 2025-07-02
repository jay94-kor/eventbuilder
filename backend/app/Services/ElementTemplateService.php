<?php

namespace App\Services;

use App\Models\ElementDefinition;
use Illuminate\Validation\ValidationException;

class ElementTemplateService
{
    /**
     * 모든 ElementDefinition과 동적 스펙 템플릿 조회
     */
    public function getAllElementTemplates()
    {
        $elements = ElementDefinition::with('category:id,name,color')
            ->select([
                'id', 'display_name', 'element_type', 'description', 
                'complexity', 'category_id', 'default_spec_template',
                'quantity_config', 'variant_rules', 'created_at'
            ])
            ->orderBy('category_id')
            ->orderBy('display_name')
            ->get();

        // 템플릿 유무와 기본 정보 표시
        $elements->each(function ($element) {
            $element->has_template = !empty($element->default_spec_template);
            $element->spec_field_count = $element->has_template 
                ? count($element->default_spec_template) 
                : 0;
            
            // quantity_config 기본값 설정
            if (!$element->quantity_config) {
                $element->quantity_config = config('element.defaults.quantity_config');
            }
        });

        return $elements;
    }

    /**
     * 특정 ElementDefinition의 동적 스펙 템플릿 상세 조회
     */
    public function getElementTemplate(string $elementId): ElementDefinition
    {
        $element = ElementDefinition::with('category:id,name,color')
            ->findOrFail($elementId);

        // 기본값 설정
        if (!$element->default_spec_template) {
            $element->default_spec_template = [];
        }
        
        if (!$element->quantity_config) {
            $element->quantity_config = config('element.defaults.quantity_config');
        }
        
        if (!$element->variant_rules) {
            $element->variant_rules = config('element.defaults.variant_rules');
        }

        return $element;
    }

    /**
     * ElementDefinition의 동적 스펙 템플릿 업데이트
     */
    public function updateElementTemplate(ElementDefinition $element, array $data): ElementDefinition
    {
        // 스펙 필드들 검증
        $this->validateSpecFields($data['default_spec_template']);
        
        // quantity_config 검증
        $this->validateQuantityConfig($data['quantity_config']);

        $element->update([
            'default_spec_template' => $data['default_spec_template'],
            'quantity_config' => $data['quantity_config'],
            'variant_rules' => $data['variant_rules'] ?? config('element.defaults.variant_rules')
        ]);

        // 업데이트된 정보와 함께 다시 로드
        return $element->load('category:id,name,color');
    }

    /**
     * 동적 스펙 템플릿 초기화 (기본 템플릿 생성)
     */
    public function resetElementTemplate(ElementDefinition $element): ElementDefinition
    {
        $defaultTemplates = config('element.default_templates');
        
        $template = $defaultTemplates[$element->element_type] ?? $defaultTemplates['default'];

        $element->update([
            'default_spec_template' => $template['spec_template'],
            'quantity_config' => $template['quantity_config'],
            'variant_rules' => config('element.defaults.variant_rules')
        ]);

        return $element->fresh(['category']);
    }

    /**
     * 스펙 필드들 유효성 검사
     */
    private function validateSpecFields(array $specFields): void
    {
        foreach ($specFields as $index => $field) {
            // select 타입의 경우 options 필수
            if ($field['type'] === 'select' && empty($field['options'])) {
                throw ValidationException::withMessages([
                    "default_spec_template.{$index}.options" => 'select 타입은 options가 필수입니다.'
                ]);
            }
            
            // number 타입의 경우 validation 검증
            if ($field['type'] === 'number' && isset($field['validation'])) {
                if (isset($field['validation']['min'], $field['validation']['max']) && 
                    $field['validation']['min'] > $field['validation']['max']) {
                    throw ValidationException::withMessages([
                        "default_spec_template.{$index}.validation" => '최소값이 최대값보다 클 수 없습니다.'
                    ]);
                }
            }
        }
    }

    /**
     * quantity_config 유효성 검사
     */
    private function validateQuantityConfig(array $quantityConfig): void
    {
        if ($quantityConfig['min'] > $quantityConfig['max']) {
            throw ValidationException::withMessages([
                'quantity_config.min' => '최소 수량이 최대 수량보다 클 수 없습니다.'
            ]);
        }
        
        if ($quantityConfig['typical'] < $quantityConfig['min'] || 
            $quantityConfig['typical'] > $quantityConfig['max']) {
            throw ValidationException::withMessages([
                'quantity_config.typical' => '권장 수량은 최소-최대 범위 내에 있어야 합니다.'
            ]);
        }
    }

    /**
     * 템플릿 통계 조회
     */
    public function getTemplateStats(): array
    {
        $totalElements = ElementDefinition::count();
        $elementsWithTemplate = ElementDefinition::whereNotNull('default_spec_template')
            ->where('default_spec_template', '!=', '[]')
            ->count();

        return [
            'total_elements' => $totalElements,
            'elements_with_template' => $elementsWithTemplate,
            'elements_without_template' => $totalElements - $elementsWithTemplate,
            'completion_percentage' => $totalElements > 0 ? round(($elementsWithTemplate / $totalElements) * 100, 1) : 0,
        ];
    }
}