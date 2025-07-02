import React from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Trash2, AlertCircle } from 'lucide-react';
import { ElementDefinition, SpecField, SpecVariant } from '@/lib/types';

interface VariantEditorProps {
  variant: SpecVariant;
  specFields: SpecField[];
  elementDefinition: ElementDefinition;
  onUpdate: (variant: SpecVariant) => void;
  onRemove: () => void;
}

export default function VariantEditor({
  variant,
  specFields,
  elementDefinition,
  onUpdate,
  onRemove
}: VariantEditorProps) {
  const quantityConfig = elementDefinition.quantity_config;
  const unit = quantityConfig?.unit || '개';
  const requireName = elementDefinition.variant_rules?.require_name || false;

  const handleNameChange = (name: string) => {
    onUpdate({ ...variant, name });
  };

  const handleQuantityChange = (quantity: number) => {
    onUpdate({ ...variant, quantity: Math.max(1, quantity) });
  };

  const handleNotesChange = (notes: string) => {
    onUpdate({ ...variant, notes });
  };

  const handleFieldModificationToggle = (fieldName: string, isModified: boolean) => {
    if (isModified) {
      // 필드를 수정 목록에 추가
      const field = specFields.find(f => f.name === fieldName);
      if (field) {
        onUpdate({
          ...variant,
          modified_fields: [...variant.modified_fields, fieldName],
          spec_values: {
            ...variant.spec_values,
            [fieldName]: field.value // 기본값으로 초기화
          }
        });
      }
    } else {
      // 필드를 수정 목록에서 제거
      onUpdate({
        ...variant,
        modified_fields: variant.modified_fields.filter(f => f !== fieldName),
        spec_values: Object.fromEntries(
          Object.entries(variant.spec_values).filter(([key]) => key !== fieldName)
        )
      });
    }
  };

  const handleSpecValueChange = (fieldName: string, value: any) => {
    onUpdate({
      ...variant,
      spec_values: {
        ...variant.spec_values,
        [fieldName]: value
      }
    });
  };

  const renderSpecFieldInput = (field: SpecField, value: any) => {
    switch (field.type) {
      case 'number':
        return (
          <div className="flex items-center gap-2">
            <Input
              type="number"
              value={value || ''}
              onChange={(e) => handleSpecValueChange(field.name, parseFloat(e.target.value) || 0)}
              min={field.validation?.min}
              max={field.validation?.max}
              className="flex-1"
              placeholder={`${field.name}을 입력하세요`}
            />
            {field.unit && (
              <span className="text-sm text-gray-500 min-w-[30px]">{field.unit}</span>
            )}
          </div>
        );

      case 'select':
        return (
          <Select
            value={value || ''}
            onValueChange={(newValue) => handleSpecValueChange(field.name, newValue)}
          >
            <SelectTrigger>
              <SelectValue placeholder={`${field.name}을 선택하세요`} />
            </SelectTrigger>
            <SelectContent>
              {field.options?.map(option => (
                <SelectItem key={option} value={option}>
                  {option}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        );

      case 'boolean':
        return (
          <div className="flex items-center space-x-2">
            <Checkbox
              checked={value || false}
              onCheckedChange={(checked) => handleSpecValueChange(field.name, checked)}
              id={`variant-${variant.id}-${field.name}`}
            />
            <Label htmlFor={`variant-${variant.id}-${field.name}`} className="text-sm cursor-pointer">
              {field.name}
            </Label>
          </div>
        );

      default: // text
        return (
          <Input
            type="text"
            value={value || ''}
            onChange={(e) => handleSpecValueChange(field.name, e.target.value)}
            placeholder={`${field.name}을 입력하세요`}
          />
        );
    }
  };

  return (
    <div className="space-y-4">
      {/* 기본 정보 */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="space-y-2">
          <Label htmlFor={`variant-name-${variant.id}`} className="text-sm font-medium">
            변형명 {requireName && <span className="text-red-500">*</span>}
          </Label>
          <Input
            id={`variant-name-${variant.id}`}
            type="text"
            value={variant.name}
            onChange={(e) => handleNameChange(e.target.value)}
            placeholder="예: 소형 버전, 고출력 버전"
            required={requireName}
          />
        </div>
        
        <div className="space-y-2">
          <Label htmlFor={`variant-quantity-${variant.id}`} className="text-sm font-medium">
            수량
          </Label>
          <div className="relative">
            <Input
              id={`variant-quantity-${variant.id}`}
              type="number"
              min={1}
              value={variant.quantity}
              onChange={(e) => handleQuantityChange(parseInt(e.target.value) || 1)}
              className="pr-12"
            />
            <span className="absolute right-3 top-1/2 transform -translate-y-1/2 text-sm text-gray-500">
              {unit}
            </span>
          </div>
        </div>
      </div>

      {/* 수정할 스펙 필드 선택 */}
      <div className="space-y-3">
        <Label className="text-sm font-medium">수정할 스펙 필드</Label>
        <div className="space-y-3">
          {specFields.map(field => {
            const isModified = variant.modified_fields.includes(field.name);
            const value = variant.spec_values[field.name];
            
            return (
              <div key={field.name} className="border rounded-lg p-3 bg-gray-50">
                <div className="flex items-center space-x-3 mb-2">
                  <Checkbox
                    checked={isModified}
                    onCheckedChange={(checked) => handleFieldModificationToggle(field.name, !!checked)}
                    id={`modify-${variant.id}-${field.name}`}
                  />
                  <Label 
                    htmlFor={`modify-${variant.id}-${field.name}`} 
                    className="text-sm font-medium cursor-pointer flex-1"
                  >
                    {field.name}
                    {field.unit && <span className="text-gray-500 ml-1">({field.unit})</span>}
                  </Label>
                  {field.required && (
                    <span className="text-xs text-red-500">필수</span>
                  )}
                </div>
                
                {isModified && (
                  <div className="ml-6">
                    {renderSpecFieldInput(field, value)}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </div>

      {/* 특별 요구사항 */}
      <div className="space-y-2">
        <Label htmlFor={`variant-notes-${variant.id}`} className="text-sm font-medium">
          변형별 특별 요구사항
        </Label>
        <Textarea
          id={`variant-notes-${variant.id}`}
          value={variant.notes || ''}
          onChange={(e) => handleNotesChange(e.target.value)}
          placeholder="이 변형에 대한 특별한 요구사항이 있다면 입력하세요"
          rows={2}
          className="resize-none"
        />
      </div>

      {/* 변형 삭제 버튼 */}
      <div className="flex justify-end pt-2 border-t border-gray-200">
        <Button
          onClick={onRemove}
          variant="outline"
          size="sm"
          className="text-red-600 border-red-300 hover:bg-red-50 hover:border-red-400"
        >
          <Trash2 className="w-4 h-4 mr-1" />
          이 변형 삭제
        </Button>
      </div>

      {/* 검증 오류 표시 */}
      {requireName && !variant.name.trim() && (
        <div className="flex items-center gap-2 text-red-600 text-sm">
          <AlertCircle className="w-4 h-4" />
          <span>변형명을 입력해주세요.</span>
        </div>
      )}
    </div>
  );
} 