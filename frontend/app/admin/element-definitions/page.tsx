'use client';

import { useState, useEffect } from 'react';
import api from '../../../lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ElementDefinition } from '@/lib/types';
import { AxiosError } from 'axios';

<<<<<<< Updated upstream
export default function ElementDefinitionsPage() {
  const [elements, setElements] = useState<ElementDefinition[]>([]);
  const [currentElement, setCurrentElement] = useState<ElementDefinition | null>(null);
=======
interface TabType {
  id: string;
  name: string;
  icon: React.ElementType;
  description: string;
}

interface CategoryFormData {
  name: string;
  display_name: string;
  description: string;
  icon: string;
  color: string;
  group: string;
}

interface ElementFormData {
  category_id: string | null;
  element_type: string;
  display_name: string;
  description: string;
  input_schema: string;
  default_details_template: string;
  complexity_level: 'basic' | 'intermediate' | 'advanced';
  event_types: string[];
}

// 🆕 동적 스펙 템플릿 관련 인터페이스
interface SpecField {
  id?: string;
  name: string;
  type: 'number' | 'text' | 'select' | 'boolean';
  unit?: string;
  default_value: any;
  required?: boolean;
  options?: string[];
  validation?: {
    min?: number;
    max?: number;
    pattern?: string;
  };
}

interface QuantityConfig {
  unit: string;
  min: number;
  max: number;
  typical: number;
  allow_variants: boolean;
}

interface VariantRules {
  allowed_fields: string[];
  max_variants: number;
  require_name: boolean;
}

interface ElementTemplateData {
  id: string;
  display_name: string;
  element_type: string;
  category?: {
    id: string;
    name: string;
    color: string;
  };
  has_template: boolean;
  spec_field_count: number;
  default_spec_template: SpecField[];
  quantity_config: QuantityConfig;
  variant_rules: VariantRules;
}

// 요소 타입 영어명을 한국어로 매핑 (동적으로 availableElements에서 찾기)
const getElementTypeKoreanName = (elementType: string, availableElements: ElementDefinition[]): string => {
  const foundElement = availableElements.find(el => el.element_type === elementType);
  return foundElement ? foundElement.display_name : elementType;
};

// 스키마 미리보기 컴포넌트
const SchemaPreview: React.FC<{ schema: string; defaultTemplate: string }> = ({ schema, defaultTemplate }) => {
  const [previewData, setPreviewData] = useState<Record<string, any>>({});

  useEffect(() => {
    try {
      const parsedTemplate = defaultTemplate ? JSON.parse(defaultTemplate) : {};
      setPreviewData(parsedTemplate);
    } catch (error) {
      setPreviewData({});
    }
  }, [defaultTemplate]);

  const handlePreviewChange = (fieldKey: string, value: any) => {
    setPreviewData(prev => ({ ...prev, [fieldKey]: value }));
  };

  const handleNestedPreviewChange = (parentKey: string, fieldKey: string, value: any) => {
    setPreviewData(prev => ({
      ...prev,
      [parentKey]: {
        ...prev[parentKey],
        [fieldKey]: value
      }
    }));
  };

  // enum 값들의 한국어 매핑
  const getKoreanLabel = (fieldKey: string, enumValue: string): string => {
    const mappings: Record<string, Record<string, string>> = {
      kind: {
        main_stage: '메인 무대',
        sub_stage: '서브 무대',
        runway: '런웨이',
        dais: '단상'
      },
      shape: {
        '사각형': '사각형',
        '원형': '원형',
        'T자형': 'T자형',
        '돌출형': '돌출형',
        '투명 무대': '투명 무대'
      },
      installation_method: {
        truss_hanging: '트러스 행잉',
        floor_standing: '바닥 설치',
        ceiling_hanging: '천장 행잉',
        wall_mount: '벽면 설치',
        stand_mount: '스탠드 설치'
      },
      resolution: {
        'Full HD': 'Full HD',
        '4K': '4K'
      },
      projection_type: {
        front_projection: '전면 투사',
        rear_projection: '후면 투사'
      }
    };
    
    return mappings[fieldKey]?.[enumValue] || enumValue;
  };

  let parsedSchema: any = {};
  try {
    parsedSchema = JSON.parse(schema);
  } catch (error) {
    return (
      <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p className="text-red-600 text-sm">스키마 JSON이 올바르지 않습니다.</p>
      </div>
    );
  }

  const properties = parsedSchema.properties || parsedSchema;

  if (Object.keys(properties).length === 0) {
    return (
      <div className="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center">
        <p className="text-gray-500 text-sm">표시할 필드가 없습니다.</p>
      </div>
    );
  }

  return (
    <div className="p-4 bg-gray-50 border border-gray-200 rounded-lg">
      <div className="space-y-4">
        {Object.entries(properties).map(([fieldKey, fieldSchema]: [string, any]) => {
          const currentValue = previewData[fieldKey];

          // 중첩 객체 처리
          if (fieldSchema.type === 'object' && fieldSchema.properties) {
            return (
              <div key={fieldKey} className="space-y-3 p-3 border rounded-md bg-white">
                <h5 className="font-medium text-gray-800 text-sm">
                  {fieldSchema.description || fieldKey}
                </h5>
                {Object.entries(fieldSchema.properties).map(([subFieldKey, subFieldSchema]: [string, any]) => {
                  const subCurrentValue = currentValue?.[subFieldKey];

                  if (subFieldSchema.type === 'number') {
                    return (
                      <div key={subFieldKey} className="space-y-1">
                        <Label className="text-sm">
                          {subFieldSchema.description || subFieldKey}
                          {subFieldSchema.minimum !== undefined && (
                            <span className="text-gray-500 text-xs ml-1">
                              (최소: {subFieldSchema.minimum})
                            </span>
                          )}
                        </Label>
                        <Input
                          type="number"
                          min={subFieldSchema.minimum}
                          value={typeof subCurrentValue === 'number' ? subCurrentValue : ''}
                          onChange={(e) => handleNestedPreviewChange(fieldKey, subFieldKey, parseFloat(e.target.value) || 0)}
                          placeholder={`${subFieldSchema.description || subFieldKey}을 입력하세요`}
                          className="text-sm"
                        />
                      </div>
                    );
                  }

                  if (subFieldSchema.enum) {
                    return (
                      <div key={subFieldKey} className="space-y-1">
                        <Label className="text-sm">
                          {subFieldSchema.description || subFieldKey}
                        </Label>
                        <Select
                          value={typeof subCurrentValue === 'string' ? subCurrentValue : ''}
                          onValueChange={(value) => handleNestedPreviewChange(fieldKey, subFieldKey, value)}
                        >
                          <SelectTrigger className="text-sm">
                            <SelectValue placeholder={`${subFieldSchema.description || subFieldKey}을 선택하세요`} />
                          </SelectTrigger>
                          <SelectContent>
                            {subFieldSchema.enum.map((option: string) => (
                              <SelectItem key={option} value={option}>
                                {getKoreanLabel(subFieldKey, option)}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                    );
                  }

                  return (
                    <div key={subFieldKey} className="space-y-1">
                      <Label className="text-sm">
                        {subFieldSchema.description || subFieldKey}
                      </Label>
                      <Input
                        type="text"
                        value={typeof subCurrentValue === 'string' ? subCurrentValue : ''}
                        onChange={(e) => handleNestedPreviewChange(fieldKey, subFieldKey, e.target.value)}
                        placeholder={`${subFieldSchema.description || subFieldKey}을 입력하세요`}
                        className="text-sm"
                      />
                    </div>
                  );
                })}
              </div>
            );
          }

          if (fieldSchema.type === 'number') {
            return (
              <div key={fieldKey} className="space-y-2">
                <Label>
                  {fieldSchema.description || fieldKey}
                  {fieldSchema.minimum !== undefined && (
                    <span className="text-gray-500 text-sm ml-1">
                      (최소: {fieldSchema.minimum})
                    </span>
                  )}
                </Label>
                <Input
                  type="number"
                  min={fieldSchema.minimum}
                  value={typeof currentValue === 'number' ? currentValue : ''}
                  onChange={(e) => handlePreviewChange(fieldKey, parseFloat(e.target.value) || 0)}
                  placeholder={`${fieldSchema.description || fieldKey}을 입력하세요`}
                />
              </div>
            );
          }

          if (fieldSchema.type === 'boolean') {
            return (
              <div key={fieldKey} className="flex items-center space-x-2">
                <Checkbox
                  checked={!!currentValue}
                  onCheckedChange={(checked) => handlePreviewChange(fieldKey, checked)}
                />
                <Label>
                  {fieldSchema.description || fieldKey}
                </Label>
              </div>
            );
          }

          if (fieldSchema.enum) {
            return (
              <div key={fieldKey} className="space-y-2">
                <Label>
                  {fieldSchema.description || fieldKey}
                </Label>
                <Select
                  value={typeof currentValue === 'string' ? currentValue : ''}
                  onValueChange={(value) => handlePreviewChange(fieldKey, value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder={`${fieldSchema.description || fieldKey}을 선택하세요`} />
                  </SelectTrigger>
                  <SelectContent>
                    {fieldSchema.enum.map((option: string) => (
                      <SelectItem key={option} value={option}>
                        {getKoreanLabel(fieldKey, option)}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            );
          }

          if (fieldSchema.type === 'array' && fieldSchema.items?.enum) {
            const selectedValues = Array.isArray(currentValue) ? currentValue : [];
            
            return (
              <div key={fieldKey} className="space-y-2">
                <Label>{fieldSchema.description || fieldKey}</Label>
                <div className="grid grid-cols-2 gap-2">
                  {fieldSchema.items.enum.map((option: string) => (
                    <div key={option} className="flex items-center space-x-2">
                      <Checkbox
                        checked={selectedValues.includes(option)}
                        onCheckedChange={(checked) => {
                          const newValues = checked
                            ? [...selectedValues, option]
                            : selectedValues.filter((v: string) => v !== option);
                          handlePreviewChange(fieldKey, newValues);
                        }}
                      />
                      <Label className="text-sm">
                        {getKoreanLabel(fieldKey, option)}
                      </Label>
                    </div>
                  ))}
                </div>
              </div>
            );
          }

          return (
            <div key={fieldKey} className="space-y-2">
              <Label>
                {fieldSchema.description || fieldKey}
              </Label>
              <Input
                type="text"
                value={typeof currentValue === 'string' ? currentValue : ''}
                onChange={(e) => handlePreviewChange(fieldKey, e.target.value)}
                placeholder={`${fieldSchema.description || fieldKey}을 입력하세요`}
              />
            </div>
          );
        })}
      </div>

      {/* 현재 데이터 미리보기 */}
      <div className="mt-6 pt-4 border-t border-gray-200">
        <Label className="text-sm font-medium text-gray-700 mb-2 block">생성될 데이터 미리보기</Label>
        <pre className="bg-white p-3 rounded border text-xs font-mono overflow-auto max-h-40">
          {JSON.stringify(previewData, null, 2)}
        </pre>
      </div>
    </div>
  );
};

// 곡선 연결선 컴포넌트
const ConnectionLines: React.FC<{
  sourceItems: any[];
  targetItems: any[];
  selectedSourceId: string | null;
  connections: any[];
  containerRef: React.RefObject<HTMLDivElement | null>;
}> = ({ sourceItems, targetItems, selectedSourceId, connections, containerRef }) => {
  const [lines, setLines] = useState<{ x1: number; y1: number; x2: number; y2: number; strength: number }[]>([]);

  useEffect(() => {
    if (!selectedSourceId || !containerRef.current) return;

    const updateLines = () => {
      const container = containerRef.current;
      if (!container) return;

      const sourceElements = container.querySelectorAll(`[data-source-id="${selectedSourceId}"]`);
      const newLines: { x1: number; y1: number; x2: number; y2: number; strength: number }[] = [];

      connections.forEach((connection) => {
        const targetElement = container.querySelector(`[data-target-id="${connection.id}"]`);
        
        if (sourceElements.length > 0 && targetElement) {
          const sourceRect = sourceElements[0].getBoundingClientRect();
          const targetRect = targetElement.getBoundingClientRect();
          const containerRect = container.getBoundingClientRect();

          const x1 = sourceRect.right - containerRect.left;
          const y1 = sourceRect.top + sourceRect.height / 2 - containerRect.top;
          const x2 = targetRect.left - containerRect.left;
          const y2 = targetRect.top + targetRect.height / 2 - containerRect.top;

          newLines.push({ x1, y1, x2, y2, strength: connection.strength || 5 });
        }
      });

      setLines(newLines);
    };

    // 초기 업데이트
    setTimeout(updateLines, 100);

    // 리사이즈 이벤트 리스너
    window.addEventListener('resize', updateLines);
    return () => window.removeEventListener('resize', updateLines);
  }, [selectedSourceId, connections, sourceItems, targetItems, containerRef]);

  if (lines.length === 0) return null;

  return (
    <svg
      className="absolute inset-0 pointer-events-none"
      style={{ zIndex: 1 }}
    >
      {lines.map((line, index) => {
        const midX = (line.x1 + line.x2) / 2;
        const controlPoint1X = midX;
        const controlPoint2X = midX;
        
        const pathData = `M ${line.x1} ${line.y1} C ${controlPoint1X} ${line.y1}, ${controlPoint2X} ${line.y2}, ${line.x2} ${line.y2}`;
        
        // 강도에 따른 색상과 두께
        const opacity = Math.max(0.3, line.strength / 10);
        const strokeWidth = Math.max(1, line.strength / 3);
        
        return (
          <g key={index}>
            <path
              d={pathData}
              stroke="#3B82F6"
              strokeWidth={strokeWidth}
              fill="none"
              opacity={opacity}
              className="drop-shadow-sm"
            />
            <circle
              cx={line.x2}
              cy={line.y2}
              r="3"
              fill="#3B82F6"
              opacity={opacity}
            />
          </g>
        );
      })}
    </svg>
  );
};

export default function AdminDashboard() {
  const [activeTab, setActiveTab] = useState<string>('categories');
>>>>>>> Stashed changes
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
<<<<<<< Updated upstream
  
  const [formData, setFormData] = useState({
=======
  const [searchTerm, setSearchTerm] = useState('');

  // Data states
  const [categories, setCategories] = useState<Category[]>([]);
  const [elements, setElements] = useState<ElementDefinition[]>([]);
  const [categoryRecommendations, setCategoryRecommendations] = useState<CategoryRecommendation[]>([]);
  const [elementRecommendations, setElementRecommendations] = useState<ElementRecommendation[]>([]);

  // 🆕 동적 스펙 템플릿 관련 상태
  const [elementTemplates, setElementTemplates] = useState<ElementTemplateData[]>([]);
  const [currentTemplate, setCurrentTemplate] = useState<ElementTemplateData | null>(null);
  const [isTemplateModalOpen, setIsTemplateModalOpen] = useState(false);
  const [templateFormData, setTemplateFormData] = useState<{
    default_spec_template: SpecField[];
    quantity_config: QuantityConfig;
    variant_rules: VariantRules;
  }>({
    default_spec_template: [],
    quantity_config: { unit: '개', min: 1, max: 10, typical: 1, allow_variants: false },
    variant_rules: { allowed_fields: [], max_variants: 3, require_name: true }
  });

  // Recommendation management states
  const [selectedSourceCategory, setSelectedSourceCategory] = useState<string | null>(null);
  const [selectedSourceElement, setSelectedSourceElement] = useState<string | null>(null);
  const [currentCategoryRecommendations, setCurrentCategoryRecommendations] = useState<any[]>([]);
  const [currentElementRecommendations, setCurrentElementRecommendations] = useState<any[]>([]);
  const [isLoadingRecommendations, setIsLoadingRecommendations] = useState(false);

  // Form states
  const [currentCategory, setCurrentCategory] = useState<Category | null>(null);
  const [currentElement, setCurrentElement] = useState<ElementDefinition | null>(null);
  const [categoryFormData, setCategoryFormData] = useState<CategoryFormData>({
    name: '',
    display_name: '',
    description: '',
    icon: '',
    color: '#3B82F6',
    group: '하드웨어/설비'
  });
  const [elementFormData, setElementFormData] = useState<ElementFormData>({
    category_id: null,
>>>>>>> Stashed changes
    element_type: '',
    display_name: '',
    description: '',
  });

<<<<<<< Updated upstream
=======
  const [jsonErrors, setJsonErrors] = useState({
    input_schema: '',
    default_details_template: '',
  });

  // Refs
  const connectionContainerRef = useRef<HTMLDivElement | null>(null);

  const tabs: TabType[] = [
    { id: 'categories', name: '카테고리 관리', icon: Tag, description: '이벤트 카테고리를 생성, 수정, 삭제합니다' },
    { id: 'category_recommendations', name: '카테고리 추천', icon: TrendingUp, description: '카테고리 간 추천 관계를 관리합니다' },
    { id: 'element_definitions', name: '요소 정의', icon: Package, description: '이벤트 요소 정의를 생성, 수정, 삭제합니다' },
    { id: 'element_recommendations', name: '요소 추천', icon: Network, description: '요소 간 추천 관계를 관리합니다' },
    { id: 'dynamic_templates', name: '동적 스펙 템플릿', icon: Settings, description: '요소별 동적 스펙 템플릿을 관리합니다' }
  ];

  const categoryGroups = [
    '하드웨어/설비',
    '콘텐츠/연출', 
    '인력/운영',
    '기획/지원 서비스',
    '기타'
  ];

  const complexityLevels = [
    { value: 'basic', label: '기본' },
    { value: 'intermediate', label: '중급' },
    { value: 'advanced', label: '고급' }
  ];

>>>>>>> Stashed changes
  useEffect(() => {
    fetchElements();
  }, []);

  const fetchElements = async () => {
    try {
      setIsLoading(true);
<<<<<<< Updated upstream
      const response = await api.get<{ elements: ElementDefinition[] }>('/api/element-definitions');
      setElements(response.data.elements || []);
=======
      
      if (activeTab === 'categories') {
      const response = await api.get('/api/categories');
      setCategories(response.data.categories || []);
      } else if (activeTab === 'element_definitions') {
        const [categoriesRes, elementsRes] = await Promise.all([
          api.get('/api/categories'),
          api.get('/api/element-definitions')
        ]);
        setCategories(categoriesRes.data.categories || []);
        setElements(elementsRes.data.elements || []);
      } else if (activeTab === 'dynamic_templates') {
        // 🆕 동적 스펙 템플릿 데이터 로드
        const response = await api.get('/api/admin/element-templates');
        setElementTemplates(response.data.data || []);
      } else if (activeTab === 'category_recommendations') {
        const categoriesRes = await api.get('/api/categories');
        setCategories(categoriesRes.data.categories || []);
        // 첫 번째 카테고리를 자동 선택
        if (categoriesRes.data.categories?.length > 0) {
          const firstCategory = categoriesRes.data.categories[0];
          setSelectedSourceCategory(firstCategory.id);
          await loadCategoryRecommendations(firstCategory.id);
        }
      } else if (activeTab === 'element_recommendations') {
        const [categoriesRes, elementsRes] = await Promise.all([
          api.get('/api/categories'),
          api.get('/api/element-definitions')
        ]);
        setCategories(categoriesRes.data.categories || []);
        setElements(elementsRes.data.elements || []);
        // 첫 번째 요소를 자동 선택
        if (elementsRes.data.elements?.length > 0) {
          const firstElement = elementsRes.data.elements[0];
          setSelectedSourceElement(firstElement.id);
          await loadElementRecommendations(firstElement.id);
        }
      }
>>>>>>> Stashed changes
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '요소 정의를 불러오는데 실패했습니다.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleOpenModal = (element?: ElementDefinition) => {
    if (element) {
      setCurrentElement(element);
      setFormData({
        element_type: element.element_type,
        display_name: element.display_name,
        description: element.description || '',
      });
    } else {
      setCurrentElement(null);
      setFormData({ element_type: '', display_name: '', description: '' });
    }
    setError(null);
    setIsModalOpen(true);
  };

<<<<<<< Updated upstream
=======
  const handleElementSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!elementFormData.element_type || !elementFormData.display_name) {
      setError('요소 타입과 표시 이름은 필수입니다.');
      return;
    }

    const schemaValid = validateJson('input_schema', elementFormData.input_schema);
    const templateValid = validateJson('default_details_template', elementFormData.default_details_template);

    if (!schemaValid || !templateValid) {
      setError('JSON 형식 오류를 수정해주세요.');
      return;
    }

    setIsSaving(true);
    try {
      const submitData = {
        category_id: elementFormData.category_id || null,
        element_type: elementFormData.element_type,
        display_name: elementFormData.display_name,
        description: elementFormData.description,
        input_schema: elementFormData.input_schema || '{}',
        default_details_template: elementFormData.default_details_template || '{}',
        complexity_level: elementFormData.complexity_level,
        event_types: JSON.stringify(elementFormData.event_types)
      };

      if (currentElement) {
        await api.put(`/api/element-definitions/${currentElement.id}`, submitData);
      } else {
        await api.post('/api/element-definitions', submitData);
      }
      
      await fetchData();
      handleCloseModal();
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '저장에 실패했습니다.');
    } finally {
      setIsSaving(false);
    }
  };

  const handleElementDelete = async (element: ElementDefinition) => {
    if (!confirm(`"${element.display_name}" 요소를 삭제하시겠습니까?`)) return;

    try {
      await api.delete(`/api/element-definitions/${element.id}`);
      await fetchData();
      } catch (err) {
        const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '삭제에 실패했습니다.');
    }
  };

  // 🆕 ===== 동적 스펙 템플릿 관리 함수들 =====

  // 템플릿 모달 열기
  const handleTemplateModal = async (template: ElementTemplateData) => {
    try {
      setIsLoading(true);
      const response = await api.get(`/api/admin/element-templates/${template.id}`);
      const templateData = response.data.data;
      
      setCurrentTemplate(templateData);
      setTemplateFormData({
        default_spec_template: templateData.default_spec_template || [],
        quantity_config: templateData.quantity_config || { unit: '개', min: 1, max: 10, typical: 1, allow_variants: false },
        variant_rules: templateData.variant_rules || { allowed_fields: [], max_variants: 3, require_name: true }
      });
      setIsTemplateModalOpen(true);
      setError(null);
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '템플릿 정보를 불러오는데 실패했습니다.');
    } finally {
      setIsLoading(false);
    }
  };

  // 스펙 필드 추가
  const addSpecField = () => {
    const newField: SpecField = {
      name: '',
      type: 'text',
      default_value: '',
      required: false
    };
    setTemplateFormData(prev => ({
      ...prev,
      default_spec_template: [...prev.default_spec_template, newField]
    }));
  };

  // 스펙 필드 삭제
  const removeSpecField = (index: number) => {
    setTemplateFormData(prev => ({
      ...prev,
      default_spec_template: prev.default_spec_template.filter((_, i) => i !== index)
    }));
  };

  // 스펙 필드 업데이트
  const updateSpecField = (index: number, field: Partial<SpecField>) => {
    setTemplateFormData(prev => ({
      ...prev,
      default_spec_template: prev.default_spec_template.map((item, i) => 
        i === index ? { ...item, ...field } : item
      )
    }));
  };

  // 템플릿 저장
  const handleTemplateSubmit = async () => {
    if (!currentTemplate) return;

    // 검증
    const hasEmptyNames = templateFormData.default_spec_template.some(field => !field.name.trim());
    if (hasEmptyNames) {
      setError('모든 스펙 필드의 이름을 입력해주세요.');
      return;
    }

    // select 타입 필드에 options 검증
    const selectFieldsWithoutOptions = templateFormData.default_spec_template.filter(
      field => field.type === 'select' && (!field.options || field.options.length === 0)
    );
    if (selectFieldsWithoutOptions.length > 0) {
      setError('선택 타입 필드는 옵션을 최소 1개 이상 입력해주세요.');
      return;
    }

    setIsSaving(true);
    try {
      await api.put(`/api/admin/element-templates/${currentTemplate.id}`, templateFormData);
      await fetchData(); // 목록 새로고침
      setIsTemplateModalOpen(false);
      setCurrentTemplate(null);
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '템플릿 저장에 실패했습니다.');
    } finally {
      setIsSaving(false);
    }
  };

  // 템플릿 초기화
  const handleTemplateReset = async (template: ElementTemplateData) => {
    if (!confirm(`"${template.display_name}" 템플릿을 기본값으로 초기화하시겠습니까?`)) return;

    try {
      await api.post(`/api/admin/element-templates/${template.id}/reset`);
      await fetchData();
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '템플릿 초기화에 실패했습니다.');
    }
  };

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
  const handleCloseModal = () => {
    setIsModalOpen(false);
    setCurrentElement(null);
  };
  
  const validateForm = () => {
    if (!formData.element_type || !formData.display_name) {
      setError('요소 타입과 표시 이름은 필수입니다.');
      return false;
    }
    setError(null);
    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      if (currentElement) {
        await api.patch(`/api/element-definitions/${currentElement.id}`, formData);
      } else {
        await api.post('/api/element-definitions', formData);
      }
      fetchElements();
      handleCloseModal();
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '요청 처리 중 오류가 발생했습니다.');
    }
  };

  const handleDelete = async (id: string) => {
    if (window.confirm('정말로 이 요소를 삭제하시겠습니까?')) {
      try {
        await api.delete(`/api/element-definitions/${id}`);
        fetchElements();
      } catch (err) {
        const error = err as AxiosError<{ message: string }>;
        setError(error.response?.data?.message || '삭제 중 오류가 발생했습니다.');
      }
    }
  };
  
  if (isLoading) return <div className="p-8 text-center">요소 목록을 불러오는 중...</div>;
  if (error && !isModalOpen) return <div className="p-8 text-center text-red-500">{error}</div>;

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">요소 정의 관리</h1>
        <Button onClick={() => handleOpenModal()}>새 요소 정의 추가</Button>
      </div>

      <div className="bg-white shadow rounded-lg">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>요소 타입</TableHead>
              <TableHead>표시 이름</TableHead>
              <TableHead>설명</TableHead>
              <TableHead className="text-right">작업</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {elements.length > 0 ? (
              elements.map((element) => (
                <TableRow key={element.id}>
                  <TableCell className="font-medium">{element.element_type}</TableCell>
                  <TableCell>{element.display_name}</TableCell>
                  <TableCell className="text-sm text-gray-600">{element.description}</TableCell>
                  <TableCell className="text-right">
                    <Button variant="outline" size="sm" className="mr-2" onClick={() => handleOpenModal(element)}>수정</Button>
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(element.id)}>삭제</Button>
                  </TableCell>
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={4} className="text-center text-gray-500 py-8">
                  생성된 요소 정의가 없습니다.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>

<<<<<<< Updated upstream
=======
                    {/* 연결선 */}
                    <ConnectionLines
                      sourceItems={categories}
                      targetItems={currentCategoryRecommendations}
                      selectedSourceId={selectedSourceCategory}
                      connections={currentCategoryRecommendations}
                      containerRef={connectionContainerRef}
                    />
                                 </div>
                               </div>
              )}

              {/* Element Recommendations Tab */}
              {activeTab === 'element_recommendations' && (
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <Network className="w-5 h-5 text-blue-600" />
                      <h3 className="text-lg font-semibold">요소 추천 관계 관리</h3>
                    </div>
                  </div>

                  <div 
                    className="relative grid grid-cols-2 gap-8 min-h-[600px] bg-gray-50 rounded-lg p-6"
                  >
                    {/* 왼쪽: 선택할 요소 */}
                    <div className="space-y-4">
                      <div className="flex items-center gap-2 mb-4">
                        <div className="w-3 h-3 bg-purple-600 rounded-full"></div>
                        <h4 className="text-lg font-medium text-gray-800">요소를 선택하면</h4>
                      </div>
                      <div className="space-y-2 max-h-[500px] overflow-y-auto">
                        {elements.map((element) => {
                          const category = categories.find(c => c.id === element.category_id);
                          return (
                            <div
                              key={element.id}
                              data-source-id={element.id}
                              onClick={() => handleElementSelect(element.id)}
                              className={`p-3 rounded-lg border-2 cursor-pointer transition-all ${
                                selectedSourceElement === element.id
                                  ? 'border-purple-500 bg-purple-50 shadow-md'
                                  : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm'
                              }`}
                            >
                              <div className="flex items-center gap-3">
                                <Package className="w-5 h-5 text-gray-600" />
                                <div className="flex-1">
                                  <div className="font-medium text-gray-900">
                                    {element.display_name}
                                  </div>
                                  <div className="text-sm text-gray-500">
                                    {category?.display_name || '카테고리 없음'}
                                  </div>
                                  <div className="text-xs text-gray-400">
                                    {element.element_type}
                                  </div>
                                </div>
                                <div className="flex items-center gap-2">
                                       <Badge 
                                    variant={
                                      element.complexity_level === 'basic' ? 'secondary' :
                                      element.complexity_level === 'advanced' ? 'destructive' : 'default'
                                    }
                                    className="text-xs"
                                  >
                                    {element.complexity_level === 'basic' ? '기본' :
                                     element.complexity_level === 'advanced' ? '고급' : '중급'}
                                       </Badge>
                                  {selectedSourceElement === element.id && (
                                    <div className="w-2 h-2 bg-purple-600 rounded-full"></div>
                                  )}
                                   </div>
                                 </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>

                    {/* 오른쪽: 추천되는 요소 */}
                    <div className="space-y-4">
                      <div className="flex items-center gap-2 mb-4">
                        <div className="w-3 h-3 bg-orange-600 rounded-full"></div>
                        <h4 className="text-lg font-medium text-gray-800">요소를 추천합니다</h4>
                        <div className="text-sm text-gray-500">
                          (클릭하여 추천 설정/해제)
                                     </div>
                                   </div>
                      
                      {selectedSourceElement ? (
                        <div className="space-y-2 max-h-[500px] overflow-y-auto">
                          {elements
                            .filter(element => element.id !== selectedSourceElement) // 자기 자신 제외
                            .map((element) => {
                              const isRecommended = currentElementRecommendations.some(rec => rec.id === element.id);
                              const recommendation = currentElementRecommendations.find(rec => rec.id === element.id);
                              const elementCategory = categories.find(cat => cat.id === element.category_id);
                              
                              return (
                                <div
                                  key={element.id}
                                  data-target-id={element.id}
                                  onClick={() => toggleElementRecommendation(element.id)}
                                  className={`p-3 rounded-lg border-2 cursor-pointer transition-all ${
                                    isRecommended
                                      ? 'border-orange-500 bg-orange-50 shadow-md'
                                      : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm'
                                  } ${isLoadingRecommendations ? 'pointer-events-none opacity-50' : ''}`}
                                >
                                  <div className="flex items-center gap-3">
                                    <div className="flex items-center justify-center w-6 h-6">
                                      {isRecommended ? (
                                        <div className="w-4 h-4 bg-orange-600 rounded-full flex items-center justify-center">
                                          <div className="w-2 h-2 bg-white rounded-full"></div>
                                     </div>
                                      ) : (
                                        <div className="w-4 h-4 border-2 border-gray-300 rounded-full"></div>
                                      )}
                                   </div>
                                    <div className="flex-1">
                                      <div className="font-medium text-gray-900">
                                        {element.display_name}
                                      </div>
                                      <div className="text-sm text-gray-500 flex items-center gap-2">
                                        {elementCategory && (
                                          <>
                                            <div 
                                              className="w-3 h-3 rounded-full border border-gray-300"
                                              style={{ backgroundColor: elementCategory.color }}
                                            />
                                            {elementCategory.display_name}
                                          </>
                                        )}
                                        <span className="mx-1">•</span>
                                                                                 <Badge variant="outline" className="text-xs">
                                           {element.complexity_level === 'basic' ? '기본' :
                                            element.complexity_level === 'intermediate' ? '중급' : '고급'}
                                         </Badge>
                               </div>
                                      {isRecommended && recommendation && (
                                        <div className="text-xs text-orange-600 mt-1">
                                          {recommendation.reason || '추천 설정됨'}
                                 </div>
                                      )}
                               </div>
                                    {isRecommended && recommendation && (
                                      <div className="flex items-center gap-2">
                                        <Badge 
                                          variant={
                                            recommendation.recommendation_type === 'essential' ? 'destructive' :
                                            recommendation.recommendation_type === 'suggested' ? 'default' : 'secondary'
                                          }
                                          className="text-xs"
                                        >
                                          {recommendation.recommendation_type === 'essential' ? '필수' :
                                           recommendation.recommendation_type === 'suggested' ? '권장' : '선택'}
                                        </Badge>
                                        <div className="text-xs font-medium text-orange-600">
                                          강도: {recommendation.strength}
                             </div>
                                      </div>
                                    )}
                                  </div>
                                </div>
                              );
                            })}
                        </div>
                      ) : (
                        <div className="flex items-center justify-center h-32 text-gray-400">
                          <div className="text-center">
                            <Info className="w-12 h-12 mx-auto mb-2 text-gray-300" />
                            <p className="text-sm">왼쪽에서 요소를 선택하세요</p>
                    </div>
                  </div>
                      )}
                    </div>

                    {/* 연결선 */}
                    <ConnectionLines
                      sourceItems={elements}
                      targetItems={currentElementRecommendations}
                      selectedSourceId={selectedSourceElement}
                      connections={currentElementRecommendations}
                      containerRef={connectionContainerRef}
                    />
                  </div>
                </div>
              )}

              {/* Dynamic Templates Tab */}
              {activeTab === 'dynamic_templates' && (
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <Settings className="w-5 h-5 text-blue-600" />
                      <h3 className="text-lg font-semibold">동적 스펙 템플릿 관리</h3>
                    </div>
                    <Badge variant="outline" className="text-blue-600">
                      {elementTemplates.filter(t => t.has_template).length} / {elementTemplates.length} 요소에 템플릿 적용됨
                    </Badge>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {filterData(elementTemplates, ['display_name', 'element_type']).map((template) => (
                      <Card key={template.id} className="hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3">
                          <div className="flex items-start justify-between">
                            <div className="flex-1">
                              <CardTitle className="text-sm font-medium text-gray-900 mb-1">
                                {template.display_name}
                              </CardTitle>
                              <div className="text-xs text-gray-500 mb-2">
                                {template.element_type}
                              </div>
                              {template.category && (
                                <div className="flex items-center gap-1 mb-2">
                                  <div 
                                    className="w-2 h-2 rounded-full"
                                    style={{ backgroundColor: template.category.color }}
                                  />
                                  <span className="text-xs text-gray-600">
                                    {template.category.name}
                                  </span>
                                </div>
                              )}
                            </div>
                            <div className="flex items-center gap-1">
                              {template.has_template ? (
                                <Check className="w-4 h-4 text-green-600" />
                              ) : (
                                <AlertCircle className="w-4 h-4 text-gray-400" />
                              )}
                            </div>
                          </div>
                        </CardHeader>
                        <CardContent className="pt-0">
                          <div className="space-y-2 mb-4">
                            <div className="flex justify-between text-xs">
                              <span className="text-gray-500">스펙 필드:</span>
                              <span className="font-medium">
                                {template.spec_field_count}개
                              </span>
                            </div>
                            <div className="flex justify-between text-xs">
                              <span className="text-gray-500">수량 단위:</span>
                              <span className="font-medium">
                                {template.quantity_config.unit}
                              </span>
                            </div>
                            <div className="flex justify-between text-xs">
                              <span className="text-gray-500">변형 허용:</span>
                              <span className="font-medium">
                                {template.quantity_config.allow_variants ? '예' : '아니오'}
                              </span>
                            </div>
                          </div>
                          
                          <div className="flex gap-2">
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => handleTemplateModal(template)}
                              className="flex-1"
                            >
                              <Edit className="w-3 h-3 mr-1" />
                              편집
                            </Button>
                            {template.has_template && (
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => handleTemplateReset(template)}
                                className="text-gray-500 hover:text-red-600"
                              >
                                <X className="w-3 h-3" />
                              </Button>
                            )}
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>

                  {filterData(elementTemplates, ['display_name', 'element_type']).length === 0 && (
                    <div className="text-center py-12">
                      <Package className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                      <p className="text-gray-500">검색 결과가 없습니다.</p>
                    </div>
                  )}
                </div>
              )}
            </>
          )}
        </CardContent>
      </Card>

      {/* 🆕 Dynamic Template Edit Modal */}
      <Dialog open={isTemplateModalOpen} onOpenChange={setIsTemplateModalOpen}>
        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
<<<<<<< Updated upstream
=======
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <Settings className="w-5 h-5" />
              {currentTemplate?.display_name} - 동적 스펙 템플릿 편집
            </DialogTitle>
            <DialogDescription>
              {currentTemplate?.element_type} 요소의 스펙 필드와 수량 설정을 관리합니다.
            </DialogDescription>
          </DialogHeader>

          {currentTemplate && (
            <div className="space-y-6">
              {/* 수량 설정 */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-base">수량 설정</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="grid grid-cols-4 gap-4">
                    <div className="space-y-2">
                      <Label>단위</Label>
                      <Input
                        value={templateFormData.quantity_config.unit}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, unit: e.target.value }
                        }))}
                        placeholder="대, 개, 세트..."
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>최소</Label>
                      <Input
                        type="number"
                        min="1"
                        value={templateFormData.quantity_config.min}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, min: parseInt(e.target.value) || 1 }
                        }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>최대</Label>
                      <Input
                        type="number"
                        min="1"
                        value={templateFormData.quantity_config.max}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, max: parseInt(e.target.value) || 10 }
                        }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>권장</Label>
                      <Input
                        type="number"
                        min="1"
                        value={templateFormData.quantity_config.typical}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, typical: parseInt(e.target.value) || 1 }
                        }))}
                      />
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Checkbox
                      checked={templateFormData.quantity_config.allow_variants}
                      onCheckedChange={(checked) => setTemplateFormData(prev => ({
                        ...prev,
                        quantity_config: { ...prev.quantity_config, allow_variants: !!checked }
                      }))}
                    />
                    <Label className="text-sm">스펙 변형 허용</Label>
                  </div>
                </CardContent>
              </Card>

              {/* 스펙 필드 관리 */}
              <Card>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-base">스펙 필드</CardTitle>
                    <Button size="sm" onClick={addSpecField}>
                      <Plus className="w-4 h-4 mr-1" />
                      필드 추가
                    </Button>
                  </div>
                </CardHeader>
                <CardContent>
                  {templateFormData.default_spec_template.length > 0 ? (
                    <div className="space-y-4">
                      {templateFormData.default_spec_template.map((field, index) => (
                        <Card key={index} className="p-4">
                          <div className="grid grid-cols-12 gap-3 items-start">
                            {/* 필드명 */}
                            <div className="col-span-3">
                              <Label className="text-xs">필드명</Label>
                              <Input
                                value={field.name}
                                onChange={(e) => updateSpecField(index, { name: e.target.value })}
                                placeholder="가로, 세로, 해상도..."
                                className="text-sm"
                              />
                            </div>

                            {/* 타입 */}
                            <div className="col-span-2">
                              <Label className="text-xs">타입</Label>
                              <Select
                                value={field.type}
                                onValueChange={(value: 'number' | 'text' | 'select' | 'boolean') => 
                                  updateSpecField(index, { type: value })
                                }
                              >
                                <SelectTrigger className="text-sm">
                                  <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                  <SelectItem value="number">숫자</SelectItem>
                                  <SelectItem value="text">텍스트</SelectItem>
                                  <SelectItem value="select">선택</SelectItem>
                                  <SelectItem value="boolean">체크박스</SelectItem>
                                </SelectContent>
                              </Select>
                            </div>

                            {/* 단위 */}
                            <div className="col-span-2">
                              <Label className="text-xs">단위</Label>
                              <Input
                                value={field.unit || ''}
                                onChange={(e) => updateSpecField(index, { unit: e.target.value })}
                                placeholder="m, W, 대..."
                                className="text-sm"
                              />
                            </div>

                            {/* 기본값 */}
                            <div className="col-span-2">
                              <Label className="text-xs">기본값</Label>
                              {field.type === 'boolean' ? (
                                <div className="flex items-center mt-1">
                                  <Checkbox
                                    checked={!!field.default_value}
                                    onCheckedChange={(checked) => updateSpecField(index, { default_value: checked })}
                                  />
                                </div>
                              ) : field.type === 'select' ? (
                                <Select
                                  value={field.default_value || ''}
                                  onValueChange={(value) => updateSpecField(index, { default_value: value })}
                                >
                                  <SelectTrigger className="text-sm">
                                    <SelectValue placeholder="선택..." />
                                  </SelectTrigger>
                                  <SelectContent>
                                    {field.options?.map((option) => (
                                      <SelectItem key={option} value={option}>
                                        {option}
                                      </SelectItem>
                                    ))}
                                  </SelectContent>
                                </Select>
                              ) : (
                                <Input
                                  value={field.default_value || ''}
                                  onChange={(e) => updateSpecField(index, { 
                                    default_value: field.type === 'number' ? 
                                      (parseFloat(e.target.value) || 0) : 
                                      e.target.value 
                                  })}
                                  type={field.type === 'number' ? 'number' : 'text'}
                                  className="text-sm"
                                />
                              )}
                            </div>

                            {/* 필수/삭제 */}
                            <div className="col-span-2">
                              <Label className="text-xs">옵션</Label>
                              <div className="flex items-center gap-2 mt-1">
                                <div className="flex items-center space-x-1">
                                  <Checkbox
                                    checked={!!field.required}
                                    onCheckedChange={(checked) => updateSpecField(index, { required: !!checked })}
                                  />
                                  <span className="text-xs">필수</span>
                                </div>
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  onClick={() => removeSpecField(index)}
                                  className="h-6 w-6 p-0 text-red-600 hover:text-red-800"
                                >
                                  <Trash2 className="w-3 h-3" />
                                </Button>
                              </div>
                            </div>

                            {/* select 타입일 때 옵션 관리 */}
                            {field.type === 'select' && (
                              <div className="col-span-12 mt-2">
                                <Label className="text-xs">선택 옵션 (쉼표로 구분)</Label>
                                <Input
                                  value={field.options?.join(', ') || ''}
                                  onChange={(e) => updateSpecField(index, { 
                                    options: e.target.value.split(',').map(s => s.trim()).filter(s => s) 
                                  })}
                                  placeholder="옵션1, 옵션2, 옵션3..."
                                  className="text-sm"
                                />
                              </div>
                            )}

                            {/* number 타입일 때 검증 규칙 */}
                            {field.type === 'number' && (
                              <div className="col-span-12 mt-2">
                                <Label className="text-xs">검증 규칙</Label>
                                <div className="grid grid-cols-2 gap-2">
                                  <Input
                                    type="number"
                                    value={field.validation?.min || ''}
                                    onChange={(e) => updateSpecField(index, { 
                                      validation: { 
                                        ...field.validation, 
                                        min: parseFloat(e.target.value) || undefined 
                                      } 
                                    })}
                                    placeholder="최소값"
                                    className="text-sm"
                                  />
                                  <Input
                                    type="number"
                                    value={field.validation?.max || ''}
                                    onChange={(e) => updateSpecField(index, { 
                                      validation: { 
                                        ...field.validation, 
                                        max: parseFloat(e.target.value) || undefined 
                                      } 
                                    })}
                                    placeholder="최대값"
                                    className="text-sm"
                                  />
                                </div>
                              </div>
                            )}
                          </div>
                        </Card>
                      ))}
                    </div>
                  ) : (
                    <div className="text-center py-8">
                      <Package className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                      <p className="text-gray-500 text-sm">스펙 필드를 추가해주세요.</p>
                    </div>
                  )}
                </CardContent>
              </Card>

              {error && (
                <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                  {error}
                </div>
              )}

              <DialogFooter>
                <Button type="button" variant="outline" onClick={() => setIsTemplateModalOpen(false)}>
                  취소
                </Button>
                <Button onClick={handleTemplateSubmit} disabled={isSaving}>
                  {isSaving ? (
                    <>
                      <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                      저장 중...
                    </>
                  ) : (
                    <>
                      <Save className="w-4 h-4 mr-2" />
                      저장
                    </>
                  )}
                </Button>
              </DialogFooter>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Modal for Category/Element CRUD */}
      <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
