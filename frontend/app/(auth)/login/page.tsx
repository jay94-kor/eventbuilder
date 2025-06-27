// frontend/app/(auth)/login/page.tsx

'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import api from '../../../lib/api';
import useAuthStore from '../../../lib/stores/authStore';
import { Button } from '@/components/ui/button'; // shadcn/ui Button 임포트

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();
  const setAuth = useAuthStore((state) => state.setAuth);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    try {
      const response = await api.post('/api/login', { email, password });
      const { user, token, message } = response.data;
      
      setAuth(token, user);
      console.log('로그인 성공:', message);
      
      // 사용자 타입에 따라 적절한 대시보드로 리다이렉트
      switch (user.user_type) {
        case 'admin':
          router.push('/admin/dashboard');
          break;
        case 'agency_member':
          router.push('/agency/dashboard');
          break;
        case 'vendor_member':
          router.push('/vendor/dashboard');
          break;
        default:
          router.push('/');
      }
    } catch (err: any) {
      console.error('로그인 실패:', err.response?.data || err.message);
      setError(err.response?.data?.message || '로그인에 실패했습니다. 이메일과 비밀번호를 확인해주세요.');
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 className="text-2xl font-bold text-center mb-6">Bidly 로그인</h2>
        <form onSubmit={handleSubmit}>
          <div className="mb-4">
            <label htmlFor="email" className="block text-gray-700 text-sm font-bold mb-2">
              이메일
            </label>
            <input
              type="email"
              id="email"
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <div className="mb-6">
            <label htmlFor="password" className="block text-gray-700 text-sm font-bold mb-2">
              비밀번호
            </label>
            <input
              type="password"
              id="password"
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          {error && <p className="text-red-500 text-xs italic mb-4">{error}</p>}
          <div className="flex items-center justify-between">
            <Button
              type="submit"
              className="w-full"
            >
              로그인
            </Button>
          </div>
        </form>
        <p className="text-center text-gray-500 text-xs mt-4">
          테스트 계정: <br/>
          관리자: admin@bidly.com / bidlyadmin123! <br/>
          대행사: agency.a.master@bidly.com / password <br/>
          용역사: vendor.x.master@bidly.com / password
        </p>
      </div>
    </div>
  );
}