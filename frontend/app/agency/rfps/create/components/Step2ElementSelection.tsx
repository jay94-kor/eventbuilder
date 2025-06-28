import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { RfpFormData } from '@/lib/types';

interface Step2ElementSelectionProps {
  formData: RfpFormData;
  onElementsChange: (elements: RfpFormData['elements']) => void;
}

// 기본 요소 정의
const DEFAULT_ELEMENTS = [
  {
    element_type: 'stage',
    display_name: '무대',
    description: '공연 및 발표를 위한 무대 설치',
    category: 'structure'
  },
  {
    element_type: 'sound_system',
    display_name: '음향 시스템',
    description: '스피커, 마이크, 음향 장비',
    category: 'equipment'
  },
  {
    element_type: 'lighting',
    display_name: '조명',
    description: '무대 조명 및 연출 조명',
    category: 'equipment'
  },
  {
    element_type: 'led_screen',
    display_name: 'LED 스크린',
    description: '대형 LED 디스플레이',
    category: 'equipment'
  },
  {
    element_type: 'decoration',
    display_name: '장식',
    description: '행사장 장식 및 꾸미기',
    category: 'decoration'
  },
  {
    element_type: 'catering',
    display_name: '케이터링',
    description: '음식 및 음료 서비스',
    category: 'service'
  },
  {
    element_type: 'security',
    display_name: '보안',
    description: '행사장 보안 서비스',
    category: 'service'
  },
  {
    element_type: 'photography',
    display_name: '사진/영상',
    description: '행사 촬영 및 기록',
    category: 'service'
  }
];

export default function Step2ElementSelection({ formData, onElementsChange }: Step2ElementSelectionProps) {
  const [selectedElements, setSelectedElements] = useState<string[]>(
    formData.elements.map(el => el.element_type)
  );

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

  const groupedElements = DEFAULT_ELEMENTS.reduce((groups, element) => {
    const category = element.category;
    if (!groups[category]) {
      groups[category] = [];
    }
    groups[category].push(element);
    return groups;
  }, {} as Record<string, typeof DEFAULT_ELEMENTS>);

  const categoryNames = {
    structure: '구조물',
    equipment: '장비',
    decoration: '장식',
    service: '서비스'
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow">
      <h2 className="text-xl font-semibold mb-6">2단계: 요소 선택</h2>
      <p className="text-gray-600 mb-6">행사에 필요한 요소들을 선택해주세요.</p>
      
      <div className="space-y-6">
        {Object.entries(groupedElements).map(([category, elements]) => (
          <Card key={category}>
            <CardHeader>
              <CardTitle className="text-lg">{categoryNames[category as keyof typeof categoryNames]}</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {elements.map((element) => (
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
          {selectedElements.map(type => {
            const element = DEFAULT_ELEMENTS.find(el => el.element_type === type);
            return (
              <span key={type} className="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                {element?.display_name}
              </span>
            );
          })}
        </div>
      </div>
    </div>
  );
}