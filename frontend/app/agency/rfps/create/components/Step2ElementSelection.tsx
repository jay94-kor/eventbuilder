import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { RfpFormData } from '@/lib/types';
import { elementDefinitionApi, ElementCategory } from '@/lib/api';

interface Step2ElementSelectionProps {
  formData: RfpFormData;
  onElementsChange: (elements: RfpFormData['elements']) => void;
}

export default function Step2ElementSelection({ formData, onElementsChange }: Step2ElementSelectionProps) {
  const [selectedElements, setSelectedElements] = useState<string[]>(
    formData.elements.map(el => el.element_type)
  );
  const [categories, setCategories] = useState<ElementCategory[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        setLoading(true);
        const data = await elementDefinitionApi.getGroupedByCategory();
        setCategories(data);
      } catch (err) {
        console.error('카테고리 목록 로딩 실패:', err);
        setError('요소 목록을 불러오는데 실패했습니다.');
      } finally {
        setLoading(false);
      }
    };

    fetchCategories();
  }, []);

  const handleElementToggle = (elementType: string, checked: boolean) => {
    let newSelectedElements: string[];
    
    if (checked) {
      newSelectedElements = [...selectedElements, elementType];
    } else {
      newSelectedElements = selectedElements.filter(type => type !== elementType);
    }
    
    setSelectedElements(newSelectedElements);
    
    // 새로운 elements 배열 생성
    const newElements = newSelectedElements.map(type => {
      // 기존 요소가 있으면 유지, 없으면 새로 생성
      const existingElement = formData.elements.find(el => el.element_type === type);
      if (existingElement) {
        return existingElement;
      }
      
      return {
        element_type: type,
        details: {},
        allocated_budget: 1000000,
        prepayment_ratio: 0.3,
        prepayment_due_date: '',
        balance_ratio: 0.7,
        balance_due_date: '',
      };
    });
    
    onElementsChange(newElements);
  };

  const getElementDisplayName = (elementType: string): string => {
    for (const category of categories) {
      const element = category.elements.find(el => el.element_type === elementType);
      if (element) {
        return element.display_name;
      }
    }
    return elementType;
  };

  if (loading) {
    return (
      <div className="bg-white p-6 rounded-lg shadow">
        <h2 className="text-xl font-semibold mb-6">2단계: 요소 선택</h2>
        <div className="flex items-center justify-center h-32">
          <div className="text-gray-500">요소 목록을 불러오는 중...</div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-white p-6 rounded-lg shadow">
        <h2 className="text-xl font-semibold mb-6">2단계: 요소 선택</h2>
        <div className="flex items-center justify-center h-32">
          <div className="text-red-500">{error}</div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white p-6 rounded-lg shadow">
      <h2 className="text-xl font-semibold mb-6">2단계: 요소 선택</h2>
      <p className="text-gray-600 mb-6">행사에 필요한 요소들을 선택해주세요.</p>
      
      <div className="space-y-6">
        {categories.map((category) => (
          <Card key={category.category}>
            <CardHeader>
              <CardTitle className="text-lg flex items-center gap-2">
                <span>{category.icon}</span>
                {category.name}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {category.elements.map((element) => (
                  <div key={element.element_type} className="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50">
                    <Checkbox
                      id={element.element_type}
                      checked={selectedElements.includes(element.element_type)}
                      onCheckedChange={(checked) => 
                        handleElementToggle(element.element_type, checked as boolean)
                      }
                    />
                    <div className="flex-1">
                      <label htmlFor={element.element_type} className="font-medium cursor-pointer">
                        {element.display_name}
                      </label>
                      <p className="text-sm text-gray-600 mt-1">{element.description}</p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
      
      <div className="mt-6 p-4 bg-blue-50 rounded-lg">
        <h3 className="font-medium text-blue-800 mb-2">선택된 요소: {selectedElements.length}개</h3>
        <div className="flex flex-wrap gap-2">
          {selectedElements.map(type => (
            <span key={type} className="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
              {getElementDisplayName(type)}
            </span>
          ))}
        </div>
      </div>
    </div>
  );
}