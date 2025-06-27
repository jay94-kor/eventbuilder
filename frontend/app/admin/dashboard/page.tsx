// frontend/app/(admin)/dashboard/page.tsx

'use client';

import Link from 'next/link';
import { Button } from '@/components/ui/button';

export default function AdminDashboard() {
  return (
    <div>
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-gray-800">관리자 대시보드</h1>
        <p className="text-gray-600">Bidly 시스템을 관리할 수 있습니다.</p>
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
                  <dt className="text-sm font-medium text-gray-500 truncate">
                    전체 RFP
                  </dt>
                  <dd className="text-lg font-medium text-gray-900">
                    -
                  </dd>
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
                  <span className="text-white text-sm font-medium">공고</span>
                </div>
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">
                    활성 공고
                  </dt>
                  <dd className="text-lg font-medium text-gray-900">
                    -
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white overflow-hidden shadow rounded-lg">
          <div className="p-5">
            <div className="flex items-center">
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                  <span className="text-white text-sm font-medium">계약</span>
                </div>
              </div>
              <div className="ml-5 w-0 flex-1">
                <dl>
                  <dt className="text-sm font-medium text-gray-500 truncate">
                    진행 중인 계약
                  </dt>
                  <dd className="text-lg font-medium text-gray-900">
                    -
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Action Cards */}
      <div className="bg-white shadow rounded-lg">
        <div className="px-4 py-5 sm:p-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">
            관리 메뉴
          </h3>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link href="/admin/element-definitions">
              <Button className="h-20 flex flex-col items-center justify-center w-full">
                <span className="text-lg font-medium">요소 정의 관리</span>
                <span className="text-sm text-gray-600">RFP 요소 타입 관리</span>
              </Button>
            </Link>
            
            <Link href="/admin/rfp-approvals">
              <Button className="h-20 flex flex-col items-center justify-center w-full" variant="outline">
                <span className="text-lg font-medium">RFP 승인</span>
                <span className="text-sm text-gray-600">결재 대기 RFP 승인</span>
              </Button>
            </Link>
            
            <Link href="/admin/users">
              <Button className="h-20 flex flex-col items-center justify-center w-full" variant="outline">
                <span className="text-lg font-medium">사용자 관리</span>
                <span className="text-sm text-gray-600">대행사/용역사 관리</span>
              </Button>
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}