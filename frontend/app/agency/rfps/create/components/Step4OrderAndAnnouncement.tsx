import React from 'react';
import { format } from 'date-fns';
import { CalendarIcon } from '@radix-ui/react-icons';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { RfpFormData } from '@/lib/types';

interface Step4OrderAndAnnouncementProps {
  formData: RfpFormData;
  handleChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => void;
  handleEvaluationStepChange: (index: number, field: 'step_name' | 'start_date' | 'end_date' | 'send_notification', value: string | Date | boolean | null) => void;
  addEvaluationStep: () => void;
  handleSubmit: () => void;
  handlePrevStep: () => void;
  error: string | null;
  isSubmitting: boolean;
}

export const Step4OrderAndAnnouncement: React.FC<Step4OrderAndAnnouncementProps> = ({
  formData,
  handleChange,
  handleEvaluationStepChange,
  addEvaluationStep,
  handleSubmit,
  handlePrevStep,
  error,
  isSubmitting,
}) => {
  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>4단계: 발주 방식 결정 및 공고 설정</CardTitle>
          <CardDescription>RFP의 발주 방식을 선택하고 공고 관련 상세 설정을 진행합니다.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div>
            <Label>발주 방식 <span className="text-red-500">*</span></Label>
            <div className="flex space-x-4 mt-2">
              <label className="flex items-center space-x-2">
                <input
                  type="radio"
                  name="issue_type"
                  value="integrated"
                  checked={formData.issue_type === 'integrated'}
                  onChange={handleChange}
                  className="form-radio"
                />
                <span>통합 발주</span>
              </label>
              <label className="flex items-center space-x-2">
                <input
                  type="radio"
                  name="issue_type"
                  value="separated_by_element"
                  checked={formData.issue_type === 'separated_by_element'}
                  onChange={handleChange}
                  className="form-radio"
                />
                <span>요소별 분리 발주</span>
              </label>
            </div>
          </div>

          <Separator />
          <h3 className="text-lg font-semibold">평가 및 협상 일정</h3>
          <p className="text-sm text-muted-foreground mb-4">각 단계의 정확한 시간은 공고 마감 후 개별 통보됩니다.</p>
          {formData.evaluation_steps.map((stepData: { step_name: string; start_date: Date | null; end_date: Date | null; send_notification: boolean; }, index: number) => (
            <div key={index} className="border p-4 rounded-md bg-gray-50 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor={`step_name-${index}`}>단계 이름</Label>
                <Input
                  id={`step_name-${index}`}
                  value={stepData.step_name}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) => handleEvaluationStepChange(index, 'step_name', e.target.value)}
                  required
                />
              </div>
              <div>
                <Label htmlFor={`step_start_date-${index}`}>시작일</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      variant={"outline"}
                      className={`w-full justify-start text-left font-normal ${
                        !stepData.start_date && "text-muted-foreground"
                      }`}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {stepData.start_date ? format(stepData.start_date, "yyyy-MM-dd") : <span>날짜 선택</span>}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar
                      mode="single"
                      selected={stepData.start_date || undefined}
                      onSelect={(date: Date | undefined) => handleEvaluationStepChange(index, 'start_date', date || null)}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>
              <div>
                <Label htmlFor={`step_end_date-${index}`}>종료일</Label>
                <Popover>
                  <PopoverTrigger asChild>
                    <Button
                      variant={"outline"}
                      className={`w-full justify-start text-left font-normal ${
                        !stepData.end_date && "text-muted-foreground"
                      }`}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {stepData.end_date ? format(stepData.end_date, "yyyy-MM-dd") : <span>날짜 선택</span>}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0">
                    <Calendar
                      mode="single"
                      selected={stepData.end_date || undefined}
                      onSelect={(date: Date | undefined) => handleEvaluationStepChange(index, 'end_date', date || null)}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>
              <div className="flex items-center space-x-2 col-span-1 md:col-span-2">
                <Switch
                  id={`send_notification-${index}`}
                  checked={stepData.send_notification}
                  onCheckedChange={(checked: boolean) => handleEvaluationStepChange(index, 'send_notification', checked)}
                />
                <Label htmlFor={`send_notification-${index}`}>알림 발송</Label>
              </div>
            </div>
          ))}
          <Button 
            variant="outline" 
            onClick={addEvaluationStep}
          >
            평가 단계 추가
          </Button>
        </CardContent>
      </Card>
      <div className="flex justify-between mt-6">
        <Button variant="outline" onClick={handlePrevStep}>이전 단계</Button>
        <Button onClick={handleSubmit} disabled={isSubmitting}>
          {isSubmitting ? 'RFP 생성 중...' : 'RFP 최종 생성'}
        </Button>
      </div>
    </div>
  );
};