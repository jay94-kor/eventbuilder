import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Settings, ChevronDown, ChevronUp } from 'lucide-react';
import { ElementDefinition, SpecField, SpecVariant } from '@/lib/types';
import VariantEditor from './VariantEditor';

interface VariantManagerProps {
  elementDefinition: ElementDefinition;
  specFields: SpecField[];
  variants: SpecVariant[];
  onVariantsChange: (variants: SpecVariant[]) => void;
}

export default function VariantManager({
  elementDefinition,
  specFields,
  variants,
  onVariantsChange
}: VariantManagerProps) {
  const [expandedVariants, setExpandedVariants] = useState<Set<string>>(new Set());

  const variantRules = elementDefinition.variant_rules;
  const maxVariants = variantRules?.max_variants || 5;
  const allowedFields = variantRules?.allowed_fields || [];
  const requireName = variantRules?.require_name || false;

  // 변형 가능한 필드만 필터링
  const variantableFields = specFields.filter(field => 
    allowedFields.length === 0 || allowedFields.includes(field.name)
  );

  const addVariant = () => {
    if (variants.length >= maxVariants) return;

    const newVariant: SpecVariant = {
      id: crypto.randomUUID(),
      name: requireName ? `변형 ${variants.length + 1}` : '',
      quantity: 1,
      modified_fields: [],
      spec_values: {},
      notes: ''
    };

    onVariantsChange([...variants, newVariant]);
    setExpandedVariants(prev => new Set([...prev, newVariant.id]));
  };

  const updateVariant = (variantId: string, updatedVariant: SpecVariant) => {
    onVariantsChange(variants.map(variant => 
      variant.id === variantId ? updatedVariant : variant
    ));
  };

  const removeVariant = (variantId: string) => {
    onVariantsChange(variants.filter(variant => variant.id !== variantId));
    setExpandedVariants(prev => {
      const newSet = new Set(prev);
      newSet.delete(variantId);
      return newSet;
    });
  };

  const toggleVariantExpansion = (variantId: string) => {
    setExpandedVariants(prev => {
      const newSet = new Set(prev);
      if (newSet.has(variantId)) {
        newSet.delete(variantId);
      } else {
        newSet.add(variantId);
      }
      return newSet;
    });
  };

  const totalVariantQuantity = variants.reduce((sum, variant) => sum + variant.quantity, 0);

  return (
    <Card className="border-blue-200 bg-blue-50">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Settings className="w-4 h-4 text-blue-600" />
            <CardTitle className="text-base font-medium text-blue-900">
              스펙 변형 관리
            </CardTitle>
            <Badge variant="outline" className="text-xs bg-blue-100 text-blue-700">
              {variants.length}/{maxVariants}개
            </Badge>
          </div>
          <Button
            onClick={addVariant}
            disabled={variants.length >= maxVariants}
            size="sm"
            className="bg-blue-600 hover:bg-blue-700"
          >
            <Plus className="w-4 h-4 mr-1" />
            변형 추가
          </Button>
        </div>
        
        {variants.length > 0 && (
          <div className="flex items-center gap-4 pt-2 text-sm text-blue-700">
            <span>총 변형 수량: {totalVariantQuantity}{elementDefinition.quantity_config?.unit || '개'}</span>
            <span>변형 가능 필드: {variantableFields.length}개</span>
          </div>
        )}
      </CardHeader>

      {variants.length > 0 && (
        <CardContent className="pt-0 space-y-3">
          {variants.map((variant, index) => (
            <Card key={variant.id} className="bg-white border-gray-200">
              <CardHeader 
                className="pb-2 cursor-pointer hover:bg-gray-50 transition-colors"
                onClick={() => toggleVariantExpansion(variant.id)}
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <div className="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-medium">
                      {index + 1}
                    </div>
                    <div>
                      <h4 className="font-medium text-gray-900">
                        {variant.name || `변형 ${index + 1}`}
                      </h4>
                      <div className="flex items-center gap-2 mt-1">
                        <Badge variant="outline" className="text-xs">
                          {variant.quantity}{elementDefinition.quantity_config?.unit || '개'}
                        </Badge>
                        {variant.modified_fields.length > 0 && (
                          <Badge variant="outline" className="text-xs bg-green-50 text-green-700">
                            {variant.modified_fields.length}개 필드 수정
                          </Badge>
                        )}
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    {expandedVariants.has(variant.id) ? 
                      <ChevronUp className="w-4 h-4" /> : 
                      <ChevronDown className="w-4 h-4" />
                    }
                  </div>
                </div>
              </CardHeader>

              {expandedVariants.has(variant.id) && (
                <CardContent className="pt-0">
                  <VariantEditor
                    variant={variant}
                    specFields={variantableFields}
                    elementDefinition={elementDefinition}
                    onUpdate={(updatedVariant) => updateVariant(variant.id, updatedVariant)}
                    onRemove={() => removeVariant(variant.id)}
                  />
                </CardContent>
              )}
            </Card>
          ))}
        </CardContent>
      )}

      {variants.length === 0 && (
        <CardContent className="pt-0">
          <div className="text-center py-6 text-gray-500">
            <Settings className="w-8 h-8 mx-auto mb-2 text-gray-400" />
            <p className="text-sm">아직 추가된 변형이 없습니다.</p>
            <p className="text-xs text-gray-400 mt-1">
              스펙이 다른 제품이 필요한 경우 변형을 추가하세요.
            </p>
          </div>
        </CardContent>
      )}
    </Card>
  );
} 