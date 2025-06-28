'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import api from '../../../../lib/api';
import { Button } from '@/components/ui/button';
import Step1Form from './components/Step1Form';
import Step2ElementSelection from './components/Step2ElementSelection';
import Step3ElementDetails from './components/Step3ElementDetails';
import { Step4OrderAndAnnouncement } from './components/Step4OrderAndAnnouncement';
import { useRfpForm } from '@/hooks/useRfpForm';
import { RfpFormData, RfpCreateResponse } from '@/lib/types';
import { AxiosError } from 'axios';

export default function CreateRfpPage() {
  const router = useRouter();
  const { 
    formData, 
    setFormData, 
    error, 
    setError, 
    handleChange,
    handleSwitchChange,
    handleDateChange,
    handleNumericChange,
    handleElementUpdate,
    handleEvaluationStepChange,
    addEvaluationStep
  } = useRfpForm();
  const [currentStep, setCurrentStep] = useState(1);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleElementsChange = (elements: RfpFormData['elements']) => {
    setFormData(prev => ({
      ...prev,
      elements
    }));
  };

  const validateStep1 = () => {
    const requiredFields: (keyof RfpFormData)[] = ['project_name', 'start_datetime', 'end_datetime', 'client_name', 'client_contact_person', 'client_contact_number', 'location', 'budget_including_vat', 'closing_at'];
    
    for (const field of requiredFields) {
      if (!formData[field]) {
        setError(`${field} 필드는 필수입니다.`);
        return false;
      }
    }

    // 날짜 검증
    const startDate = new Date(formData.start_datetime);
    const endDate = new Date(formData.end_datetime);
    const closingDate = new Date(formData.closing_at);
    const today = new Date();

    if (startDate >= endDate) {
      setError('시작 날짜는 종료 날짜보다 이전이어야 합니다.');
      return false;
    }

    if (closingDate <= today) {
      setError('공고 마감일은 현재보다 미래여야 합니다.');
      return false;
    }

    return true;
  };

  const validateStep2 = () => {
    if (formData.elements.length === 0) {
      setError('최소 하나의 요소를 선택해주세요.');
      return false;
    }
    return true;
  };

  const handleSubmit = async () => {
    if (!validateStep1() || !validateStep2()) {
      return;
    }

    setIsSubmitting(true);
    setError(null);

    try {
      // 날짜를 ISO 형식으로 변환
      const submitData = {
        ...formData,
        start_datetime: new Date(formData.start_datetime).toISOString(),
        end_datetime: new Date(formData.end_datetime).toISOString(),
        closing_at: new Date(formData.closing_at).toISOString(),
        elements: formData.elements.map(element => ({
          ...element,
          prepayment_due_date: element.prepayment_due_date || new Date().toISOString().split('T')[0],
          balance_due_date: element.balance_due_date || new Date().toISOString().split('T')[0],
        }))
      };

      console.log('전송할 데이터:', submitData);

      const response = await api.post<RfpCreateResponse>('/api/rfps', submitData);
      
      console.log('RFP 생성 성공:', response.data);
      
      // 성공 시 RFP 목록 페이지로 이동
      router.push('/agency/rfps');
    } catch (err) {
      const error = err as AxiosError<{ message: string; errors?: Record<string, string[]> }>;
      console.error('RFP 생성 실패:', error.response?.data || error.message);
      
      if (error.response?.data?.errors) {
        // Laravel 검증 오류 처리
        const errors = error.response.data.errors;
        const firstError = Object.values(errors)[0]?.[0];
        setError(firstError || error.response.data.message || 'RFP 생성에 실패했습니다.');
      } else {
        setError(error.response?.data?.message || 'RFP 생성에 실패했습니다.');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const nextStep = () => {
    if (currentStep === 1 && !validateStep1()) {
      return;
    }
    if (currentStep === 2 && !validateStep2()) {
      return;
    }
    setError(null);
    setCurrentStep(currentStep + 1);
  };

  const prevStep = () => {
    setError(null);
    setCurrentStep(currentStep - 1);
  };

  const renderStep = () => {
    switch (currentStep) {
      case 1:
        return (
          <Step1Form
            formData={formData}
            handleChange={handleChange}
            handleSwitchChange={handleSwitchChange}
            handleDateChange={handleDateChange}
            handleNumericChange={handleNumericChange}
          />
        );
      case 2:
        return (
          <Step2ElementSelection
            formData={formData}
            onElementsChange={handleElementsChange}
          />
        );
      case 3:
        return (
          <Step3ElementDetails
            formData={formData}
            onElementUpdate={handleElementUpdate}
          />
        );
      case 4:
        return (
          <Step4OrderAndAnnouncement
            formData={formData}
            handleChange={handleChange}
            handleEvaluationStepChange={handleEvaluationStepChange}
            addEvaluationStep={addEvaluationStep}
            handleSubmit={handleSubmit}
            handlePrevStep={prevStep}
            error={error}
            isSubmitting={isSubmitting}
          />
        );
      default:
        return null;
    }
  };

  return (
    <div className="p-8">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-800 mb-2">RFP 생성</h1>
          <p className="text-gray-600">새로운 RFP를 생성합니다.</p>
        </div>

        {/* 진행 단계 표시 */}
        <div className="mb-8">
          <div className="flex items-center space-x-4">
            {[1, 2, 3, 4].map((stepNumber) => (
              <div key={stepNumber} className="flex items-center">
                <div
                  className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${
                    currentStep >= stepNumber
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 text-gray-600'
                  }`}
                >
                  {stepNumber}
                </div>
                {stepNumber < 4 && (
                  <div
                    className={`w-16 h-1 mx-2 ${
                      currentStep > stepNumber ? 'bg-blue-600' : 'bg-gray-200'
                    }`}
                  />
                )}
              </div>
            ))}
          </div>
          <div className="flex justify-between mt-2 text-sm text-gray-600">
            <span>기본 정보</span>
            <span>요소 선택</span>
            <span>요소 상세</span>
            <span>발주 및 공고</span>
          </div>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <p className="text-red-600 text-sm">{error}</p>
          </div>
        )}

        {/* 단계별 폼 */}
        {renderStep()}

        {/* 네비게이션 버튼 */}
        <div className="flex justify-between mt-8">
          <Button
            variant="outline"
            onClick={prevStep}
            disabled={currentStep === 1}
          >
            이전
          </Button>
          
          <div className="space-x-4">
            {currentStep < 4 ? (
              <Button onClick={nextStep}>
                다음
              </Button>
            ) : (
              <Button 
                onClick={handleSubmit}
                disabled={isSubmitting}
                className="bg-blue-600 hover:bg-blue-700"
              >
                {isSubmitting ? '생성 중...' : 'RFP 생성'}
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
} 