// frontend/app/(agency)/dashboard/page.tsx

'use client';

import useAuthStore from '../../../lib/stores/authStore';
import { Button } from '@/components/ui/button';
import { useRouter } from 'next/navigation';
import api from '../../../lib/api';

export default function AgencyDashboardPage() {
  const { user, clearAuth } = useAuthStore();
  const router = useRouter();

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

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-gray-50 p-4">
      <div className="bg-white p-8 rounded-lg shadow-lg text-center">
        <h1 className="text-3xl font-bold text-accent-600 mb-4">
          대행사 대시보드
        </h1>
        {user ? (
          <p className="text-lg text-gray-700 mb-6">
            환영합니다, <span className="font-semibold text-accent-500">{user.name}</span>님! (타입: {user.user_type})
          </p>
        ) : (
          <p className="text-lg text-gray-700 mb-6">사용자 정보를 불러오는 중...</p>
        )}
        <p className="text-md text-gray-600 mb-8">
          이곳은 RFP 생성 및 관리, 제안서 평가를 위한 대시보드입니다.
        </p>
        <Button onClick={handleLogout} className="bg-destructive-500 hover:bg-destructive-600">
          로그아웃
        </Button>
      </div>
    </div>
  );
}