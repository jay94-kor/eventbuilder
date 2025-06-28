// frontend/app/AuthProvider.tsx

'use client'; // 클라이언트 컴포넌트로 지정

import { useEffect, useRef, ReactNode } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import useAuthStore from '../lib/stores/authStore'; // authStore 임포트
import api from '../lib/api'; // api 인스턴스 임포트

interface AuthProviderProps {
  children: ReactNode;
}

const publicPaths = ['/login']; // 인증이 필요 없는 경로

export default function AuthProvider({ children }: AuthProviderProps) {
  const router = useRouter();
  const pathname = usePathname();
  const { isAuthenticated, initializeAuth, user } = useAuthStore();
  const interceptorId = useRef<number | undefined>(undefined);

  useEffect(() => {
    initializeAuth();

    const currentInterceptorId = interceptorId.current;
    if (currentInterceptorId !== undefined) {
      api.interceptors.response.eject(currentInterceptorId);
    }

    const newInterceptorId = api.interceptors.response.use(
      (response) => response,
      async (error) => {
        if (error.response?.status === 401) {
          useAuthStore.getState().clearAuth();
        } else if (error.response && error.response.status === 403) {
          console.error('🚫 권한 없음: 403 Forbidden. 접근 권한이 없습니다.');
          // TODO: 403 페이지 또는 권한 없음 메시지 표시 (향후 구현)
          // router.replace('/forbidden');
        }
        return Promise.reject(error);
      }
    );
    
    interceptorId.current = newInterceptorId;

    return () => {
      api.interceptors.response.eject(newInterceptorId);
    };
  }, [initializeAuth]);

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