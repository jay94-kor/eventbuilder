import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { CalendarIcon } from '@radix-ui/react-icons';
import { format } from 'date-fns';
import { RfpFormData } from '@/lib/types';

interface Step3ElementDetailsProps {
  formData: RfpFormData;
  onElementUpdate: (elementIndex: number, field: string, value: any) => void;
}

const formatDateForDisplay = (dateString: string) => {
  if (!dateString) return "날짜 선택";
  try {
    return format(new Date(dateString), "yyyy-MM-dd");
  } catch {
    return "날짜 선택";
  }
};

const stringToDate = (dateString: string): Date | undefined => {
  if (!dateString) return undefined;
  try {
    return new Date(dateString);
  } catch {
    return undefined;
  }
};

export default function Step3ElementDetails({ formData, onElementUpdate }: Step3ElementDetailsProps) {
  const handleFieldChange = (elementIndex: number, field: string, value: any) => {
    onElementUpdate(elementIndex, field, value);
  };

  const handleDetailsChange = (elementIndex: number, detailKey: string, value: string) => {
    const currentElement = formData.elements[elementIndex];
    const updatedDetails = {
      ...currentElement.details,
      [detailKey]: value
    };
    onElementUpdate(elementIndex, 'details', updatedDetails);
  };

  const handleDateChange = (elementIndex: number, field: string, date: Date | undefined) => {
    const dateString = date ? date.toISOString().split('T')[0] : '';
    onElementUpdate(elementIndex, field, dateString);
  };

  const getElementDisplayName = (elementType: string) => {
    const elementNames: Record<string, string> = {
      stage: '무대',
      sound_system: '음향 시스템',
      lighting: '조명',
      led_screen: 'LED 스크린',
      decoration: '장식',
      catering: '케이터링',
      security: '보안',
      photography: '사진/영상'
    };
    return elementNames[elementType] || elementType;
  };

  const getDefaultDetailsFields = (elementType: string) => {
    const defaultFields: Record<string, Array<{key: string, label: string, type: string, placeholder: string}>> = {
      stage: [
        { key: 'size', label: '크기', type: 'text', placeholder: '예: 10m x 8m' },
        { key: 'height', label: '높이', type: 'text', placeholder: '예: 1.2m' },
        { key: 'material', label: '재질', type: 'text', placeholder: '예: 목재, 철골' }
      ],
      sound_system: [
        { key: 'speaker_count', label: '스피커 수량', type: 'number', placeholder: '예: 8' },
        { key: 'microphone_type', label: '마이크 종류', type: 'text', placeholder: '예: 무선 핸드마이크, 헤드셋' },
        { key: 'coverage_area', label: '커버리지', type: 'text', placeholder: '예: 500평' }
      ],
      lighting: [
        { key: 'light_count', label: '조명 수량', type: 'number', placeholder: '예: 20' },
        { key: 'light_type', label: '조명 종류', type: 'text', placeholder: '예: LED, 무빙라이트' },
        { key: 'color_options', label: '색상 옵션', type: 'text', placeholder: '예: RGB, 단색' }
      ],
      led_screen: [
        { key: 'screen_size', label: '스크린 크기', type: 'text', placeholder: '예: 5m x 3m' },
        { key: 'resolution', label: '해상도', type: 'text', placeholder: '예: 1920x1080' },
        { key: 'brightness', label: '밝기', type: 'text', placeholder: '예: 5000 nits' }
      ],
      decoration: [
        { key: 'theme', label: '테마', type: 'text', placeholder: '예: 모던, 클래식' },
        { key: 'color_scheme', label: '색상 구성', type: 'text', placeholder: '예: 화이트&골드' },
        { key: 'flower_arrangement', label: '꽃장식', type: 'text', placeholder: '예: 계절꽃, 조화' }
      ],
      catering: [
        { key: 'meal_type', label: '식사 종류', type: 'text', placeholder: '예: 뷔페, 코스요리' },
        { key: 'guest_count', label: '예상 인원', type: 'number', placeholder: '예: 100' },
        { key: 'dietary_restrictions', label: '식이 제한', type: 'text', placeholder: '예: 할랄, 비건' }
      ],
      security: [
        { key: 'guard_count', label: '경비 인원', type: 'number', placeholder: '예: 4' },
        { key: 'security_level', label: '보안 등급', type: 'text', placeholder: '예: 일반, 고급' },
        { key: 'equipment', label: '보안 장비', type: 'text', placeholder: '예: CCTV, 금속탐지기' }
      ],
      photography: [
        { key: 'photographer_count', label: '촬영 인원', type: 'number', placeholder: '예: 2' },
        { key: 'service_type', label: '서비스 종류', type: 'text', placeholder: '예: 사진+영상, 사진만' },
        { key: 'delivery_format', label: '전달 형식', type: 'text', placeholder: '예: 디지털, 인화' }
      ]
    };
    return defaultFields[elementType] || [];
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow">
      <h2 className="text-xl font-semibold mb-6">3단계: 요소 상세 설정</h2>
      <p className="text-gray-600 mb-6">선택한 요소들의 상세 정보를 입력해주세요.</p>
      
      <div className="space-y-6">
        {formData.elements.map((element, index) => (
          <Card key={index}>
            <CardHeader>
              <CardTitle className="text-lg">{getElementDisplayName(element.element_type)}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* 기본 정보 */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor={`budget-${index}`}>할당 예산 (원) <span className="text-red-500">*</span></Label>
                  <Input
                    id={`budget-${index}`}
                    type="number"
                    value={element.allocated_budget}
                    onChange={(e) => handleFieldChange(index, 'allocated_budget', Number(e.target.value))}
                    placeholder="예: 10000000"
                  />
                </div>
                
                <div>
                  <Label htmlFor={`prepayment-${index}`}>선급금 비율 (%)</Label>
                  <Input
                    id={`prepayment-${index}`}
                    type="number"
                    min="0"
                    max="1"
                    step="0.1"
                    value={element.prepayment_ratio}
                    onChange={(e) => handleFieldChange(index, 'prepayment_ratio', Number(e.target.value))}
                    placeholder="예: 0.3 (30%)"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor={`prepayment-date-${index}`}>선급금 지급일</Label>
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button variant="outline" className="w-full justify-start text-left font-normal">
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {formatDateForDisplay(element.prepayment_due_date)}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                      <Calendar
                        mode="single"
                        selected={stringToDate(element.prepayment_due_date)}
                        onSelect={(date) => handleDateChange(index, 'prepayment_due_date', date)}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                </div>

                <div>
                  <Label htmlFor={`balance-date-${index}`}>잔금 지급일</Label>
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button variant="outline" className="w-full justify-start text-left font-normal">
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {formatDateForDisplay(element.balance_due_date)}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                      <Calendar
                        mode="single"
                        selected={stringToDate(element.balance_due_date)}
                        onSelect={(date) => handleDateChange(index, 'balance_due_date', date)}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                </div>
              </div>

              {/* 요소별 상세 정보 */}
              <div>
                <Label className="text-sm font-medium text-gray-700 mb-3 block">상세 사양</Label>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {getDefaultDetailsFields(element.element_type).map((field) => (
                    <div key={field.key}>
                      <Label htmlFor={`${field.key}-${index}`}>{field.label}</Label>
                      <Input
                        id={`${field.key}-${index}`}
                        type={field.type}
                        value={element.details[field.key] || ''}
                        onChange={(e) => handleDetailsChange(index, field.key, e.target.value)}
                        placeholder={field.placeholder}
                      />
                    </div>
                  ))}
                </div>
              </div>

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
            </CardContent>
          </Card>
        ))}
      </div>

      {/* 요약 정보 */}
      <Card className="mt-6">
        <CardHeader>
          <CardTitle className="text-lg">예산 요약</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-2">
            {formData.elements.map((element, index) => (
              <div key={index} className="flex justify-between items-center">
                <span>{getElementDisplayName(element.element_type)}</span>
                <span className="font-medium">
                  {Number(element.allocated_budget).toLocaleString('ko-KR')}원
                </span>
              </div>
            ))}
            <div className="border-t pt-2 mt-2">
              <div className="flex justify-between items-center font-bold">
                <span>총 예산</span>
                <span>
                  {formData.elements
                    .reduce((sum, element) => sum + Number(element.allocated_budget), 0)
                    .toLocaleString('ko-KR')}원
                </span>
              </div>
              <div className="text-sm text-gray-600 mt-1">
                전체 프로젝트 예산: {Number(formData.budget_including_vat).toLocaleString('ko-KR')}원
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}