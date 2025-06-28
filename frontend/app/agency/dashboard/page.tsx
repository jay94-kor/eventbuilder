// frontend/app/(agency)/dashboard/page.tsx

'use client';

import Link from 'next/link';
import useAuthStore from '../../../lib/stores/authStore';
import { Button } from '@/components/ui/button';

export default function AgencyDashboard() {
  const { user } = useAuthStore();

  return (
    <div className="p-8">
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-gray-800">대행사 대시보드</h1>
        <p className="text-gray-600">RFP 생성 및 관리, 제안서 평가를 위한 대시보드입니다.</p>
        {user && (
          <p className="text-sm text-gray-500 mt-2">
            환영합니다, <strong>{user.name}</strong>님!
          </p>
        )}
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                  <span className="text-white text-sm font-medium">RFP</span>
                </div>
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">진행 중인 RFP</dt>
                  <dd className="text-lg font-medium text-gray-900">12</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                  <span className="text-white text-sm font-medium">✓</span>
                </div>
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">완료된 프로젝트</dt>
                  <dd className="text-lg font-medium text-gray-900">8</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                  <span className="text-white text-sm font-medium">📄</span>
                </div>
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">받은 제안서</dt>
                  <dd className="text-lg font-medium text-gray-900">47</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Action Cards */}
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-2 mb-8">
        {/* RFP 생성 카드 */}
        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg overflow-hidden">
          <div className="p-6 text-white">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-xl font-bold mb-2">🎯 새 RFP 생성</h3>
                <p className="text-blue-100 mb-4">행사를 위한 새로운 제안요청서를 작성합니다.</p>
                <Link href="/agency/rfps/create">
                  <Button className="bg-white text-blue-600 hover:bg-gray-100 font-medium">
                    {'지금 시작하기 →'}
                  </Button>
                </Link>
              </div>
              <div className="text-6xl opacity-20">🎯</div>
            </div>
          </div>
        </div>

        {/* RFP 관리 카드 */}
        <div className="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg overflow-hidden">
          <div className="p-6 text-white">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-xl font-bold mb-2">📋 RFP 관리</h3>
                <p className="text-green-100 mb-4">기존 RFP들을 확인하고 관리합니다.</p>
                <Link href="/agency/rfps">
                  <Button className="bg-white text-green-600 hover:bg-gray-100 font-medium">
                    {'목록 보기 →'}
                  </Button>
                </Link>
              </div>
              <div className="text-6xl opacity-20">📋</div>
            </div>
          </div>
        </div>
      </div>

      {/* Recent Activity */}
      <div className="bg-white shadow rounded-lg">
        <div className="p-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">최근 활동</h3>
          <div className="space-y-3">
            <div className="flex items-center text-sm">
              <div className="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
              <span className="text-gray-600">새로운 RFP &quot;2024 IT 컨퍼런스&quot;가 생성되었습니다.</span>
              <span className="ml-auto text-gray-400">2시간 전</span>
            </div>
            <div className="flex items-center text-sm">
              <div className="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
              <span className="text-gray-600">제안서 평가가 완료되었습니다.</span>
              <span className="ml-auto text-gray-400">5시간 전</span>
            </div>
            <div className="flex items-center text-sm">
              <div className="w-2 h-2 bg-orange-500 rounded-full mr-3"></div>
              <span className="text-gray-600">새로운 제안서가 접수되었습니다.</span>
              <span className="ml-auto text-gray-400">1일 전</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}