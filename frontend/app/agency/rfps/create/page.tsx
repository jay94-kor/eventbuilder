'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import api from '../../../../lib/api';
import { Button } from '@/components/ui/button';
import Step1Form from './components/Step1Form';
import { useRfpForm } from '@/hooks/useRfpForm';
import { RfpFormData } from '@/lib/types';
import { AxiosError } from 'axios';

export default function CreateRfpPage() {
  const router = useRouter();
  const { formData, handleChange, handleSwitchChange, handleDateChange, handleNumericChange } = useRfpForm();
  const [step, setStep] = useState(1);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const validateStep1 = () => {
    const requiredFields: (keyof RfpFormData)[] = ['project_name', 'start_datetime', 'end_datetime', 'location', 'budget_including_vat', 'rfp_description', 'closing_at'];
    
    for (const field of requiredFields) {
      if (!formData[field]) {
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
      start_datetime: formData.start_datetime?.toISOString(),
      end_datetime: formData.end_datetime?.toISOString(),
      preparation_start_datetime: formData.preparation_start_datetime?.toISOString(),
      철수_end_datetime: formData.철수_end_datetime?.toISOString(),
      closing_at: formData.closing_at?.toISOString(),
    };

    try {
      await api.post('/api/rfps', dataToSend);
      router.push('/agency/dashboard');
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      console.error('RFP 생성 실패:', error.response?.data || error.message);
      setError(error.response?.data?.message || 'RFP 생성에 실패했습니다.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="p-8">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold mb-6 text-gray-800">RFP 생성 마법사</h1>
        
        <div className="mb-8">
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div className="bg-blue-600 h-2 rounded-full transition-all duration-300" style={{ width: `${(step / 4) * 100}%` }}></div>
          </div>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <p className="text-red-600 text-sm">{error}</p>
          </div>
        )}

        {step === 1 && <Step1Form 
          formData={formData} 
          handleChange={handleChange}
          handleSwitchChange={handleSwitchChange} 
          handleDateChange={handleDateChange} 
          handleNumericChange={handleNumericChange} 
        />}

        {/* Placeholder for other steps */}
        {[2, 3].includes(step) && (
          <div className="bg-white p-6 rounded-lg shadow-md text-center py-12">
            <h2 className="text-xl font-semibold mb-4 text-gray-800">{step}단계</h2>
            <p className="text-gray-600">다음 업데이트에서 구현될 예정입니다.</p>
          </div>
        )}

        {step === 4 && (
           <div className="bg-white p-6 rounded-lg shadow-md">
             <h2 className="text-xl font-semibold mb-6 text-gray-800 border-b border-gray-200 pb-2">4단계: 최종 확인</h2>
             <div className="text-center py-8">
               <p className="text-gray-600 mb-4">입력하신 내용을 확인 후 최종 생성 버튼을 눌러주세요.</p>
             </div>
           </div>
        )}
        
        <div className="flex justify-between mt-8">
          {step > 1 ? (
            <Button variant="outline" onClick={handlePrevStep}>← 이전 단계</Button>
          ) : (
            <div></div> // Keep space for alignment
          )}
          {step < 4 ? (
            <Button onClick={handleNextStep}>다음 단계 →</Button>
          ) : (
            <Button onClick={handleSubmit} disabled={isLoading} className="bg-green-600 hover:bg-green-700">
              {isLoading ? '생성 중...' : '🎯 RFP 최종 생성'}
            </Button>
          )}
        </div>
      </div>
    </div>
  );
} 