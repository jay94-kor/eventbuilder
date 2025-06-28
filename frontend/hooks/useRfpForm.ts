import { useState } from 'react';
import { RfpFormData, EvaluationStepFormData } from '../lib/types';
import useAuthStore from '../lib/stores/authStore';

export const useRfpForm = () => {
  const { user } = useAuthStore();
  const [error, setError] = useState<string | null>(null);

  const [formData, setFormData] = useState<RfpFormData>({
    project_name: '',
    start_datetime: '',
    end_datetime: '',
    client_name: '',
    client_contact_person: '',
    client_contact_number: '',
    is_indoor: true,
    location: '',
    budget_including_vat: 0,
    issue_type: 'integrated',
    rfp_description: '',
    closing_at: '',
    elements: [{
      element_type: 'stage',
      details: { description: '기본 무대 설치' },
      allocated_budget: 1000000,
      prepayment_ratio: 0.3,
      prepayment_due_date: '',
      balance_ratio: 0.7,
      balance_due_date: '',
    }],
    evaluation_steps: [{
      step_name: '서류 심사',
      start_date: null,
      end_date: null,
      send_notification: true,
    }],
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { id, value, type } = e.target;
    const checked = (e.target as HTMLInputElement).checked;
    
    setFormData((prev: RfpFormData) => ({
      ...prev,
      [id]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleSwitchChange = (id: string, checked: boolean) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      [id]: checked,
    }));
  };

  const handleDateChange = (id: string, date: Date | undefined) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      [id]: date ? date.toISOString().split('T')[0] : '',
    }));
  };

  const handleNumericChange = (id: string, value: string) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      [id]: value === '' ? 0 : Number(value),
    }));
  };

  const handleElementUpdate = (elementIndex: number, field: string, value: any) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      elements: prev.elements.map((element, index) => 
        index === elementIndex 
          ? { ...element, [field]: value }
          : element
      )
    }));
  };

  const handleEvaluationStepChange = (
    index: number,
    field: 'step_name' | 'start_date' | 'end_date' | 'send_notification',
    value: string | Date | boolean | null
  ) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      evaluation_steps: prev.evaluation_steps.map((step, i) =>
        i === index ? { ...step, [field]: value } : step
      )
    }));
  };

  const addEvaluationStep = () => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      evaluation_steps: [
        ...prev.evaluation_steps,
        {
          step_name: '',
          start_date: null,
          end_date: null,
          send_notification: true,
        }
      ]
    }));
  };

  return {
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
    addEvaluationStep,
  };
};