'use client';

import { useEffect } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import Header from '@/components/layout/Header';
import Footer from '@/components/layout/Footer';
import ClientProvider from '@/components/providers/ClientProvider';
import { useAuthStore } from '@/stores/authStore';

export default function MainLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const { user, getUser, isLoading, skipOnboarding } = useAuthStore();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    // 클라이언트 사이드에서만 실행
    if (typeof window !== 'undefined') {
      // 사용자 정보가 없으면 가져오기 시도
      if (!user && !isLoading) {
        getUser();
      }

      // 사용자 정보가 있고, 온보딩이 필요하며, 온보딩을 건너뛰지 않았고, 현재 온보딩 페이지가 아닐 경우 리다이렉트
      if (user && !user.onboarded && !skipOnboarding && pathname !== '/onboarding') {
        router.push('/onboarding');
      }
    }
  }, [user, isLoading, router, pathname, getUser, skipOnboarding]);

  return (
    <ClientProvider>
      <div className="min-h-screen flex flex-col">
        <Header />
        <main className="flex-1">
          {children}
        </main>
        <Footer />
      </div>
    </ClientProvider>
  );
}