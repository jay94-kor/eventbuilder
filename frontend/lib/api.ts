// frontend/lib/api.ts

import axios, { AxiosInstance, AxiosResponse, AxiosError } from 'axios';

// 백엔드 API 기본 URL을 환경 변수에서 가져옵니다.
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL;

// Axios 인스턴스 생성
const api: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // CORS 문제 해결 및 쿠키 전송을 위해 필요할 수 있음 (Sanctum 사용 시)
});

// 요청 인터셉터: 모든 요청에 Bearer 토큰 추가
api.interceptors.request.use(
  (config) => {
    // 클라이언트 사이드에서만 localStorage에 접근하도록 확인
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('bidly_token'); // 저장된 토큰 이름에 맞게 수정
      console.log('🔑 API 요청 시 토큰:', token ? '토큰 있음' : '토큰 없음');
      console.log('📍 요청 URL:', config.url);
      
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
        console.log('✅ Authorization 헤더 추가됨');
      } else {
        console.warn('⚠️ 토큰이 없어 Authorization 헤더를 추가하지 않음');
      }
    }
    return config;
  },
  (error) => {
    console.error('❌ API 요청 인터셉터 에러:', error);
    return Promise.reject(error);
  }
);

// 응답 인터셉터: 에러 처리 (401 Unauthorized 등)
api.interceptors.response.use(
  (response: AxiosResponse) => {
    return response;
  },
  async (error: AxiosError) => {
    if (error.response) {
      const { status } = error.response;

      if (status === 401) {
        // 401 Unauthorized 에러 발생 시 로그아웃 처리
        // TODO: 실제 로그아웃 로직 (예: Zustand 스토어 업데이트, 페이지 리다이렉션) 구현 필요
        console.error('인증 실패: 401 Unauthorized. 로그아웃 처리합니다.');
        if (typeof window !== 'undefined') {
          localStorage.removeItem('bidly_token');
          // router.push('/login') 등의 로직이 여기에 올 수 있습니다.
          // 이 부분은 AuthProvider에서 중앙 집중적으로 처리됩니다.
        }
      } else if (status === 403) {
        // 403 Forbidden 에러 처리
        console.error('권한 없음: 403 Forbidden. 접근 권한이 없습니다.');
        // 접근 거부 페이지로 리다이렉트 또는 메시지 표시
      } else if (status === 422) {
        // 422 Unprocessable Entity (유효성 검사 실패)
        console.warn('유효성 검사 실패:', error.response?.data);
      }
      // TODO: 전역 토스트 메시지 시스템 연동 (옵션)
    }
    return Promise.reject(error);
  }
);

export default api;