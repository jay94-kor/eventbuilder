'use client';

/**
 * 개발 모드 전용 유틸리티들
 */

// 개발 모드에서만 실행되는 로깅 함수
export const devLog = (message: string, data?: unknown) => {
  if (process.env.NODE_ENV === 'development') {
    console.log(`[DEV] ${message}`, data || '');
  }
};

// 에러 로깅 개선
export const devError = (message: string, error?: unknown) => {
  if (process.env.NODE_ENV === 'development') {
    console.error(`[DEV ERROR] ${message}`, error || '');
    if (error && typeof error === 'object' && 'stack' in error) {
      console.error('Stack trace:', (error as Error).stack);
    }
  }
};

// 성능 측정
export const devPerf = (name: string, fn: () => void) => {
  if (process.env.NODE_ENV === 'development') {
    const start = performance.now();
    fn();
    const end = performance.now();
    console.log(`[DEV PERF] ${name}: ${end - start}ms`);
  } else {
    fn();
  }
};

// HMR 상태 감지
export const detectHMR = () => {
  if (process.env.NODE_ENV === 'development' && typeof window !== 'undefined') {
    // HMR 이벤트 리스너
    if ('webpackHotUpdate' in window) {
      devLog('HMR detected');
      return true;
    }
  }
  return false;
};

// 브라우저 호환성 체크
export const checkBrowserCompatibility = () => {
  if (typeof window === 'undefined') return true;
  
  const checks = {
    localStorage: typeof Storage !== 'undefined',
    fetch: typeof fetch !== 'undefined',
    Promise: typeof Promise !== 'undefined',
    WeakMap: typeof WeakMap !== 'undefined',
  };
  
  const failed = Object.entries(checks)
    .filter(([, supported]) => !supported)
    .map(([feature]) => feature);
  
  if (failed.length > 0) {
    devError('Browser compatibility issues:', failed);
    return false;
  }
  
  return true;
}; 