'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import api from '../../../../lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { format } from 'date-fns';
import { CalendarIcon } from '@radix-ui/react-icons';

export default function CreateRfpPage() {
  const router = useRouter();
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    project_name: '',
    start_datetime: null as Date | null,
    end_datetime: null as Date | null,
    preparation_start_datetime: null as Date | null,
    철수_end_datetime: null as Date | null,
    client_name: '',
    client_contact_person: '',
    client_contact_number: '',
    is_client_name_public: true,
    is_budget_public: false,
    is_indoor: true,
    location: '',
    budget_including_vat: null as number | null,
    rfp_description: '',
    closing_at: null as Date | null,
    elements: [] as any[],
    issue_type: 'integrated',
    evaluation_steps: [] as any[],
  });
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { id, value, type } = e.target;
    const checked = (e.target as HTMLInputElement).checked;
    setFormData((prev) => ({
      ...prev,
      [id]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleSwitchChange = (id: string, checked: boolean) => {
    setFormData((prev) => ({
      ...prev,
      [id]: checked,
    }));
  };

  const handleDateChange = (id: string, date: Date | undefined) => {
    setFormData((prev) => ({
      ...prev,
      [id]: date || null,
    }));
  };

  const handleNumericChange = (id: string, value: string) => {
    setFormData((prev) => ({
      ...prev,
      [id]: value === '' ? null : Number(value),
    }));
  };

  const validateStep1 = () => {
    const requiredFields = ['project_name', 'start_datetime', 'end_datetime', 'location', 'budget_including_vat', 'rfp_description', 'closing_at'];
    
    for (const field of requiredFields) {
      if (!formData[field as keyof typeof formData]) {
        setError(`필수 입력 필드를 모두 채워주세요: ${field}`);
        return false;
      }
    }
    
    if (formData.start_datetime && formData.end_datetime && formData.start_datetime >= formData.end_datetime) {
      setError('행사 시작일은 마감일보다 앞서야 합니다.');
      return false;
    }
    
    if (formData.closing_at && formData.start_datetime && formData.closing_at >= formData.start_datetime) {
      setError('공고 마감일은 행사 시작일보다 앞서야 합니다.');
      return false;
    }
    
    return true;
  };

  const handleNextStep = () => {
    if (step === 1 && !validateStep1()) return;
    setError(null);
    setStep(step + 1);
  };

  const handlePrevStep = () => {
    setStep(step - 1);
    setError(null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);

    const dataToSend = {
      ...formData,
      start_datetime: formData.start_datetime ? formData.start_datetime.toISOString() : null,
      end_datetime: formData.end_datetime ? formData.end_datetime.toISOString() : null,
      preparation_start_datetime: formData.preparation_start_datetime ? formData.preparation_start_datetime.toISOString() : null,
      철수_end_datetime: formData.철수_end_datetime ? formData.철수_end_datetime.toISOString() : null,
      closing_at: formData.closing_at ? formData.closing_at.toISOString() : null,
      elements: [],
      issue_type: 'integrated',
      evaluation_steps: [],
    };

    try {
      console.log('RFP 생성 데이터 전송:', dataToSend);
      const response = await api.post('/api/rfps', dataToSend);
      console.log('RFP 생성 성공:', response.data);
      router.push('/agency/dashboard');
    } catch (err: any) {
      console.error('RFP 생성 실패:', err.response?.data || err.message);
      setError(err.response?.data?.message || 'RFP 생성에 실패했습니다.');
    } finally {
      setIsLoading(false);
    }
  };

  const formatDateForDisplay = (date: Date | null) => {
    return date ? format(date, "yyyy-MM-dd") : "날짜 선택";
  };

  return (
    <div className="p-8">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold mb-6 text-gray-800">RFP 생성 마법사</h1>
        
        <div className="mb-8">
          <div className="flex items-center justify-between mb-2">
            <span className="text-sm font-medium text-gray-700">진행률: {step}/4 단계</span>
            <span className="text-sm text-gray-500">{Math.round((step / 4) * 100)}% 완료</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div className="bg-blue-600 h-2 rounded-full transition-all duration-300" style={{ width: `${(step / 4) * 100}%` }}></div>
          </div>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <p className="text-red-600 text-sm">{error}</p>
          </div>
        )}

        {step === 1 && (
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-6 text-gray-800 border-b border-gray-200 pb-2">
              1단계: 프로젝트 공통 정보 입력
            </h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="md:col-span-2">
                <Label htmlFor="project_name">행사명 <span className="text-red-500">*</span></Label>
                <Input id="project_name" value={formData.project_name} onChange={handleChange} placeholder="예: 2024 글로벌 IT 컨퍼런스" required />
              </div>
              
              <div>
                <Label htmlFor="start_datetime">행사 시작일 <span className="text-red-500">*</span></Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button variant={"outline"} className={`w-full justify-start text-left font-normal ${!formData.start_datetime && "text-muted-foreground"}`}>
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {formatDateForDisplay(formData.start_datetime)}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar mode="single" selected={formData.start_datetime || undefined} onSelect={(date) => handleDateChange('start_datetime', date)} initialFocus />
                  </PopoverContent>
                </Popover>
              </div>

              <div>
                <Label htmlFor="end_datetime">행사 마감일 <span className="text-red-500">*</span></Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button variant={"outline"} className={`w-full justify-start text-left font-normal ${!formData.end_datetime && "text-muted-foreground"}`}>
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {formatDateForDisplay(formData.end_datetime)}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar mode="single" selected={formData.end_datetime || undefined} onSelect={(date) => handleDateChange('end_datetime', date)} initialFocus />
                  </PopoverContent>
                </Popover>
              </div>

              <div>
                <Label htmlFor="preparation_start_datetime">행사 준비 시작일</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      variant={"outline"}
                      className={`w-full justify-start text-left font-normal ${
                        !formData.preparation_start_datetime && "text-muted-foreground"
                      }`}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {formatDateForDisplay(formData.preparation_start_datetime)}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar
                      mode="single"
                      selected={formData.preparation_start_datetime || undefined}
                      onSelect={(date) => handleDateChange('preparation_start_datetime', date)}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>

              <div>
                <Label htmlFor="철수_end_datetime">행사 철수 마감일</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      variant={"outline"}
                      className={`w-full justify-start text-left font-normal ${
                        !formData.철수_end_datetime && "text-muted-foreground"
                      }`}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {formatDateForDisplay(formData.철수_end_datetime)}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar
                      mode="single"
                      selected={formData.철수_end_datetime || undefined}
                      onSelect={(date) => handleDateChange('철수_end_datetime', date)}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>

              <div className="md:col-span-2">
                <Label htmlFor="location">행사 장소 <span className="text-red-500">*</span></Label>
                <Input id="location" value={formData.location} onChange={handleChange} placeholder="예: 서울 코엑스 컨벤션센터 홀 A" required />
              </div>

              <div className="flex items-center space-x-2">
                <Switch id="is_indoor" checked={formData.is_indoor} onCheckedChange={(checked) => handleSwitchChange('is_indoor', checked)} />
                <Label htmlFor="is_indoor">실내 행사</Label>
              </div>

              <div>
                <Label htmlFor="client_name">클라이언트명</Label>
                <div className="space-y-2">
                  <Input id="client_name" value={formData.client_name} onChange={handleChange} placeholder="예: (주)테크노베이션" />
                  <div className="flex items-center space-x-2">
                    <Switch id="is_client_name_public" checked={formData.is_client_name_public} onCheckedChange={(checked) => handleSwitchChange('is_client_name_public', checked)} />
                    <Label htmlFor="is_client_name_public" className="text-sm">클라이언트명 공개</Label>
                  </div>
                </div>
              </div>

              <div>
                <Label htmlFor="client_contact_person">클라이언트 담당자</Label>
                <Input id="client_contact_person" value={formData.client_contact_person} onChange={handleChange} placeholder="예: 김철수 부장" />
              </div>

              <div>
                <Label htmlFor="client_contact_number">클라이언트 연락처</Label>
                <Input id="client_contact_number" value={formData.client_contact_number} onChange={handleChange} placeholder="예: 02-1234-5678" />
              </div>

              <div>
                <Label htmlFor="budget_including_vat">총 예산 (VAT 포함) <span className="text-red-500">*</span></Label>
                <div className="space-y-2">
                  <Input id="budget_including_vat" type="number" value={formData.budget_including_vat === null ? '' : formData.budget_including_vat} onChange={(e) => handleNumericChange('budget_including_vat', e.target.value)} placeholder="예: 50000000" required />
                  <div className="flex items-center space-x-2">
                    <Switch id="is_budget_public" checked={formData.is_budget_public} onCheckedChange={(checked) => handleSwitchChange('is_budget_public', checked)} />
                    <Label htmlFor="is_budget_public" className="text-sm">예산 공개</Label>
                  </div>
                </div>
              </div>

              <div className="md:col-span-2">
                <Label htmlFor="rfp_description">RFP 설명 <span className="text-red-500">*</span></Label>
                <Textarea id="rfp_description" value={formData.rfp_description} onChange={handleChange} placeholder="행사에 대한 자세한 설명을 입력해주세요..." rows={4} required />
              </div>

              <div>
                <Label htmlFor="closing_at">공고 마감일 <span className="text-red-500">*</span></Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button variant={"outline"} className={`w-full justify-start text-left font-normal ${!formData.closing_at && "text-muted-foreground"}`}>
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {formatDateForDisplay(formData.closing_at)}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar mode="single" selected={formData.closing_at || undefined} onSelect={(date) => handleDateChange('closing_at', date)} initialFocus />
                  </PopoverContent>
                </Popover>
              </div>
            </div>

            <div className="flex justify-end mt-8">
              <Button onClick={handleNextStep} type="button" className="px-8">다음 단계 →</Button>
            </div>
          </div>
        )}

        {step === 2 && (
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-6 text-gray-800 border-b border-gray-200 pb-2">
              2단계: 행사 요소 선택 <span className="text-sm text-gray-500">(다음 업데이트에서 구현)</span>
            </h2>
            <div className="text-center py-12">
              <p className="text-gray-600 mb-4">이곳에 행사 요소 선택 UI가 들어갑니다.</p>
              <p className="text-sm text-gray-500">관리자가 등록한 요소 정의들을 선택하는 단계입니다.</p>
            </div>
            <div className="flex justify-between mt-6">
              <Button variant="outline" onClick={handlePrevStep}>← 이전 단계</Button>
              <Button onClick={handleNextStep}>다음 단계 →</Button>
            </div>
          </div>
        )}

        {step === 3 && (
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-6 text-gray-800 border-b border-gray-200 pb-2">
              3단계: 선택된 요소 세부 정의 <span className="text-sm text-gray-500">(다음 업데이트에서 구현)</span>
            </h2>
            <div className="text-center py-12">
              <p className="text-gray-600 mb-4">이곳에 선택된 요소들의 세부 정의 UI가 들어갑니다.</p>
              <p className="text-sm text-gray-500">각 요소별 상세 내용과 예산 배분을 설정하는 단계입니다.</p>
            </div>
            <div className="flex justify-between mt-6">
              <Button variant="outline" onClick={handlePrevStep}>← 이전 단계</Button>
              <Button onClick={handleNextStep}>다음 단계 →</Button>
            </div>
          </div>
        )}

        {step === 4 && (
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-6 text-gray-800 border-b border-gray-200 pb-2">4단계: 발주 방식 결정 및 최종 확인</h2>
            <div className="space-y-6">
              <div className="bg-gray-50 p-4 rounded-md">
                <h3 className="font-medium mb-2">입력하신 정보 요약</h3>
                <ul className="text-sm space-y-1 text-gray-600">
                  <li><strong>행사명:</strong> {formData.project_name}</li>
                  <li><strong>행사 일정:</strong> {formatDateForDisplay(formData.start_datetime)} ~ {formatDateForDisplay(formData.end_datetime)}</li>
                  <li><strong>장소:</strong> {formData.location}</li>
                  <li><strong>예산:</strong> {formData.budget_including_vat?.toLocaleString()}원</li>
                  <li><strong>공고 마감:</strong> {formatDateForDisplay(formData.closing_at)}</li>
                </ul>
              </div>
            </div>
            
            <div className="flex justify-between mt-8">
              <Button variant="outline" onClick={handlePrevStep}>← 이전 단계</Button>
              <Button onClick={handleSubmit} disabled={isLoading} className="px-8 bg-green-600 hover:bg-green-700">
                {isLoading ? '생성 중...' : '🎯 RFP 최종 생성'}
              </Button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
} 