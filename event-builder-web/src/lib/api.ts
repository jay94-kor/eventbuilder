import { User } from '@/types/auth'; // User 타입을 @/types/auth에서 임포트

interface ApiResponse<T = unknown> {
  success: boolean;
  message?: string;
  data: T;
  errors?: Record<string, string[]>;
}

class ApiClient {
  private baseURL: string;
  private token: string | null = null;

  constructor() {
    this.baseURL = 'http://localhost:8000/api';
  }

  setToken(token: string) {
    this.token = token;
  }

  clearToken() {
    this.token = null;
  }

  private getHeaders(token?: string | null): HeadersInit {
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    const finalToken = token || this.token;
    if (finalToken) {
      headers.Authorization = `Bearer ${finalToken}`;
    }

    return headers;
  }

  private async handleResponse<T>(response: Response): Promise<ApiResponse<T>> {
    const contentType = response.headers.get('content-type');
    
    if (!contentType || !contentType.includes('application/json')) {
      throw new Error('서버로부터 올바르지 않은 응답을 받았습니다.');
    }

    const data = await response.json();

    if (!response.ok) {
      throw {
        response: {
          status: response.status,
          data: data
        },
        message: data.message || '요청 처리 중 오류가 발생했습니다.'
      };
    }

    return data;
  }

  async get<T = unknown>(endpoint: string, token?: string | null): Promise<ApiResponse<T>> {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: 'GET',
        headers: this.getHeaders(token),
      });

      return this.handleResponse<T>(response);
    } catch (error) {
      console.error('API GET Error:', error);
      throw error;
    }
  }

  async post<T = unknown>(endpoint: string, data?: unknown, token?: string | null): Promise<ApiResponse<T>> {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: 'POST',
        headers: this.getHeaders(token),
        body: JSON.stringify(data),
      });

      return this.handleResponse<T>(response);
    } catch (error) {
      console.error('API POST Error:', error);
      throw error;
    }
  }

  async put<T = unknown>(endpoint: string, data?: unknown, token?: string | null): Promise<ApiResponse<T>> {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: 'PUT',
        headers: this.getHeaders(token),
        body: JSON.stringify(data),
      });

      return this.handleResponse<T>(response);
    } catch (error) {
      console.error('API PUT Error:', error);
      throw error;
    }
  }

  async delete<T = unknown>(endpoint: string, token?: string | null): Promise<ApiResponse<T>> {
    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, {
        method: 'DELETE',
        headers: this.getHeaders(token),
      });

      return this.handleResponse<T>(response);
    } catch (error) {
      console.error('API DELETE Error:', error);
      throw error;
    }
  }
}

export const apiClient = new ApiClient();

// 토큰 관리 함수들
export function setAuthToken(token: string) {
  apiClient.setToken(token);
}

export function clearAuthToken() {
  apiClient.clearToken();
}

// 기존 함수들을 유지하되, 새로운 apiClient를 사용하도록 수정
export async function fetchFeatures() {
  return apiClient.get<Feature[]>('/features');
}

export interface Feature {
  id: number;
  name: string;
  icon: string;
  description?: string;
  category_id: number;
  config?: {
    fields: Array<{
      field_level: 'parent' | 'child' | 'independent';
      parent_field?: string;
      show_when_value?: string;
      name: string;
      key: string;
      unit?: string;
      type: 'text' | 'number' | 'textarea' | 'select' | 'radio' | 'checkbox' | 'date' | 'time' | 'datetime';
      placeholder?: string;
      required: boolean;
      show_unit: boolean;
      options?: Array<{ label: string; value: string }>;
      multiple?: boolean;
      allow_undecided?: boolean;
      feature_scope?: 'by_zone' | 'overall';
      feature_zones?: string[];
      internal_resource?: boolean;
      internal_resource_person?: string;
    }>;
  };
  slug?: string;
  sort_order?: number;
  is_active?: boolean;
  is_premium?: boolean;
  budget_allocation?: boolean; // 추가된 필드
  internal_resource_flag?: boolean; // 추가된 필드
  created_at: string;
  updated_at: string;
  category: {
    id: number;
    name: string;
    slug?: string;
    description?: string;
    sort_order?: number;
    is_active?: boolean;
    budget_allocation?: boolean; // 추가된 필드
    internal_resource_flag?: boolean; // 추가된 필드
    created_at: string;
    updated_at: string;
  };
  recommendations?: RecommendedFeature[]; // RecommendedFeature 타입 사용
  selections?: RfpSelection[];
}

