import React from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertCircle } from 'lucide-react';
import { SpecField } from '@/lib/types';

interface SpecFieldEditorProps {
  field: SpecField;
  onChange: (field: SpecField) => void;
}

export default function SpecFieldEditor({ field, onChange }: SpecFieldEditorProps) {
  const validateField = (value: string | number | boolean): string | null => {
    if (field.required && (!value || value === '')) {
      return `${field.name}은(는) 필수 입력 항목입니다.`;
    }

    if (field.type === 'number' && field.validation) {
      const numValue = Number(value);
      if (field.validation.min !== undefined && numValue < field.validation.min) {
        return `${field.name}은(는) ${field.validation.min} 이상이어야 합니다.`;
      }
      if (field.validation.max !== undefined && numValue > field.validation.max) {
        return `${field.name}은(는) ${field.validation.max} 이하여야 합니다.`;
      }
    }

    return null;
  };

  const error = validateField(field.value);

  const renderFieldInput = () => {
    switch (field.type) {
      case 'number':
        return (
          <div className="flex items-center gap-2">
            <Input
              type="number"
              value={field.value as number || ''}
              onChange={(e) => onChange({
                ...field,
                value: parseFloat(e.target.value) || 0
              })}
              min={field.validation?.min}
              max={field.validation?.max}
              className={`flex-1 ${error ? 'border-red-300 focus:border-red-500' : ''}`}
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
            value={field.value as string || ''}
            onValueChange={(value) => onChange({ ...field, value })}
          >
            <SelectTrigger className={error ? 'border-red-300 focus:border-red-500' : ''}>
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
              checked={field.value as boolean || false}
              onCheckedChange={(checked) => onChange({ ...field, value: checked })}
              id={`field-${field.id}`}
            />
            <Label htmlFor={`field-${field.id}`} className="text-sm cursor-pointer">
              {field.name}
            </Label>
          </div>
        );

      default: // text
        return (
          <Input
            type="text"
            value={field.value as string || ''}
            onChange={(e) => onChange({ ...field, value: e.target.value })}
            className={error ? 'border-red-300 focus:border-red-500' : ''}
            placeholder={`${field.name}을 입력하세요`}
          />
        );
    }
  };

  return (
    <div className="grid grid-cols-12 gap-3 items-start p-3 border rounded-lg bg-white">
      {/* 필드명 */}
      <div className="col-span-3">
        <Label className="font-medium text-gray-900 text-sm">
          {field.name}
          {field.required && <span className="text-red-500 ml-1">*</span>}
        </Label>
        {field.validation?.min !== undefined && field.validation?.max !== undefined && (
          <p className="text-xs text-gray-500 mt-1">
            {field.validation.min} ~ {field.validation.max}
          </p>
        )}
      </div>

      {/* 입력 필드 */}
      <div className="col-span-6">
        {field.type === 'boolean' ? (
          renderFieldInput()
        ) : (
          <div className="space-y-1">
            {renderFieldInput()}
            {error && (
              <div className="flex items-center gap-1 text-red-600 text-xs">
                <AlertCircle className="w-3 h-3" />
                <span>{error}</span>
              </div>
            )}
          </div>
        )}
      </div>

      {/* 단위 (boolean이 아닌 경우만) */}
      <div className="col-span-2">
        {field.type !== 'boolean' && (
          <div className="text-sm text-gray-500 text-center">
            {field.unit || '-'}
          </div>
        )}
      </div>

      {/* 추가 정보 */}
      <div className="col-span-1">
        {field.required && (
          <div className="w-2 h-2 bg-red-400 rounded-full" title="필수 항목"></div>
        )}
      </div>
    </div>
  );
} 