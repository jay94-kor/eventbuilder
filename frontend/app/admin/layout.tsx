'use client';

import Link from 'next/link';
import { Button } from '@/components/ui/button';
import useAuthStore from '../../lib/stores/authStore';
import { useRouter } from 'next/navigation';
import api from '../../lib/api';
import { useEffect } from 'react';

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const { user, clearAuth, isAuthenticated } = useAuthStore();
  const router = useRouter();

  // 권한 확인
  useEffect(() => {
    if (!isAuthenticated || !user || user.user_type !== 'admin') {
      router.push('/login');
    }
  }, [isAuthenticated, user, router]);

  const handleLogout = async () => {
    try {
      await api.post('/api/logout');
      clearAuth();
      router.push('/login');
    } catch (error) {
      console.error('로그아웃 실패:', error);
      clearAuth();
      router.push('/login');
    }
  };

  // 인증되지 않은 사용자나 권한이 없는 사용자는 로딩 표시
  if (!isAuthenticated || !user || user.user_type !== 'admin') {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <p>로딩 중...</p>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen bg-gray-50">
      {/* Sidebar */}
      <aside className="w-64 bg-white shadow-md p-6 flex flex-col">
        <div className="text-2xl font-bold text-blue-600 mb-8">Bidly Admin</div>
        <nav className="flex-1">
          <ul>
            <li className="mb-2">
              <Link href="/admin/dashboard" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                대시보드
              </Link>
            </li>
            <li className="mb-2">
              <Link href="/admin/element-definitions" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                요소 정의 관리
              </Link>
            </li>
            <li className="mb-2">
              <Link href="/admin/rfp-approvals" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                RFP 승인 관리
              </Link>
            </li>
            <li className="mb-2">
              <Link href="/admin/users" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                사용자 관리
              </Link>
            </li>
          </ul>
        </nav>
        <div className="mt-auto">
          <p className="text-sm text-gray-600 mb-2">
            {user.name} ({user.user_type})
          </p>
          <Button onClick={handleLogout} className="w-full">
            로그아웃
          </Button>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 p-8">
        {children}
      </main>
    </div>
  );
} 