export interface FeatureCategory {
  id: number;
  name: string;
  slug?: string;
  description?: string;
  sort_order?: number;
  is_active?: boolean;
  budget_allocation?: boolean; // 추가된 필드
  internal_resource_flag?: boolean; // 추가된 필드
  created_at: string;
  updated_at: string;
  features: Feature[];
}

export interface RecommendedFeature extends Feature {
  pivot: {
    feature_id: number;
    recommended_feature_id: number;
    level: 'R1' | 'R2';
    priority: number | null;
  };
}

export async function fetchFeatureCategories() {
  return apiClient.get<FeatureCategory[]>('/feature-categories');
}

export async function validateBudget(data: { event_id?: number; total_budget: number | null; is_total_budget_undecided: boolean; category_budgets: Record<number, number | null>; feature_budgets: Record<number, number | null> }) {
  return apiClient.post('/rfp/budget-validation', data);
}

export interface CreateRfpData {
  title: string;
  event_date?: string;
  selections: Array<{
    feature_id: number;
    details?: Record<string, unknown>;
  }>;
}

export async function createRfp(data: CreateRfpData) {
  return apiClient.post('/rfps', data);
}

export interface RfpSelection {
  id: number;
  rfp_id: number;
  feature_id: number;
  details: Record<string, unknown>;
  created_at: string;
  updated_at: string;
  feature?: Feature;
}

export interface Rfp {
  id: number;
  title: string;
  status: 'draft' | 'completed' | 'archived';
  event_date?: string;
  expected_attendees?: number | null;
  total_budget?: number | null;
  is_total_budget_undecided?: boolean;
  description?: string | null;
  user_id: number;
  created_at: string;
  updated_at: string;
  selections?: RfpSelection[];
}

export async function fetchMyRfps(token?: string | null) {
  return apiClient.get<Rfp[]>('/rfps', token);
}

export async function fetchRfpDetails(id: string | number, token?: string | null) {
  return apiClient.get<Rfp>(`/rfps/${id}`, token);
}

// Auth-related functions
export type { User }; // User 타입을 다시 내보냄

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface LoginData {
  email: string;
  password: string;
}

export interface UpdateUserData {
  name?: string;
  email?: string;
  password?: string;
  password_confirmation?: string;
  onboarded?: boolean;
  skip_onboarding?: boolean;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export async function registerUser(data: RegisterData) {
  return apiClient.post<AuthResponse>('/auth/register', data);
}

export async function loginUser(data: LoginData) {
  return apiClient.post<AuthResponse>('/auth/login', data);
}

export async function logoutUser(token?: string | null) {
  return apiClient.post('/auth/logout', {}, token);
}

export async function getCurrentUser(token?: string | null) {
  return apiClient.get<User>('/auth/user', token);
}

export async function updateUser(data: UpdateUserData, token?: string | null) {
  return apiClient.put<User>('/auth/user', data, token);
}

export async function markUserOnboarded(token?: string | null) {
  return apiClient.post<User>('/auth/user/mark-onboarded', {}, token);
}

// Dashboard stats functions
export interface DashboardStats {
  total_rfps: number;
  completed_rfps: number;
  monthly_rfp_counts: Record<string, number>;
  top_features: Array<{
    id: number;
    name: string;
    icon: string;
    usage_count: number;
  }>;
}

export async function fetchDashboardStats(token?: string | null) {
  return apiClient.get<DashboardStats>('/dashboard/stats', token);
}