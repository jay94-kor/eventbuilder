'use client';

import { useEffect } from 'react';
import { useAuthStore } from '@/stores/authStore';
import Cookies from 'js-cookie';

export const useAuth = () => {
  const store = useAuthStore();
  const {
    user,
    token,
    isLoading,
    error,
    login,
    register,
    logout,
    getUser,
    clearError,
    setToken,
    clearAuth,
    updateUser,
    setOnboarded // setOnboarded 추가
  } = store;

  // 수동 rehydration
  useEffect(() => {
    // 클라이언트에서만 실행
    if (typeof window !== 'undefined') {
      useAuthStore.persist.rehydrate();
    }
  }, []);

  // *** 추가된 부분: Zustand 스토어의 토큰을 쿠키와 동기화 ***
  useEffect(() => {
    if (token) {
      // 토큰이 있으면 쿠키에 저장 (7일 동안 유효)
      Cookies.set('auth-token', token, { expires: 7, path: '/' });
    } else {
      // 토큰이 없으면 쿠키에서 제거
      Cookies.remove('auth-token', { path: '/' });
    }
  }, [token]);

  // 토큰이 있고 사용자 정보가 없으며 로딩 중이 아닐 때 사용자 정보 가져오기
  useEffect(() => {
    if (token && !user && !isLoading) {
      getUser();
    }
  }, [token, user, isLoading]); // getUser 제거

  // 로그인 상태 확인
  const isAuthenticated = !!(user && token);
  
  // 초기 로딩 상태 (토큰은 있지만 user 정보가 없는 경우)
  const isInitialLoading = !!(token && !user && isLoading); // 이 상태는 이제 getUser 호출 여부를 판단하는 데 사용되지 않음

  return {
    // 상태
    user,
    token,
    isLoading,
    error,
    isAuthenticated,
    isInitialLoading,
    
    // 액션들
    login,
    register,
    logout,
    getUser,
    clearError,
    setToken,
    clearAuth,
    updateUser,
    setOnboarded, // setOnboarded 추가
  };
};

export default useAuth; 