// frontend/app/AuthProvider.tsx

'use client'; // 클라이언트 컴포넌트로 지정

import { useEffect } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import useAuthStore from '../lib/stores/authStore'; // authStore 임포트
import api from '../lib/api'; // api 인스턴스 임포트

interface AuthProviderProps {
  children: React.ReactNode;
}

const publicPaths = ['/login']; // 인증이 필요 없는 경로

export default function AuthProvider({ children }: AuthProviderProps) {
  const router = useRouter();
  const pathname = usePathname();
  const { isAuthenticated, initializeAuth, clearAuth, user } = useAuthStore();

  useEffect(() => {
    initializeAuth(); // 앱 로드 시 로컬 스토리지에서 인증 정보 초기화
  }, [initializeAuth]);

  useEffect(() => {
    // API 인터셉터에 실제 로그아웃 로직 연결
    // 이 AuthProvider가 마운트될 때 axios 인스턴스에 핸들러를 등록하는 방식으로 처리합니다.

    // 기존 인터셉터 제거 (중복 등록 방지) - 이전에 등록된 핸들러가 있다면 제거
    const requestInterceptorIndex = api.interceptors.request.handlers.findIndex(
      handler => (handler as any)?.id === 'auth-request-interceptor'
    );
    if (requestInterceptorIndex > -1) {
      api.interceptors.request.eject(requestInterceptorIndex);
    }

    const responseInterceptorIndex = api.interceptors.response.handlers.findIndex(
      handler => (handler as any)?.id === 'auth-response-interceptor'
    );
    if (responseInterceptorIndex > -1) {
      api.interceptors.response.eject(responseInterceptorIndex);
    }
    
    // 요청 인터셉터 (토큰 추가)
    const requestInterceptorId = api.interceptors.request.use(
      (config) => {
        if (typeof window !== 'undefined') {
          const token = localStorage.getItem('bidly_token');
          if (token) {
            config.headers.Authorization = `Bearer ${token}`;
          }
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );
    // 인터셉터에 ID 부여 (제거 시 활용)
    (api.interceptors.request.handlers[api.interceptors.request.handlers.length - 1] as any).id = 'auth-request-interceptor';

    // 응답 인터셉터 (401 에러 처리 및 로그아웃 리다이렉션)
    const responseInterceptorId = api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response && error.response.status === 401) {
          console.error('인증 실패: 401 Unauthorized. 로그아웃 처리 및 로그인 페이지로 리다이렉트합니다.');
          clearAuth(); // Zustand 스토어 초기화
          if (pathname !== '/login') { // 이미 로그인 페이지가 아니라면 리다이렉트
            router.replace('/login');
          }
        } else if (error.response && error.response.status === 403) {
          console.error('권한 없음: 403 Forbidden. 접근 권한이 없습니다.');
          // TODO: 403 페이지 또는 권한 없음 메시지 표시
          // router.replace('/forbidden');
        }
        return Promise.reject(error);
      }
    );
    // 인터셉터에 ID 부여 (제거 시 활용)
    (api.interceptors.response.handlers[api.interceptors.response.handlers.length - 1] as any).id = 'auth-response-interceptor';


    // 컴포넌트 언마운트 시 인터셉터 제거
    return () => {
      api.interceptors.request.eject(requestInterceptorId);
      api.interceptors.response.eject(responseInterceptorId);
    };
  }, [clearAuth, pathname, router]); // 의존성 배열에 router 추가

  // 인증 상태에 따른 라우팅 처리
  useEffect(() => {
    // initializeAuth가 비동기적으로 완료된 후 또는 user 상태가 변경될 때만 동작
    // isAuthenticated는 null, true, false 세 가지 상태를 가질 수 있음.
    // 초기화가 완료되고 isAuthenticated가 true/false로 결정된 후에 라우팅 로직 실행.
    if (isAuthenticated !== undefined) {
      const isPublicPath = publicPaths.includes(pathname);

      if (!isAuthenticated && !isPublicPath) {
        // 인증되지 않았고, 공개 경로가 아니라면 로그인 페이지로
        router.replace('/login');
      } else if (isAuthenticated && isPublicPath) {
        // 인증되었고, 로그인 페이지라면 사용자 타입에 따라 대시보드로 리다이렉트
        if (user) {
          switch (user.user_type) {
            case 'admin':
              router.replace('/admin/dashboard');
              break;
            case 'agency_member':
              router.replace('/agency/dashboard');
              break;
            case 'vendor_member':
              router.replace('/vendor/dashboard');
              break;
            default:
              router.replace('/login'); // 알 수 없는 유저 타입
              break;
          }
        }
      }
    }
  }, [isAuthenticated, pathname, router, user]);

  return <>{children}</>;
}