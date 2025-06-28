import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/api';
import { ElementDefinition, RfpFormData, User, ApiResponse, RfpElementFormData, EvaluationStepFormData } from '../lib/types';
import { useRouter } from 'next/navigation';
import { AxiosError } from 'axios';

export const useRfpApi = () => {
  const queryClient = useQueryClient();
  const router = useRouter();

  // 대행사 멤버 목록 조회 (담당자 선택용)
  const { data: agencyMembers, isLoading: isLoadingMembers } = useQuery<User[]>({
    queryKey: ['agencyMembers'],
    queryFn: async () => {
      const response = await api.get<ApiResponse<{ members: User[] }>>('/api/agency-members');
      return response.data.data?.members || [];
    },
  });

  // 요소 정의 목록 조회 (2단계에서 사용)
  const { data: elementDefinitions, isLoading: isLoadingElements, isError: isElementError } = useQuery<ElementDefinition[]>({
    queryKey: ['elementDefinitions'],
    queryFn: async () => {
      const response = await api.get<ApiResponse<ElementDefinition[]>>('/api/element-definitions');
      return response.data.data || [];
    },
  });

  // RFP 생성 뮤테이션
  const createRfpMutation = useMutation({
    mutationFn: async (formData: RfpFormData) => {
      const dataToSend = {
        project_name: formData.project_name,
        start_datetime: formData.start_datetime?.toISOString() || null,
        end_datetime: formData.end_datetime?.toISOString() || null,
        preparation_start_datetime: formData.preparation_start_datetime?.toISOString() || null,
        철수_end_datetime: formData.철수_end_datetime?.toISOString() || null,
        client_name: formData.client_name,
        client_contact_person: formData.client_contact_person,
        client_contact_number: formData.client_contact_number,
        is_client_name_public: formData.is_client_name_public,
        is_budget_public: formData.is_budget_public,
        is_indoor: formData.is_indoor,
        location: formData.location,
        budget_including_vat: formData.budget_including_vat,
        rfp_description: formData.rfp_description,
        closing_at: formData.closing_at?.toISOString() || null,
        main_agency_contact_user_id: formData.main_agency_contact_user_id,
        sub_agency_contact_user_id: formData.sub_agency_contact_user_id,
        elements: formData.elements.map((el: RfpElementFormData) => ({
          element_type: el.element_type,
          details: el.details,
          allocated_budget: el.allocated_budget,
          prepayment_ratio: el.prepayment_ratio,
          prepayment_due_date: el.prepayment_due_date?.toISOString().split('T')[0] || null,
          balance_ratio: el.balance_ratio,
          balance_due_date: el.balance_due_date?.toISOString().split('T')[0] || null,
        })),
        issue_type: formData.issue_type,
        evaluation_steps: formData.evaluation_steps.map((step: EvaluationStepFormData) => ({
          step_name: step.step_name,
          start_date: step.start_date?.toISOString().split('T')[0] || null,
          end_date: step.end_date?.toISOString().split('T')[0] || null,
          send_notification: step.send_notification,
        })),
      };
      return api.post('/api/rfps', dataToSend);
    },
    onSuccess: (data) => {
      console.log('RFP 생성 성공:', data.data);
      queryClient.invalidateQueries({ queryKey: ['rfpList'] });
      router.push('/agency/rfps');
    },
    onError: (error: AxiosError<ApiResponse>) => {
      console.error('RFP 생성 실패:', error.response?.data || error.message);
      throw new Error(error.response?.data?.message || 'RFP 생성에 실패했습니다.');
    },
  });

  return {
    agencyMembers,
    isLoadingMembers,
    elementDefinitions,
    isLoadingElements,
    isElementError,
    createRfpMutation,
  };
};