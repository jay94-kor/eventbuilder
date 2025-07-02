// frontend/lib/types.ts

// =================================
//  Core Models
// =================================

export type UserType = 'admin' | 'agency_member' | 'vendor_member';

export interface User {
  id: string;
  name: string;
  email: string;
  user_type: string;
}

export interface Agency {
  id: string;
  name: string;
  business_registration_number?: string;
  address?: string;
  master_user_id: string;
  subscription_status?: string;
  subscription_end_date?: string;
  created_at?: string;
  updated_at?: string;
  masterUser?: User;
}

export interface Vendor {
  id: string;
  name: string;
  business_registration_number?: string;
  address?: string;
  master_user_id: string;
  specialties?: string[];
  created_at?: string;
  updated_at?: string;
  masterUser?: User;
}

<<<<<<< Updated upstream
=======
export interface Category {
  id: string;
  name: string;
  display_name: string;
  description?: string;
  icon?: string;
  color?: string;
  sort_order: number;
  complexity_level?: 'basic' | 'intermediate' | 'advanced';
  group?: 'hardware_equipment' | 'content_direction' | 'personnel_operation' | 'planning_support_services' | 'miscellaneous';
  element_definitions?: ElementDefinition[];
  created_at?: string;
  updated_at?: string;
}

// =================================
//  🆕 Dynamic Spec System Types
// =================================

export interface SpecFieldTemplate {
  name: string;                         // 스펙명 (예: "가로", "화질", "개수")
  unit?: string;                        // 스펙 단위 (예: "m", "대", "W", null)
  type: 'number' | 'text' | 'select' | 'boolean';
  default_value?: string | number | boolean; // 기본값
  options?: string[];                   // select 타입일 때 선택 옵션들
  required?: boolean;                   // 필수 입력 여부
  validation?: {                        // 검증 규칙
    min?: number;
    max?: number;
    pattern?: string;
  };
}

export interface SpecField {
  id: string;                           // 필드 고유 ID
  name: string;                         // 스펙명 (예: "가로", "화질", "개수")
  unit?: string;                        // 스펙 단위 (예: "m", "대", "W", null)
  value: string | number | boolean;     // 스펙 입력값
  type: 'number' | 'text' | 'select' | 'boolean';
  options?: string[];                   // select 타입일 때 선택 옵션들
  required?: boolean;                   // 필수 입력 여부
  validation?: {                        // 검증 규칙
    min?: number;
    max?: number;
    pattern?: string;
  };
}

export interface SpecVariant {
  id: string;                           // UUID
  name: string;                         // "소형 버전", "고출력 버전" 등
  quantity: number;                     // 이 변형의 수량
  modified_fields: string[];            // 변경된 스펙 필드 ID들
  spec_values: Record<string, any>;     // 변경된 스펙 값들
  notes?: string;                       // 변형별 특별 요구사항
}

>>>>>>> Stashed changes
export interface ElementDefinition {
  id: string;
  element_type: string;
  display_name: string;
  description?: string;
  category?: string;
  is_active: boolean;
  input_schema?: Record<string, unknown>;
  default_details_template?: Record<string, unknown>;
  recommended_elements?: string[];
  created_at?: string;
  updated_at?: string;
  
  // 🆕 동적 스펙 템플릿 정의
  default_spec_template?: SpecFieldTemplate[];
  quantity_config?: {
    unit: string;                        // "대", "개", "세트", "명" 등
    min: number;
    max: number;
    typical: number;
    allow_variants: boolean;             // 변형 허용 여부
  };
  variant_rules?: {
    allowed_fields: string[];            // 변형 가능한 필드 ID들
    max_variants: number;                // 최대 변형 개수
    require_name: boolean;               // 변형명 필수 여부
  };
}

// =================================
//  RFP Related
// =================================

export interface Project {
  id: string;
  project_name: string;
  start_datetime: string;
  end_datetime: string;
  preparation_start_datetime?: string | null;
  철수_end_datetime?: string | null;
  client_name: string;
  client_contact_person: string;
  client_contact_number: string;
  main_agency_contact_user_id: string;
  sub_agency_contact_user_id?: string | null;
  agency_id: string;
  is_indoor: boolean;
  location: string;
  budget_including_vat: string;
  created_at: string;
  updated_at: string;
}

