import React, { useState, useEffect } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  Settings, Package, Plus, Minus, ChevronDown, ChevronUp, 
  Building, Music, Lightbulb, Camera, Users, Palette, FileText 
} from 'lucide-react';
import { RfpElementFormData, ElementDefinition, SpecField, SpecVariant } from '@/lib/types';
import QuantityController from './QuantityController';
import SpecFieldEditor from './SpecFieldEditor';
import VariantManager from './VariantManager';

interface DynamicSpecManagerProps {
  elementDefinition: ElementDefinition;
  elementFormData: RfpElementFormData;
  onElementDataChange: (elementId: string, data: Partial<RfpElementFormData>) => void;
  onRemoveElement: (elementId: string) => void;
  isExpanded: boolean;
  onToggleExpansion: (elementId: string) => void;
}

export default function DynamicSpecManager({
  elementDefinition,
  elementFormData,
  onElementDataChange,
  onRemoveElement,
  isExpanded,
  onToggleExpansion
}: DynamicSpecManagerProps) {
  // 동적 스펙 템플릿이 있는지 확인
  const hasSpecTemplate = elementDefinition.default_spec_template && 
                         elementDefinition.default_spec_template.length > 0;

  // 스펙 필드 초기화
  useEffect(() => {
    if (hasSpecTemplate && (!elementFormData.spec_fields || elementFormData.spec_fields.length === 0)) {
      const initialSpecFields = elementDefinition.createSpecFields?.() || 
                               elementDefinition.default_spec_template?.map(template => ({
                                 id: crypto.randomUUID(),
                                 name: template.name,
                                 unit: template.unit,
                                 value: template.default_value || '',
                                 type: template.type,
                                 options: template.options,
                                 required: template.required || false,
                                 validation: template.validation
                               })) || [];

      onElementDataChange(elementFormData.element_id, {
        spec_fields: initialSpecFields,
        total_quantity: elementDefinition.quantity_config?.typical || 1,
        base_quantity: elementDefinition.quantity_config?.typical || 1,
        use_variants: false,
        spec_variants: []
      });
    }
  }, [hasSpecTemplate, elementDefinition, elementFormData, onElementDataChange]);

  const getIconForElement = (elementType: string) => {
    const iconMap: Record<string, React.ReactElement> = {
      '무대 설치': <Building className="w-5 h-5" />,
      '음향 시스템': <Music className="w-5 h-5" />,
      '조명 시스템': <Lightbulb className="w-5 h-5" />,
      '영상 시스템': <Camera className="w-5 h-5" />,
      '인력 운영': <Users className="w-5 h-5" />,
      '운영 관리': <Settings className="w-5 h-5" />,
      '특수 효과': <Palette className="w-5 h-5" />,
    };
    return iconMap[elementType] || <Package className="w-5 h-5" />;
  };

  const getComplexityColor = (level?: string) => {
    switch (level) {
      case 'basic': return 'bg-green-100 text-green-800';
      case 'intermediate': return 'bg-yellow-100 text-yellow-800';
      case 'advanced': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getComplexityText = (level?: string) => {
    switch (level) {
      case 'basic': return '기본';
      case 'intermediate': return '중급';
      case 'advanced': return '고급';
      default: return '-';
    }
  };

  const handleSpecFieldChange = (updatedField: SpecField) => {
    const updatedSpecFields = elementFormData.spec_fields?.map(field =>
      field.id === updatedField.id ? updatedField : field
    ) || [];

    onElementDataChange(elementFormData.element_id, {
      spec_fields: updatedSpecFields
    });
  };

  const handleQuantityChange = (quantityData: {
    total_quantity: number;
    base_quantity: number;
    use_variants: boolean;
  }) => {
    onElementDataChange(elementFormData.element_id, quantityData);
  };

  const handleVariantsChange = (variants: SpecVariant[]) => {
    onElementDataChange(elementFormData.element_id, {
      spec_variants: variants
    });
  };

  // 스펙 필드가 없는 경우 기존 input_schema 방식 사용
  if (!hasSpecTemplate) {
    return (
      <Card className="relative group">
        <CardHeader 
          className="cursor-pointer hover:bg-gray-50 transition-colors"
          onClick={() => onToggleExpansion(elementDefinition.id)}
        >
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              {getIconForElement(elementDefinition.element_type)}
              <div>
                <CardTitle className="text-lg font-semibold text-gray-900">
                  {elementDefinition.display_name}
                </CardTitle>
                <div className="flex items-center gap-2 mt-1">
                  <Badge variant="outline" className={getComplexityColor(elementDefinition.complexity_level)}>
                    {getComplexityText(elementDefinition.complexity_level)}
                  </Badge>
                  <Badge variant="outline" className="text-xs">
                    기존 스키마 방식
                  </Badge>
                </div>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <Button
                variant="ghost"
                size="sm"
                onClick={(e) => {
                  e.stopPropagation();
                  onRemoveElement(elementFormData.element_id);
                }}
                className="opacity-0 group-hover:opacity-100 transition-opacity text-red-600 hover:text-red-700"
              >
                <Minus className="w-4 h-4" />
              </Button>
              {isExpanded ? <ChevronUp className="w-5 h-5" /> : <ChevronDown className="w-5 h-5" />}
            </div>
          </div>
        </CardHeader>

        {isExpanded && (
          <CardContent className="pt-0">
            <div className="p-4 bg-amber-50 rounded-lg border border-amber-200">
              <div className="flex items-center gap-2 text-amber-800">
                <FileText className="w-4 h-4" />
                <span className="text-sm font-medium">기존 스키마 방식</span>
              </div>
              <p className="text-xs text-amber-700 mt-1">
                이 요소는 아직 동적 스펙 템플릿이 설정되지 않았습니다. 
                관리자에게 문의하여 템플릿을 추가해주세요.
              </p>
            </div>
          </CardContent>
        )}
      </Card>
    );
  }

  return (
    <Card className="relative group">
      <CardHeader 
        className="cursor-pointer hover:bg-gray-50 transition-colors"
        onClick={() => onToggleExpansion(elementDefinition.id)}
      >
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            {getIconForElement(elementDefinition.element_type)}
            <div>
              <CardTitle className="text-lg font-semibold text-gray-900">
                {elementDefinition.display_name}
              </CardTitle>
              <div className="flex items-center gap-2 mt-1">
                <Badge variant="outline" className={getComplexityColor(elementDefinition.complexity_level)}>
                  {getComplexityText(elementDefinition.complexity_level)}
                </Badge>
                {elementFormData.total_quantity && (
                  <Badge variant="outline" className="text-xs bg-blue-50 text-blue-700">
                    총 {elementFormData.total_quantity}{elementDefinition.quantity_config?.unit || '개'}
                  </Badge>
                )}
              </div>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Button
              variant="ghost"
              size="sm"
              onClick={(e) => {
                e.stopPropagation();
                onRemoveElement(elementFormData.element_id);
              }}
              className="opacity-0 group-hover:opacity-100 transition-opacity text-red-600 hover:text-red-700"
            >
              <Minus className="w-4 h-4" />
            </Button>
            {isExpanded ? <ChevronUp className="w-5 h-5" /> : <ChevronDown className="w-5 h-5" />}
          </div>
        </div>
      </CardHeader>

      {isExpanded && (
        <CardContent className="space-y-6">
          {/* 수량 관리 */}
          <QuantityController
            elementDefinition={elementDefinition}
            totalQuantity={elementFormData.total_quantity || 1}
            baseQuantity={elementFormData.base_quantity || 1}
            useVariants={elementFormData.use_variants || false}
            variants={elementFormData.spec_variants || []}
            onChange={handleQuantityChange}
          />

          {/* 기본 스펙 필드들 */}
          {elementFormData.spec_fields && elementFormData.spec_fields.length > 0 && (
            <div className="space-y-4">
              <div className="flex items-center gap-2">
                <Settings className="w-4 h-4 text-slate-600" />
                <h4 className="font-medium text-slate-900">기본 스펙</h4>
              </div>
              <div className="space-y-3">
                {elementFormData.spec_fields.map(field => (
                  <SpecFieldEditor
                    key={field.id}
                    field={field}
                    onChange={handleSpecFieldChange}
                  />
                ))}
              </div>
            </div>
          )}

          {/* 스펙 변형 관리 */}
          {elementDefinition.quantity_config?.allow_variants && (
            <VariantManager
              elementDefinition={elementDefinition}
              specFields={elementFormData.spec_fields || []}
              variants={elementFormData.spec_variants || []}
              onVariantsChange={handleVariantsChange}
            />
          )}

          {/* 기타 정보 (예산, 특별 요구사항 등) */}
          <div className="pt-4 border-t border-gray-200">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-medium text-gray-700">예산 배정</label>
                <input
                  type="number"
                  value={elementFormData.allocated_budget || ''}
                  onChange={(e) => onElementDataChange(elementFormData.element_id, {
                    allocated_budget: parseFloat(e.target.value) || null
                  })}
                  placeholder="예산을 입력하세요"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div className="space-y-2">
                <label className="text-sm font-medium text-gray-700">특별 요구사항</label>
                <textarea
                  value={elementFormData.special_requirements || ''}
                  onChange={(e) => onElementDataChange(elementFormData.element_id, {
                    special_requirements: e.target.value
                  })}
                  placeholder="특별한 요구사항이 있다면 입력하세요"
                  rows={2}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                />
              </div>
            </div>
          </div>
        </CardContent>
      )}
    </Card>
  );
} 