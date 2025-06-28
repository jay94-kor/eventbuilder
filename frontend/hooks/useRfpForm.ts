import { useState } from 'react';
import { 
  RfpFormData, 
  ElementDefinition, 
  RfpElementFormData, 
  UseRfpFormReturn 
} from '../lib/types';
import useAuthStore from '../lib/stores/authStore';

export const useRfpForm = (): UseRfpFormReturn => {
  const { user } = useAuthStore();
  const [step, setStep] = useState(1);
  const [error, setError] = useState<string | null>(null);

  const [formData, setFormData] = useState<RfpFormData>({
    project_name: '',
    start_datetime: null,
    end_datetime: null,
    preparation_start_datetime: null,
    철수_end_datetime: null,
    client_name: '',
    client_contact_person: '',
    client_contact_number: '',
    is_client_name_public: true,
    is_budget_public: false,
    is_indoor: true,
    location: '',
    budget_including_vat: null,
    rfp_description: '',
    closing_at: null,
    main_agency_contact_user_id: user?.id || null,
    sub_agency_contact_user_id: null,
    selected_element_definitions: [],
    elements: [],
    issue_type: 'integrated',
    evaluation_steps: [],
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { id, value, type, checked } = e.target as HTMLInputElement;
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
      [id]: date || null,
    }));
  };

  const handleNumericChange = (id: string, value: string) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      [id]: value === '' ? null : Number(value),
    }));
  };

  const handleElementSelect = (element: ElementDefinition, isChecked: boolean) => {
    setFormData((prev: RfpFormData) => {
      const newSelectedElements = isChecked
        ? [...prev.selected_element_definitions, element]
        : prev.selected_element_definitions.filter((e: ElementDefinition) => e.id !== element.id);
      
      const newElementsFormData = newSelectedElements.map((selectedElem: ElementDefinition) => {
        const existingElementData = prev.elements.find((e: RfpElementFormData) => e.element_id === selectedElem.id);
        return existingElementData || {
          element_id: selectedElem.id,
          element_type: selectedElem.element_type,
          details: {},
          allocated_budget: null,
          prepayment_ratio: null,
          prepayment_due_date: null,
          balance_ratio: null,
          balance_due_date: null,
        };
      });

      return {
        ...prev,
        selected_element_definitions: newSelectedElements,
        elements: newElementsFormData,
      };
    });
  };

  const handleElementDetailsChange = (
    elementId: string, 
    field: keyof RfpElementFormData, 
    value: any
  ) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      elements: prev.elements.map((el: RfpElementFormData) => 
        el.element_id === elementId 
          ? { ...el, [field]: value } 
          : el
      )
    }));
  };

  const handleElementSpecificDetailsChange = (
    elementId: string, 
    detailField: string, 
    value: string
  ) => {
    setFormData((prev: RfpFormData) => ({
      ...prev,
      elements: prev.elements.map((el: RfpElementFormData) => 
        el.element_id === elementId 
          ? { ...el, details: { ...el.details, [detailField]: value } } 
          : el
      )
    }));
  };

  const handleEvaluationStepChange = (
    index: number,
    field: 'step_name' | 'start_date' | 'end_date' | 'send_notification',
    value: string | Date | boolean | null
  ) => {
    setFormData((prev: RfpFormData) => {
      const newSteps = [...prev.evaluation_steps];
      if (field === 'start_date' || field === 'end_date') {
        newSteps[index][field] = value as Date | null;
      } else if (field === 'send_notification') {
        newSteps[index][field] = value as boolean;
      } else { // step_name
        newSteps[index][field] = value as string;
      }
      return { ...prev, evaluation_steps: newSteps };
    });
  };

  const addEvaluationStep = () => {
    setFormData((prev: RfpFormData) => ({ 
      ...prev, 
      evaluation_steps: [...prev.evaluation_steps, { step_name: '', start_date: null, end_date: null, send_notification: true }] 
    }));
  };

  return {
    step,
    setStep,
    formData,
    setFormData,
    error,
    setError,
    handleChange,
    handleSwitchChange,
    handleDateChange,
    handleNumericChange,
    handleElementSelect,
    handleElementDetailsChange,
    handleElementSpecificDetailsChange,
    handleEvaluationStepChange,
    addEvaluationStep,
  };
};