>>>>>>> Stashed changes
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <Settings className="w-5 h-5" />
              {currentTemplate?.display_name} - 동적 스펙 템플릿 편집
            </DialogTitle>
            <DialogDescription>
              {currentTemplate?.element_type} 요소의 스펙 필드와 수량 설정을 관리합니다.
            </DialogDescription>
          </DialogHeader>

          {currentTemplate && (
            <div className="space-y-6">
              {/* 수량 설정 */}
              <Card>
                <CardHeader>
                  <CardTitle className="text-base">수량 설정</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="grid grid-cols-4 gap-4">
                    <div className="space-y-2">
                      <Label>단위</Label>
                      <Input
                        value={templateFormData.quantity_config.unit}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, unit: e.target.value }
                        }))}
                        placeholder="대, 개, 세트..."
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>최소</Label>
                      <Input
                        type="number"
                        min="1"
                        value={templateFormData.quantity_config.min}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, min: parseInt(e.target.value) || 1 }
                        }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>최대</Label>
                      <Input
                        type="number"
                        min="1"
                        value={templateFormData.quantity_config.max}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, max: parseInt(e.target.value) || 10 }
                        }))}
                      />
                    </div>
                    <div className="space-y-2">
                      <Label>권장</Label>
                      <Input
                        type="number"
                        min="1"
                        value={templateFormData.quantity_config.typical}
                        onChange={(e) => setTemplateFormData(prev => ({
                          ...prev,
                          quantity_config: { ...prev.quantity_config, typical: parseInt(e.target.value) || 1 }
                        }))}
                      />
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Checkbox
                      checked={templateFormData.quantity_config.allow_variants}
                      onCheckedChange={(checked) => setTemplateFormData(prev => ({
                        ...prev,
                        quantity_config: { ...prev.quantity_config, allow_variants: !!checked }
                      }))}
                    />
                    <Label className="text-sm">스펙 변형 허용</Label>
                  </div>
                </CardContent>
              </Card>

              {/* 스펙 필드 관리 */}
              <Card>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-base">스펙 필드</CardTitle>
                    <Button size="sm" onClick={addSpecField}>
                      <Plus className="w-4 h-4 mr-1" />
                      필드 추가
                    </Button>
                  </div>
                </CardHeader>
                <CardContent>
                  {templateFormData.default_spec_template.length > 0 ? (
                    <div className="space-y-4">
                      {templateFormData.default_spec_template.map((field, index) => (
                        <Card key={index} className="p-4">
                          <div className="grid grid-cols-12 gap-3 items-start">
                            {/* 필드명 */}
                            <div className="col-span-3">
                              <Label className="text-xs">필드명</Label>
                              <Input
                                value={field.name}
                                onChange={(e) => updateSpecField(index, { name: e.target.value })}
                                placeholder="가로, 세로, 해상도..."
                                className="text-sm"
                              />
                            </div>

                            {/* 타입 */}
                            <div className="col-span-2">
                              <Label className="text-xs">타입</Label>
                              <Select
                                value={field.type}
                                onValueChange={(value: 'number' | 'text' | 'select' | 'boolean') => 
                                  updateSpecField(index, { type: value })
                                }
                              >
                                <SelectTrigger className="text-sm">
                                  <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                  <SelectItem value="number">숫자</SelectItem>
                                  <SelectItem value="text">텍스트</SelectItem>
                                  <SelectItem value="select">선택</SelectItem>
                                  <SelectItem value="boolean">체크박스</SelectItem>
                                </SelectContent>
                              </Select>
                            </div>

                            {/* 단위 */}
                            <div className="col-span-2">
                              <Label className="text-xs">단위</Label>
                              <Input
                                value={field.unit || ''}
                                onChange={(e) => updateSpecField(index, { unit: e.target.value })}
                                placeholder="m, W, 대..."
                                className="text-sm"
                              />
                            </div>

                            {/* 기본값 */}
                            <div className="col-span-2">
                              <Label className="text-xs">기본값</Label>
                              {field.type === 'boolean' ? (
                                <div className="flex items-center mt-1">
                                  <Checkbox
                                    checked={!!field.default_value}
                                    onCheckedChange={(checked) => updateSpecField(index, { default_value: checked })}
                                  />
                                </div>
                              ) : field.type === 'select' ? (
                                <Select
                                  value={field.default_value || ''}
                                  onValueChange={(value) => updateSpecField(index, { default_value: value })}
                                >
                                  <SelectTrigger className="text-sm">
                                    <SelectValue placeholder="선택..." />
                                  </SelectTrigger>
                                  <SelectContent>
                                    {field.options?.map((option) => (
                                      <SelectItem key={option} value={option}>
                                        {option}
                                      </SelectItem>
                                    ))}
                                  </SelectContent>
                                </Select>
                              ) : (
                                <Input
                                  value={field.default_value || ''}
                                  onChange={(e) => updateSpecField(index, { 
                                    default_value: field.type === 'number' ? 
                                      (parseFloat(e.target.value) || 0) : 
                                      e.target.value 
                                  })}
                                  type={field.type === 'number' ? 'number' : 'text'}
                                  className="text-sm"
                                />
                              )}
                            </div>

                            {/* 필수/삭제 */}
                            <div className="col-span-2">
                              <Label className="text-xs">옵션</Label>
                              <div className="flex items-center gap-2 mt-1">
                                <div className="flex items-center space-x-1">
                                  <Checkbox
                                    checked={!!field.required}
                                    onCheckedChange={(checked) => updateSpecField(index, { required: !!checked })}
                                  />
                                  <span className="text-xs">필수</span>
                                </div>
                                <Button
                                  size="sm"
                                  variant="ghost"
                                  onClick={() => removeSpecField(index)}
                                  className="h-6 w-6 p-0 text-red-600 hover:text-red-800"
                                >
                                  <Trash2 className="w-3 h-3" />
                                </Button>
                              </div>
                            </div>

                            {/* select 타입일 때 옵션 관리 */}
                            {field.type === 'select' && (
                              <div className="col-span-12 mt-2">
                                <Label className="text-xs">선택 옵션 (쉼표로 구분)</Label>
                                <Input
                                  value={field.options?.join(', ') || ''}
                                  onChange={(e) => updateSpecField(index, { 
                                    options: e.target.value.split(',').map(s => s.trim()).filter(s => s) 
                                  })}
                                  placeholder="옵션1, 옵션2, 옵션3..."
                                  className="text-sm"
                                />
                              </div>
                            )}

                            {/* number 타입일 때 검증 규칙 */}
                            {field.type === 'number' && (
                              <div className="col-span-12 mt-2">
                                <Label className="text-xs">검증 규칙</Label>
                                <div className="grid grid-cols-2 gap-2">
                                  <Input
                                    type="number"
                                    value={field.validation?.min || ''}
                                    onChange={(e) => updateSpecField(index, { 
                                      validation: { 
                                        ...field.validation, 
                                        min: parseFloat(e.target.value) || undefined 
                                      } 
                                    })}
                                    placeholder="최소값"
                                    className="text-sm"
                                  />
                                  <Input
                                    type="number"
                                    value={field.validation?.max || ''}
                                    onChange={(e) => updateSpecField(index, { 
                                      validation: { 
                                        ...field.validation, 
                                        max: parseFloat(e.target.value) || undefined 
                                      } 
                                    })}
                                    placeholder="최대값"
                                    className="text-sm"
                                  />
                                </div>
                              </div>
                            )}
                          </div>
                        </Card>
                      ))}
                    </div>
                  ) : (
                    <div className="text-center py-8">
                      <Package className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                      <p className="text-gray-500 text-sm">스펙 필드를 추가해주세요.</p>
                    </div>
                  )}
                </CardContent>
              </Card>

              {error && (
                <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                  {error}
                </div>
              )}

              <DialogFooter>
                <Button type="button" variant="outline" onClick={() => setIsTemplateModalOpen(false)}>
                  취소
                </Button>
                <Button onClick={handleTemplateSubmit} disabled={isSaving}>
                  {isSaving ? (
                    <>
                      <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                      저장 중...
                    </>
                  ) : (
                    <>
                      <Save className="w-4 h-4 mr-2" />
                      저장
                    </>
                  )}
                </Button>
              </DialogFooter>
            </div>
          )}
        </DialogContent>
      </Dialog>

      {/* Modal for Category/Element CRUD */}
>>>>>>> Stashed changes
      <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{currentElement ? '요소 정의 수정' : '새 요소 정의 추가'}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && <p className="text-red-500 text-sm">{error}</p>}
            <div>
              <Label htmlFor="element_type">요소 타입 <span className="text-red-500">*</span></Label>
              <Input
                id="element_type"
                value={formData.element_type}
                onChange={(e) => setFormData({ ...formData, element_type: e.target.value })}
                disabled={!!currentElement}
                required
              />
               {currentElement && <p className="text-xs text-gray-500 mt-1">요소 타입은 변경할 수 없습니다.</p>}
            </div>
            <div>
              <Label htmlFor="display_name">표시 이름 <span className="text-red-500">*</span></Label>
              <Input
                id="display_name"
                value={formData.display_name}
                onChange={(e) => setFormData({ ...formData, display_name: e.target.value })}
                required
              />
            </div>
            <div>
              <Label htmlFor="description">설명</Label>
              <Textarea
                id="description"
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              />
            </div>
            <DialogFooter>
              <Button type="button" variant="ghost" onClick={handleCloseModal}>취소</Button>
              <Button type="submit">저장</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  );
} 