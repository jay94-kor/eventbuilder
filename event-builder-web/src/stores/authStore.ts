import { create } from 'zustand';
import { persist, devtools } from 'zustand/middleware';
import { apiClient, UpdateUserData, AuthResponse } from '@/lib/api';
import { User } from '@/types/auth'; // User 타입을 @/types/auth에서 임포트

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

interface AuthState {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  error: string | null;
  skipOnboarding: boolean; // skipOnboarding 상태 추가
}

interface AuthActions {
  login: (data: LoginData) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => Promise<void>;
  getUser: () => Promise<void>;
  clearError: () => void;
  setToken: (token: string) => void;
  clearAuth: () => void;
  updateUser: (data: UpdateUserData) => Promise<void>;
  setOnboarded: (onboarded: boolean) => void; // onboarded 상태 업데이트 액션 추가
  setSkipOnboarding: (skipOnboarding: boolean) => void; // skipOnboarding 상태 업데이트 액션 추가
}

export const useAuthStore = create<AuthState & AuthActions>()(
  devtools(
    persist(
      (set, get) => ({
        // 상태
        user: null,
        token: null,
        isLoading: false,
        error: null,
        skipOnboarding: false, // 초기값 설정

        // 액션
        login: async (data: LoginData) => {
          try {
            set({ isLoading: true, error: null });
            
            const response = await apiClient.post<AuthResponse>('/auth/login', data);
            
            if (response.success) {
              const { user, token } = response.data;
              set({ user, token, isLoading: false, skipOnboarding: user.skip_onboarding });
              
              // API 클라이언트에 토큰 설정
              apiClient.setToken(token);
            } else {
              throw new Error(response.message || '로그인에 실패했습니다.');
            }
          } catch (error: unknown) {
            console.error('Login error:', error);
            const errorMessage = error instanceof Error 
              ? error.message 
              : (error as { response?: { data?: { message?: string } }; message?: string })?.response?.data?.message 
                || (error as { message?: string })?.message 
                || '로그인 중 오류가 발생했습니다.';
            set({ 
              error: errorMessage, 
              isLoading: false 
            });
            throw error;
          }
        },

        register: async (data: RegisterData) => {
          try {
            set({ isLoading: true, error: null });
            
            const response = await apiClient.post<AuthResponse>('/auth/register', data);
            
            if (response.success) {
              const { user, token } = response.data;
              set({ user, token, isLoading: false, skipOnboarding: user.skip_onboarding });
              
              // API 클라이언트에 토큰 설정
              apiClient.setToken(token);
            } else {
              throw new Error(response.message || '회원가입에 실패했습니다.');
            }
          } catch (error: unknown) {
            console.error('Register error:', error);
            const errorMessage = error instanceof Error 
              ? error.message 
              : (error as { response?: { data?: { message?: string } }; message?: string })?.response?.data?.message 
                || (error as { message?: string })?.message 
                || '회원가입 중 오류가 발생했습니다.';
            set({ 
              error: errorMessage, 
              isLoading: false 
            });
            throw error;
          }
        },

        logout: async () => {
          try {
            set({ isLoading: true });
            
            const { token } = get();
            if (token) {
              // 백엔드에 로그아웃 요청
              await apiClient.post('/auth/logout');
            }
            
            // 상태 초기화
            set({ user: null, token: null, isLoading: false, error: null, skipOnboarding: false });
            
            // API 클라이언트에서 토큰 제거
            apiClient.clearToken();
          } catch (error: unknown) {
            console.error('Logout error:', error);
            // 에러가 발생해도 로컬 상태는 초기화
            set({ user: null, token: null, isLoading: false, error: null, skipOnboarding: false });
            apiClient.clearToken();
          }
        },

        getUser: async () => {
          try {
            set({ isLoading: true, error: null });
            
            const response = await apiClient.get<User>('/auth/user');
            
            if (response.success) {
              set({ user: response.data, isLoading: false, skipOnboarding: response.data.skip_onboarding });
            } else {
              throw new Error(response.message || '사용자 정보를 가져올 수 없습니다.');
            }
          } catch (error: unknown) {
            console.error('Get user error:', error);
            const errorMessage = error instanceof Error 
              ? error.message 
              : (error as { response?: { data?: { message?: string }; status?: number }; message?: string })?.response?.data?.message 
                || (error as { message?: string })?.message 
                || '사용자 정보 조회 중 오류가 발생했습니다.';
            set({ 
              error: errorMessage, 
              isLoading: false 
            });
            
            // 인증 오류인 경우 로그아웃 처리
            if ((error as { response?: { status?: number } })?.response?.status === 401) {
              get().clearAuth();
            }
          }
        },

        clearError: () => {
          set({ error: null });
        },

        setToken: (token: string) => {
          set({ token });
          apiClient.setToken(token);
        },

        clearAuth: () => {
          set({ user: null, token: null, error: null, skipOnboarding: false });
          apiClient.clearToken();
        },

        setOnboarded: (onboarded: boolean) => {
          set((state) => ({
            user: state.user ? { ...state.user, onboarded } : null,
          }));
        },

        setSkipOnboarding: (skipOnboarding: boolean) => {
          set((state) => ({
            user: state.user ? { ...state.user, skip_onboarding: skipOnboarding } : null,
            skipOnboarding: skipOnboarding,
          }));
        },

        updateUser: async (data: UpdateUserData) => {
          try {
            set({ isLoading: true, error: null });
            const response = await apiClient.put<User>('/auth/user', data);

            if (response.success) {
              set({ user: response.data, isLoading: false, skipOnboarding: response.data.skip_onboarding });
            } else {
              throw new Error(response.message || '사용자 정보 업데이트에 실패했습니다.');
            }
          } catch (error: unknown) {
            console.error('Update user error:', error);
            const errorMessage = error instanceof Error
              ? error.message
              : (error as { response?: { data?: { message?: string } }; message?: string })?.response?.data?.message
                || (error as { message?: string })?.message
                || '사용자 정보 업데이트 중 오류가 발생했습니다.';
            set({
              error: errorMessage,
              isLoading: false
            });
            throw error;
          }
        }
      }),
      {
        name: 'auth-storage',
        skipHydration: true,
        partialize: (state) => ({
          user: state.user,
          token: state.token,
          skipOnboarding: state.skipOnboarding, // skipOnboarding 상태도 영구 저장
        }),
        onRehydrateStorage: () => (state) => {
          // 스토어 복원 시 API 클라이언트에 토큰 설정 및 사용자 정보 가져오기
          if (state?.token) {
            apiClient.setToken(state.token);
          }
        },
      }
    ),
    {
      name: 'auth-store'
    }
  )
);