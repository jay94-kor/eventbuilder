// frontend/app/Providers.tsx
'use client'; // 이 컴포넌트는 클라이언트 컴포넌트임을 명시

import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useState } from 'react';
import AuthProvider from './AuthProvider'; // 새로 생성할 AuthProvider 임포트

export default function Providers({ children }: { children: React.ReactNode }) {
  // useState를 사용하여 QueryClient 인스턴스를 한 번만 생성하고 유지합니다.
  const [queryClient] = useState(() => new QueryClient());

  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        {children}
      </AuthProvider>
    </QueryClientProvider>
  );
}