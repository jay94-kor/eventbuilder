import React, { useState, useEffect } from 'react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AlertCircle, FileText, CheckCircle } from 'lucide-react';
import { RfpFormData, RfpElementFormData, ElementDefinition } from '@/lib/types';
import DynamicSpecManager from './DynamicSpecManager';

interface Step3FormProps {
  formData: RfpFormData;
  selectedElementDefinitions: ElementDefinition[];
  rfpElements: RfpElementFormData[];
  onRfpElementsChange: (elements: RfpElementFormData[]) => void;
  onRemoveElement: (elementId: string) => void;
}

export default function Step3Form({ 
  formData, 
  selectedElementDefinitions, 
  rfpElements, 
  onRfpElementsChange, 
  onRemoveElement 
}: Step3FormProps) {
  const [expandedElements, setExpandedElements] = useState<Set<string>>(new Set());
  const [validationErrors, setValidationErrors] = useState<Record<string, string[]>>({});

  // 컴포넌트 마운트 시 첫 번째 요소는 기본으로 펼쳐놓기
  useEffect(() => {
    if (selectedElementDefinitions.length > 0 && expandedElements.size === 0) {
      const newExpanded = new Set<string>();
      // 첫 번째 요소만 펼치기
      newExpanded.add(selectedElementDefinitions[0].id);
      setExpandedElements(newExpanded);
    }
  }, [selectedElementDefinitions, expandedElements.size]);

  // 선택된 요소에 대한 formData가 없으면 초기화
  useEffect(() => {
    if (selectedElementDefinitions.length > 0) {
      const existingElementIds = new Set(rfpElements.map(e => {
        // 인스턴스 ID에서 기본 ElementDefinition ID 추출
        if (!e.element_id || typeof e.element_id !== 'string') {
          return '';
        }
        return e.element_id.includes('-') && e.element_id.match(/^(.+)-\d+$/) 
          ? e.element_id.replace(/-\d+$/, '') 
          : e.element_id;
      }).filter(id => id !== ''));

      const newElements: RfpElementFormData[] = [];
      
      selectedElementDefinitions.forEach(elementDef => {
        if (!existingElementIds.has(elementDef.id)) {
          // 첫 번째 인스턴스는 기본 ID-1 형태로 생성
          newElements.push({
            element_id: `${elementDef.id}-1`,
            element_type: elementDef.element_type,
            
            // 🆕 동적 스펙 시스템 초기값
            total_quantity: elementDef.quantity_config?.typical || 1,
            base_quantity: elementDef.quantity_config?.typical || 1,
            use_variants: false,
            spec_fields: [],
            spec_variants: [],
            
            // 기존 필드들 (하위 호환성)
            details: {},
            special_requirements: '',
            allocated_budget: null,
            prepayment_ratio: null,
            prepayment_due_date: null,
            balance_ratio: null,
            balance_due_date: null,
          });
        }
      });

      if (newElements.length > 0) {
        onRfpElementsChange([...rfpElements, ...newElements]);
      }
    }
  }, [selectedElementDefinitions, rfpElements, onRfpElementsChange]);

  const toggleElementExpansion = (elementDefinitionId: string) => {
    const newExpanded = new Set(expandedElements);
    if (newExpanded.has(elementDefinitionId)) {
      newExpanded.delete(elementDefinitionId);
    } else {
      newExpanded.add(elementDefinitionId);
    }
    setExpandedElements(newExpanded);
  };

  const getElementDefinitionFromInstanceId = (instanceId: string): ElementDefinition | null => {
    if (!instanceId || typeof instanceId !== 'string') {
      return null;
    }
    
    const baseId = instanceId.includes('-') && instanceId.match(/^(.+)-\d+$/) 
      ? instanceId.replace(/-\d+$/, '') 
      : instanceId;
    
    return selectedElementDefinitions.find(def => def.id === baseId) || null;
  };

  const handleElementDataChange = (elementId: string, data: Partial<RfpElementFormData>) => {
    onRfpElementsChange(rfpElements.map(element => 
      element.element_id === elementId 
        ? { ...element, ...data }
        : element
    ));
    
    // 변경 시 해당 요소의 검증 오류 제거
    if (validationErrors[elementId]) {
      const newErrors = { ...validationErrors };
      delete newErrors[elementId];
      setValidationErrors(newErrors);
    }
  };

  const handleRemoveElement = (elementId: string) => {
    onRemoveElement(elementId);
    
    // 삭제 시 검증 오류도 제거
    if (validationErrors[elementId]) {
      const newErrors = { ...validationErrors };
      delete newErrors[elementId];
      setValidationErrors(newErrors);
    }
  };

  // 전체 유효성 검사
  const validateAllElements = (): boolean => {
    const errors: Record<string, string[]> = {};
    let isValid = true;

    rfpElements.forEach(element => {
      const elementErrors: string[] = [];
      const elementDef = getElementDefinitionFromInstanceId(element.element_id);
      
      if (!elementDef) {
        elementErrors.push('요소 정의를 찾을 수 없습니다.');
        isValid = false;
      } else {
        // 수량 검증
        if (!element.total_quantity || element.total_quantity < 1) {
          elementErrors.push('총 수량은 1 이상이어야 합니다.');
          isValid = false;
        }

        const quantityConfig = elementDef.quantity_config;
        if (quantityConfig) {
          if (element.total_quantity < quantityConfig.min) {
            elementErrors.push(`수량은 최소 ${quantityConfig.min}${quantityConfig.unit} 이상이어야 합니다.`);
            isValid = false;
          }
          if (element.total_quantity > quantityConfig.max) {
            elementErrors.push(`수량은 최대 ${quantityConfig.max}${quantityConfig.unit} 이하여야 합니다.`);
            isValid = false;
          }
        }

        // 스펙 필드 검증
        if (element.spec_fields) {
          element.spec_fields.forEach(field => {
            if (field.required && (!field.value || field.value === '')) {
              elementErrors.push(`${field.name}은(는) 필수 입력 항목입니다.`);
              isValid = false;
            }
          });
        }

        // 변형 수량 검증
        if (element.use_variants && element.spec_variants) {
          const variantTotal = element.spec_variants.reduce((sum, variant) => sum + variant.quantity, 0);
          const calculatedTotal = (element.base_quantity || 0) + variantTotal;
          
          if (calculatedTotal !== element.total_quantity) {
            elementErrors.push(`수량이 일치하지 않습니다. (기본: ${element.base_quantity}, 변형: ${variantTotal}, 총: ${element.total_quantity})`);
            isValid = false;
          }
        }
      }

      if (elementErrors.length > 0) {
        errors[element.element_id] = elementErrors;
      }
    });

    setValidationErrors(errors);
    return isValid;
  };

  // 요소별로 그룹화 (같은 ElementDefinition을 기반으로 한 인스턴스들)
  const groupedElements = selectedElementDefinitions.map(elementDef => {
    const instances = rfpElements.filter(element => {
      if (!element.element_id || typeof element.element_id !== 'string') {
        return false;
      }
      const baseId = element.element_id.includes('-') && element.element_id.match(/^(.+)-\d+$/) 
        ? element.element_id.replace(/-\d+$/, '') 
        : element.element_id;
      return baseId === elementDef.id;
    });

    return {
      elementDefinition: elementDef,
      instances
    };
  });

  const totalErrors = Object.values(validationErrors).flat().length;
  const completedElements = rfpElements.filter(element => {
    return element.spec_fields && element.spec_fields.length > 0 &&
           element.total_quantity && element.total_quantity > 0;
  }).length;

  return (
    <div className="space-y-6">
      {/* 진행 상황 요약 */}
      <Card className="bg-slate-50 border-slate-200">
        <CardContent className="p-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="flex items-center gap-2">
                <FileText className="w-5 h-5 text-slate-600" />
                <span className="font-medium text-slate-900">스펙 설정 진행도</span>
              </div>
              <div className="flex items-center gap-4 text-sm">
                <div className="flex items-center gap-1">
                  <CheckCircle className="w-4 h-4 text-green-600" />
                  <span>{completedElements}/{rfpElements.length} 완료</span>
                </div>
                {totalErrors > 0 && (
                  <div className="flex items-center gap-1">
                    <AlertCircle className="w-4 h-4 text-red-600" />
                    <span className="text-red-600">{totalErrors}개 오류</span>
                  </div>
                )}
              </div>
            </div>
            <Button 
              onClick={validateAllElements}
              variant="outline"
              size="sm"
              className="bg-white"
            >
              전체 검증
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* 선택된 요소들이 없는 경우 */}
      {selectedElementDefinitions.length === 0 && (
        <Card className="border-dashed border-2 border-gray-300">
          <CardContent className="p-8 text-center">
            <FileText className="w-12 h-12 mx-auto mb-4 text-gray-400" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">요소를 먼저 선택해주세요</h3>
            <p className="text-gray-600">
              Step 2에서 필요한 요소들을 선택한 후 여기서 상세 스펙을 설정할 수 있습니다.
            </p>
          </CardContent>
        </Card>
      )}

      {/* 동적 스펙 관리자들 */}
      {groupedElements.map(({ elementDefinition, instances }) => 
        instances.map(elementFormData => {
          const hasErrors = validationErrors[elementFormData.element_id]?.length > 0;
          
          return (
            <div key={elementFormData.element_id} className="relative">
              {/* 검증 오류 표시 */}
              {hasErrors && (
                <div className="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                  <div className="flex items-center gap-2 text-red-800 mb-2">
                    <AlertCircle className="w-4 h-4" />
                    <span className="font-medium">설정 오류</span>
                  </div>
                  <ul className="text-sm text-red-700 space-y-1">
                    {validationErrors[elementFormData.element_id].map((error, index) => (
                      <li key={index}>• {error}</li>
                    ))}
                  </ul>
                </div>
              )}

              <DynamicSpecManager
                elementDefinition={elementDefinition}
                elementFormData={elementFormData}
                onElementDataChange={handleElementDataChange}
                onRemoveElement={handleRemoveElement}
                isExpanded={expandedElements.has(elementDefinition.id)}
                onToggleExpansion={toggleElementExpansion}
              />
            </div>
          )
        })
      )}

      {/* 요약 정보 */}
      {rfpElements.length > 0 && (
        <Card className="bg-blue-50 border-blue-200">
          <CardHeader>
            <CardTitle className="text-lg text-blue-900">설정 요약</CardTitle>
            <CardDescription className="text-blue-700">
              현재 설정된 모든 요소들의 요약 정보입니다.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {groupedElements.map(({ elementDefinition, instances }) => {
                const totalQuantity = instances.reduce((sum, instance) => sum + (instance.total_quantity || 0), 0);
                const totalBudget = instances.reduce((sum, instance) => sum + (instance.allocated_budget || 0), 0);
                
                return (
                  <div key={elementDefinition.id} className="bg-white p-4 rounded-lg border border-blue-200">
                    <h4 className="font-medium text-gray-900 mb-2">{elementDefinition.display_name}</h4>
                    <div className="space-y-1 text-sm text-gray-600">
                      <div className="flex justify-between">
                        <span>총 수량:</span>
                        <span>{totalQuantity}{elementDefinition.quantity_config?.unit || '개'}</span>
                      </div>
                      <div className="flex justify-between">
                        <span>인스턴스:</span>
                        <span>{instances.length}개</span>
                      </div>
                      {totalBudget > 0 && (
                        <div className="flex justify-between">
                          <span>예산:</span>
                          <span>{totalBudget.toLocaleString()}원</span>
                        </div>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}