'use client';

import { useEffect, useState } from 'react';

/**
 * SSR/클라이언트 hydration 불일치를 방지하는 훅
 * 클라이언트에서만 컴포넌트가 렌더링되도록 보장
 */
export function useSafeHydration() {
  const [hasMounted, setHasMounted] = useState(false);

  useEffect(() => {
    // 컴포넌트가 마운트되었을 때만 상태 업데이트
    if (!hasMounted) {
      setHasMounted(true);
    }
  }, []); // 빈 dependency array로 한 번만 실행

  return hasMounted;
}

export default useSafeHydration; 