import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
// import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { RfpFormData } from '@/lib/types';
import { elementDefinitionApi, ElementWithSpecTemplate } from '@/lib/api';

interface Step3ElementDetailsProps {
  formData: RfpFormData;
  onElementUpdate: (elementIndex: number, field: string, value: any) => void;
}

interface ElementSpecData {
  [elementType: string]: ElementWithSpecTemplate;
}

export default function Step3ElementDetails({ formData, onElementUpdate }: Step3ElementDetailsProps) {
  const [elementSpecs, setElementSpecs] = useState<ElementSpecData>({});
  const [loadingElements, setLoadingElements] = useState<Set<string>>(new Set());
  const [errorElements, setErrorElements] = useState<Set<string>>(new Set());

  useEffect(() => {
    const fetchElementSpecs = async () => {
      // 새로운 요소들만 로딩
      const newElements = formData.elements.filter(
        element => !elementSpecs[element.element_type] && !loadingElements.has(element.element_type)
      );

      if (newElements.length === 0) return;

      const newLoadingElements = new Set(loadingElements);
      newElements.forEach(element => newLoadingElements.add(element.element_type));
      setLoadingElements(newLoadingElements);

      // 각 요소에 대해 API 호출
      const promises = newElements.map(async (element) => {
        try {
          // ElementDefinition을 먼저 찾아서 ID를 가져와야 함
          const categories = await elementDefinitionApi.getGroupedByCategory();
          let elementDefinition = null;
          
          for (const category of categories) {
            const found = category.elements.find(el => el.element_type === element.element_type);
            if (found) {
              elementDefinition = found;
              break;
            }
          }
          
          if (!elementDefinition) {
            throw new Error(`Element definition not found for ${element.element_type}`);
          }
          
          const specData = await elementDefinitionApi.getWithSpecTemplate(elementDefinition.id);
          return { elementType: element.element_type, specData };
        } catch (error) {
          console.error(`Failed to fetch spec for ${element.element_type}:`, error);
          return { elementType: element.element_type, error: true };
        }
      });

      const results = await Promise.all(promises);
      
      const newElementSpecs = { ...elementSpecs };
      const newErrorElements = new Set(errorElements);
      const updatedLoadingElements = new Set(loadingElements);
      
      results.forEach(result => {
        updatedLoadingElements.delete(result.elementType);
        if ('error' in result) {
          newErrorElements.add(result.elementType);
        } else {
          newElementSpecs[result.elementType] = result.specData;
        }
      });
      
      setElementSpecs(newElementSpecs);
      setErrorElements(newErrorElements);
      setLoadingElements(updatedLoadingElements);
    };

    fetchElementSpecs();
  }, [formData.elements, elementSpecs, loadingElements, errorElements]);

  const handleDetailsChange = (elementIndex: number, detailKey: string, value: string) => {
    const currentElement = formData.elements[elementIndex];
    const updatedDetails = {
      ...currentElement.details,
      [detailKey]: value
    };
    onElementUpdate(elementIndex, 'details', updatedDetails);
  };

  const handleSpecFieldChange = (elementIndex: number, fieldId: string, value: string) => {
    const currentElement = formData.elements[elementIndex];
    const updatedSpecs = {
      ...currentElement.details.specifications,
      [fieldId]: value
    };
    const updatedDetails = {
      ...currentElement.details,
      specifications: updatedSpecs
    };
    onElementUpdate(elementIndex, 'details', updatedDetails);
  };

  const handleQuantityChange = (elementIndex: number, value: number) => {
    const currentElement = formData.elements[elementIndex];
    const updatedDetails = {
      ...currentElement.details,
      quantity: value
    };
    onElementUpdate(elementIndex, 'details', updatedDetails);
  };

  const getElementDisplayName = (elementType: string) => {
    const spec = elementSpecs[elementType];
    return spec?.element.display_name || elementType;
  };

  const renderSpecField = (field: any, elementIndex: number, currentValue: string) => {
    const fieldId = field.id;
    
    switch (field.type) {
      case 'select':
        return (
          <select
            value={currentValue}
            onChange={(e) => handleSpecFieldChange(elementIndex, fieldId, e.target.value)}
            className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="">선택해주세요</option>
            {field.options?.map((option: string) => (
              <option key={option} value={option}>{option}</option>
            ))}
          </select>
        );
      case 'number':
        return (
          <Input
            type="number"
            value={currentValue}
            onChange={(e) => handleSpecFieldChange(elementIndex, fieldId, e.target.value)}
            placeholder={field.unit ? `단위: ${field.unit}` : ''}
          />
        );
      case 'textarea':
        return (
          <Textarea
            value={currentValue}
            onChange={(e) => handleSpecFieldChange(elementIndex, fieldId, e.target.value)}
            placeholder={field.placeholder || ''}
            rows={3}
          />
        );
      default:
        return (
          <Input
            type="text"
            value={currentValue}
            onChange={(e) => handleSpecFieldChange(elementIndex, fieldId, e.target.value)}
            placeholder={field.unit ? `단위: ${field.unit}` : ''}
          />
        );
    }
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow">
      <h2 className="text-xl font-semibold mb-6">3단계: 요소 상세 사양</h2>
      <p className="text-gray-600 mb-6">선택한 요소들의 상세 사양과 특별 요구사항을 입력해주세요.</p>
      
      <div className="space-y-6">
        {formData.elements.map((element, index) => {
          const isLoading = loadingElements.has(element.element_type);
          const hasError = errorElements.has(element.element_type);
          const spec = elementSpecs[element.element_type];
          
          return (
            <Card key={index}>
              <CardHeader>
                <CardTitle className="text-lg">{getElementDisplayName(element.element_type)}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {isLoading && (
                  <div className="text-center py-4">
                    <div className="text-gray-500">사양 정보를 불러오는 중...</div>
                  </div>
                )}
                
                {hasError && (
                  <div className="text-center py-4">
                    <div className="text-red-500">사양 정보를 불러오는데 실패했습니다.</div>
                  </div>
                )}
                
                {spec && (
                  <>
                    {/* 수량 설정 */}
                    <div>
                      <Label htmlFor={`quantity-${index}`}>
                        수량 ({spec.quantity_config.unit})
                      </Label>
                      <div className="flex items-center space-x-2">
                        <Input
                          id={`quantity-${index}`}
                          type="number"
                          value={element.details.quantity || spec.quantity_config.typical}
                          onChange={(e) => handleQuantityChange(index, parseInt(e.target.value))}
                          min={spec.quantity_config.range.min}
                          max={spec.quantity_config.range.max}
                          className="w-32"
                        />
                        <span className="text-sm text-gray-500">
                          권장: {spec.quantity_config.typical}{spec.quantity_config.unit}
                        </span>
                      </div>
                    </div>

                    {/* 동적 스펙 필드들 */}
                    {spec.spec_fields.length > 0 && (
                      <div>
                        <Label className="text-sm font-medium text-gray-700 mb-3 block">상세 사양</Label>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          {spec.spec_fields.map((field) => {
                            const currentValue = element.details.specifications?.[field.id] || '';
                            return (
                              <div key={field.id}>
                                <Label htmlFor={`${field.id}-${index}`} className="flex items-center gap-1">
                                  {field.name}
                                  {field.required && <span className="text-red-500">*</span>}
                                  {field.unit && <span className="text-sm text-gray-500">({field.unit})</span>}
                                </Label>
                                {renderSpecField(field, index, currentValue)}
                              </div>
                            );
                          })}
                        </div>
                      </div>
                    )}

                    {/* 추가 요구사항 */}
                    <div>
                      <Label htmlFor={`requirements-${index}`}>추가 요구사항</Label>
                      <Textarea
                        id={`requirements-${index}`}
                        value={element.details.additional_requirements || ''}
                        onChange={(e) => handleDetailsChange(index, 'additional_requirements', e.target.value)}
                        placeholder="특별한 요구사항이나 주의사항을 입력해주세요..."
                        rows={3}
                      />
                    </div>
                  </>
                )}
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
}