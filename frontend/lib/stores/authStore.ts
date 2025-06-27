// frontend/lib/stores/authStore.ts

import { create } from 'zustand';

// 사용자 정보 타입 정의 (API 문서 기반)
export interface User {
  id: string;
  name: string;
  email: string;
  user_type: 'admin' | 'agency_member' | 'vendor_member'; // API 문서의 user_type enum
  // 필요한 경우 다른 사용자 관련 필드 추가
}

interface AuthState {
  token: string | null;
  user: User | null;
  isAuthenticated: boolean; // 로그인 여부
  setAuth: (token: string, user: User) => void;
  clearAuth: () => void;
  // 초기화 시 로컬 스토리지에서 토큰/사용자 정보 불러오는 액션
  initializeAuth: () => void;
}

const useAuthStore = create<AuthState>((set, get) => ({
  token: null,
  user: null,
  isAuthenticated: false,

  setAuth: (token, user) => {
    // 로컬 스토리지에 토큰과 사용자 정보 저장
    if (typeof window !== 'undefined') {
      localStorage.setItem('bidly_token', token);
      localStorage.setItem('bidly_user', JSON.stringify(user));
    }
    set({ token, user, isAuthenticated: true });
  },

  clearAuth: () => {
    // 로컬 스토리지에서 토큰과 사용자 정보 제거
    if (typeof window !== 'undefined') {
      localStorage.removeItem('bidly_token');
      localStorage.removeItem('bidly_user');
    }
    set({ token: null, user: null, isAuthenticated: false });
  },

  initializeAuth: () => {
    console.log('🔄 인증 초기화 시작...');
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('bidly_token');
      const userStr = localStorage.getItem('bidly_user');
      console.log('🔍 localStorage 확인:', { 
        token: token ? '토큰 있음' : '토큰 없음', 
        user: userStr ? '사용자 정보 있음' : '사용자 정보 없음' 
      });
      
      if (token && userStr) {
        try {
          const user: User = JSON.parse(userStr);
          console.log('✅ 인증 정보 복구 성공:', { user: user.email, user_type: user.user_type });
          set({ token, user, isAuthenticated: true });
        } catch (e) {
          console.error("❌ 사용자 정보 파싱 실패:", e);
          get().clearAuth(); // 파싱 실패 시 인증 정보 초기화
        }
      } else {
        console.log('⚠️ 저장된 인증 정보 없음 - 로그아웃 상태로 설정');
        get().clearAuth(); // 토큰이 없으면 인증 정보 초기화
      }
    } else {
      console.log('🚫 서버 사이드 렌더링 중 - localStorage 접근 불가');
    }
  },
}));

export default useAuthStore;