export type RfpStatus = 'draft' | 'approval_pending' | 'approved' | 'rejected' | 'published' | 'closed';
export type IssueType = 'integrated' | 'separated_by_element' | 'separated_by_group';

export interface RfpElement {
  id: string;
  rfp_id: string;
  element_type: string;
  details: Record<string, any>;
  allocated_budget: string;
  prepayment_ratio: string;
  prepayment_due_date: string;
  balance_ratio: string;
  balance_due_date: string;
  parent_rfp_element_id?: string | null;
  created_at: string;
  updated_at: string;
}

export interface Rfp {
  id: string;
  project_id: string;
  current_status: string;
  created_by_user_id: string;
  agency_id: string;
  issue_type: string;
  rfp_description: string;
  closing_at: string;
  published_at?: string | null;
  created_at: string;
  updated_at: string;
  is_client_name_public: boolean;
  is_budget_public: boolean;
  project?: Project;
  elements?: RfpElement[];
}

// =================================
//  Announcement Related
// =================================

export type AnnouncementStatus = 'open' | 'closed' | 'awarded';
export type ChannelType = 'public' | 'agency_private';

export interface EvaluationCriteria {
  price_weight: number;
  portfolio_weight: number;
  additional_weight: number;
  price_deduction_rate: number;
  price_rank_deduction_points: number[];
}

export interface Announcement {
  id: string;
  rfp_id: string;
  title: string;
  description?: string;
  estimated_price?: number;
  closing_at: string;
  channel_type: ChannelType;
  contact_info_private: boolean;
  status: AnnouncementStatus;
  evaluation_criteria?: EvaluationCriteria;
  created_at?: string;
  updated_at?: string;
  rfp?: Rfp;
  agency?: Agency;
}

// =================================
//  Proposal & Contract Related
// =================================

export type ProposalStatus = 'submitted' | 'under_review' | 'awarded' | 'rejected';

export interface Proposal {
  id: string;
  announcement_id: string;
  vendor_id: string;
  proposed_price: number;
  proposal_text?: string;
  status: ProposalStatus;
  reserve_rank?: number;
  submitted_at?: string;
  created_at?: string;
  updated_at?: string;
  announcement?: Announcement;
  vendor?: Vendor;
}

export type PaymentStatus = 'pending' | 'prepayment_paid' | 'balance_paid' | 'all_paid';

export interface Contract {
  id: string;
  proposal_id: string;
  final_price: number;
  payment_status: PaymentStatus;
  contract_signed_at?: string;
  created_at?: string;
  updated_at?: string;
  proposal?: Proposal;
}

// =================================
//  Schedule Related
// =================================

export type ScheduleStatus = 'planned' | 'ongoing' | 'completed' | 'cancelled';
export type ScheduleType = 
  | 'meeting' | 'delivery' | 'installation' | 'dismantling' | 'rehearsal'
  | 'event_execution' | 'setup' | 'testing' | 'load_in' | 'load_out'
  | 'storage' | 'breakdown' | 'cleaning' | 'training' | 'briefing'
  | 'pickup' | 'transportation' | 'site_visit' | 'concept_meeting'
  | 'technical_rehearsal' | 'dress_rehearsal' | 'final_inspection' | 'wrap_up';

export interface Schedule {
  id: string;
  schedulable_type: string;
  schedulable_id: string;
  title: string;
  description?: string;
  start_datetime: string;
  end_datetime: string;
  location?: string;
  status: ScheduleStatus;
  type?: ScheduleType;
  created_at?: string;
  updated_at?: string;
}

export interface ScheduleAttachment {
  id: string;
  schedule_id: string;
  file_name: string;
  file_path: string;
  file_size: number;
  mime_type: string;
  uploaded_by_user_id: string;
  created_at?: string;
  updated_at?: string;
}

// =================================
//  Evaluation Related
// =================================

export interface Evaluation {
  id: string;
  proposal_id: string;
  evaluator_user_id: string;
  price_score?: number;
  portfolio_score?: number;
  additional_score?: number;
  total_score?: number;
  evaluation_comment?: string;
  submitted_at?: string;
  created_at?: string;
  updated_at?: string;
  proposal?: Proposal;
  evaluator?: User;
}

