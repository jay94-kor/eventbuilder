'use client';

import Link from 'next/link';
import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import useAuthStore from '../../lib/stores/authStore';
import api from '../../lib/api';

export default function AgencyLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const { user, clearAuth, isAuthenticated } = useAuthStore();
  const router = useRouter();

  // 권한 확인
  useEffect(() => {
    if (!isAuthenticated || !user || user.user_type !== 'agency_member') {
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

  if (!isAuthenticated || !user || user.user_type !== 'agency_member') {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <p>접근 권한이 없습니다.</p>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen bg-gray-50">
      {/* Sidebar */}
      <aside className="w-64 bg-white shadow-md p-6 flex flex-col">
        <div className="text-2xl font-bold text-blue-600 mb-8">Bidly Agency</div>
        <nav className="flex-1">
          <ul>
            <li className="mb-2">
              <Link href="/agency/dashboard" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                대시보드
              </Link>
            </li>
            <li className="mb-2">
              <Link href="/agency/rfps" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                RFP 목록
              </Link>
            </li>
            <li className="mb-2">
              <Link href="/agency/rfps/create" className="block p-2 rounded-md text-gray-700 hover:bg-blue-100 font-medium">
                🎯 RFP 생성
              </Link>
            </li>
            <li className="mb-2">
              <Link href="/agency/proposals" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                제안서 관리
              </Link>
            </li>
            <li className="mb-2">
              <Link href="/agency/contracts" className="block p-2 rounded-md text-gray-700 hover:bg-gray-100">
                계약 관리
              </Link>
            </li>
          </ul>
        </nav>
        <div className="mt-auto">
          <p className="text-sm text-gray-600 mb-2">
            {user.name} ({user.user_type})
          </p>
          <Button onClick={handleLogout} className="w-full bg-red-500 hover:bg-red-600">
            로그아웃
          </Button>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1">
        {children}
      </main>
    </div>
  );
} 