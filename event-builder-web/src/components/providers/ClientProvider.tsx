'use client';

interface ClientProviderProps {
  children: React.ReactNode;
}

// 단순히 children을 래핑만 하는 컴포넌트
export default function ClientProvider({ children }: ClientProviderProps) {
  return <>{children}</>;
} 