export interface AnnouncementEvaluator {
  id: string;
  announcement_id: string;
  evaluator_user_id: string;
  assigned_at?: string;
  created_at?: string;
  updated_at?: string;
  announcement?: Announcement;
  evaluator?: User;
}

// =================================
//  Form Related
// =================================

// 🔄 기존 RfpElementFormData 완전 재설계
export interface RfpElementFormData {
  element_id: string;
  element_type: string;
  
  // 🆕 수량 관리
  total_quantity: number;               // 총 수량
  base_quantity: number;                // 기본 스펙 적용 수량
  use_variants: boolean;                // 스펙 변형 사용 여부
  
  // 🆕 동적 스펙 시스템
  spec_fields: SpecField[];             // 기본 스펙 필드들
  spec_variants: SpecVariant[];         // 스펙 변형들
  
  // 기존 필드들 (하위 호환성)
  details: Record<string, unknown>;     // 기존 details → spec_fields로 변환
  special_requirements: string;
  allocated_budget: number | null;
  prepayment_ratio: number | null;
  prepayment_due_date: Date | null;
  balance_ratio: number | null;
  balance_due_date: Date | null;
}

export interface EvaluationStepFormData {
  step_name: string;
  start_date: Date | null;
  end_date: Date | null;
  send_notification: boolean;
}

export interface RfpFormData {
  project_name: string;
  start_datetime: string;
  end_datetime: string;
  client_name: string;
  client_contact_person: string;
  client_contact_number: string;
  is_indoor: boolean;
  location: string;
  budget_including_vat: number;
  issue_type: 'integrated' | 'separated_by_element' | 'separated_by_group';
  rfp_description: string;
  closing_at: string;
  elements: Array<{
    element_type: string;
    details: Record<string, any>;
    allocated_budget: number;
    prepayment_ratio: number;
    prepayment_due_date: string;
    balance_ratio: number;
    balance_due_date: string;
  }>;
  evaluation_steps: EvaluationStepFormData[];
}

// =================================
//  API Related
// =================================

export interface ApiResponse<T> {
  message: string;
  data?: T;
}

export interface PaginatedResponse<T = unknown> {
  data: T[];
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface LoginResponse {
  user: User;
  token: string;
  message: string;
}

export interface RfpListResponse {
  message: string;
  rfps: {
    current_page: number;
    data: Rfp[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
  };
}

export interface RfpCreateResponse {
  message: string;
  rfp: Rfp;
}

// =================================
//  UI Related
// =================================

export interface SelectOption {
  value: string;
  label: string;
}

export interface TableColumn<T = unknown> {
  key: keyof T;
  title: string;
  render?: (value: unknown, record: T) => React.ReactNode;
}

// =================================
//  Hook Related
// =================================

export interface UseRfpFormActions {
  handleChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => void;
  handleSwitchChange: (id: string, checked: boolean) => void;
  handleDateChange: (id: string, date: Date | undefined) => void;
  handleNumericChange: (id: string, value: string) => void;
  handleElementSelect: (element: ElementDefinition, isChecked: boolean) => void;
  handleElementDetailsChange: (elementId: string, field: keyof RfpElementFormData, value: unknown) => void;
  handleElementSpecificDetailsChange: (elementId: string, detailField: string, value: string) => void;
  handleEvaluationStepChange: (
    index: number,
    field: 'step_name' | 'start_date' | 'end_date' | 'send_notification',
    value: string | Date | boolean | null
  ) => void;
  addEvaluationStep: () => void;
}

export interface UseRfpFormReturn extends UseRfpFormActions {
  step: number;
  setStep: (step: number) => void;
  formData: RfpFormData;
  setFormData: React.Dispatch<React.SetStateAction<RfpFormData>>;
  error: string | null;
  setError: (error: string | null) => void;
}

// =================================
//  Auth Store Related
// =================================

export interface AuthStore {
  user: User | null;
  token: string | null;
  isLoggedIn: boolean;
  login: (user: User, token: string) => void;
  logout: () => void;
  updateUser: (user: User) => void;
} 