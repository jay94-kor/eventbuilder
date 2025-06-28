// frontend/app/(agency)/dashboard/page.tsx

'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import useAuthStore from '../../../lib/stores/authStore';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import api from '../../../lib/api';
import { Rfp, RfpListResponse } from '@/lib/types';
import { AxiosError } from 'axios';

export default function AgencyDashboard() {
  const { user } = useAuthStore();
  const [rfps, setRfps] = useState<Rfp[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchRfps = async () => {
      try {
        setLoading(true);
        const response = await api.get<RfpListResponse>('/api/rfps');
        console.log('RFP 목록 응답:', response.data);
        
        if (response.data.rfps && response.data.rfps.data) {
          setRfps(response.data.rfps.data);
        } else {
          setRfps([]);
        }
      } catch (err) {
        const error = err as AxiosError<{ message: string }>;
        console.error('RFP 목록 조회 실패:', error.response?.data || error.message);
        setError(error.response?.data?.message || 'RFP 목록을 불러오는데 실패했습니다.');
      } finally {
        setLoading(false);
      }
    };

    fetchRfps();
  }, []);

  // 통계 계산
  const totalRfps = rfps.length;
  const draftRfps = rfps.filter(rfp => rfp.current_status === 'draft').length;
  const publishedRfps = rfps.filter(rfp => rfp.current_status === 'published').length;
  const closedRfps = rfps.filter(rfp => rfp.current_status === 'closed').length;

  const formatDate = (dateString: string) => {
    try {
      return new Date(dateString).toLocaleDateString('ko-KR');
    } catch {
      return '날짜 오류';
    }
  };

  const formatBudget = (budget: string) => {
    try {
      return Number(budget).toLocaleString('ko-KR') + '원';
    } catch {
      return '예산 정보 없음';
    }
  };

  if (loading) {
    return (
      <div className="p-8">
        <div className="flex items-center justify-center h-64">
          <div className="text-lg text-gray-600">로딩 중...</div>
        </div>
      </div>
    );
  }

  return (
    <div className="p-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-800 mb-2">대행사 대시보드</h1>
        <p className="text-gray-600">환영합니다, {user?.name}님!</p>
      </div>

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
          <p className="text-red-600 text-sm">{error}</p>
        </div>
      )}

      {/* 통계 카드들 */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">전체 RFP</CardTitle>
            <div className="h-4 w-4 text-muted-foreground">📊</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalRfps}</div>
            <p className="text-xs text-muted-foreground">총 생성된 RFP</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">초안</CardTitle>
            <div className="h-4 w-4 text-muted-foreground">📝</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{draftRfps}</div>
            <p className="text-xs text-muted-foreground">작성 중인 RFP</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">공개됨</CardTitle>
            <div className="h-4 w-4 text-muted-foreground">🚀</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{publishedRfps}</div>
            <p className="text-xs text-muted-foreground">공개된 RFP</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">마감됨</CardTitle>
            <div className="h-4 w-4 text-muted-foreground">✅</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{closedRfps}</div>
            <p className="text-xs text-muted-foreground">마감된 RFP</p>
          </CardContent>
        </Card>
      </div>

      {/* 액션 카드들 */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <Card className="hover:shadow-lg transition-shadow cursor-pointer">
          <CardHeader>
            <CardTitle className="text-lg">새 RFP 생성</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">새로운 제안요청서를 생성합니다.</p>
            <Link href="/agency/rfps/create">
              <Button className="w-full">RFP 생성하기</Button>
            </Link>
          </CardContent>
        </Card>

        <Card className="hover:shadow-lg transition-shadow cursor-pointer">
          <CardHeader>
            <CardTitle className="text-lg">RFP 관리</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">기존 RFP를 조회하고 관리합니다.</p>
            <Link href="/agency/rfps">
              <Button variant="outline" className="w-full">RFP 목록 보기</Button>
            </Link>
          </CardContent>
        </Card>

        <Card className="hover:shadow-lg transition-shadow cursor-pointer">
          <CardHeader>
            <CardTitle className="text-lg">제안서 검토</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">용역사 제안서를 검토합니다.</p>
            <Button variant="outline" className="w-full" disabled>
              준비 중
            </Button>
          </CardContent>
        </Card>
      </div>

      {/* 최근 RFP 목록 */}
      <Card>
        <CardHeader>
          <CardTitle className="text-xl">최근 RFP</CardTitle>
        </CardHeader>
        <CardContent>
          {rfps.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-gray-500 mb-4">생성된 RFP가 없습니다.</p>
              <Link href="/agency/rfps/create">
                <Button>첫 번째 RFP 생성하기</Button>
              </Link>
            </div>
          ) : (
            <div className="space-y-4">
              {rfps.slice(0, 5).map((rfp) => (
                <div key={rfp.id} className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                  <div className="flex-1">
                    <h3 className="font-semibold text-lg">{rfp.project?.project_name || '프로젝트명 없음'}</h3>
                    <p className="text-gray-600 text-sm mb-2">{rfp.rfp_description}</p>
                    <div className="flex items-center space-x-4 text-sm text-gray-500">
                      <span>상태: {rfp.current_status}</span>
                      <span>마감일: {formatDate(rfp.closing_at)}</span>
                      {rfp.project?.budget_including_vat && (
                        <span>예산: {formatBudget(rfp.project.budget_including_vat)}</span>
                      )}
                    </div>
                  </div>
                  <div className="flex space-x-2">
                    <Link href={`/agency/rfps/${rfp.id}`}>
                      <Button variant="outline" size="sm">보기</Button>
                    </Link>
                    <Link href={`/agency/rfps/${rfp.id}/edit`}>
                      <Button variant="outline" size="sm">수정</Button>
                    </Link>
                  </div>
                </div>
              ))}
              
              {rfps.length > 5 && (
                <div className="text-center pt-4">
                  <Link href="/agency/rfps">
                    <Button variant="outline">모든 RFP 보기</Button>
                  </Link>
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}