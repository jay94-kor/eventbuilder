'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import api from '../../../lib/api';
import { Rfp } from '@/lib/types';
import { AxiosError } from 'axios';

export default function RfpListPage() {
  const [rfps, setRfps] = useState<Rfp[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchRfps = async () => {
      try {
        setLoading(true);
        const response = await api.get('/api/rfps');
        console.log('RFP 목록 응답:', response.data);
        
        if (response.data.rfps && response.data.rfps.data) {
          setRfps(response.data.rfps.data);
        } else {
          setRfps([]);
        }
      } catch (err) {
        const error = err as AxiosError<{ message: string }>;
        console.error('RFP 목록 로드 실패:', error.response?.data || error.message);
        setError(error.response?.data?.message || 'RFP 목록을 불러오는데 실패했습니다.');
      } finally {
        setLoading(false);
      }
    };

    fetchRfps();
  }, []);

  const getStatusBadge = (status: string) => {
    const statusConfig = {
      draft: { label: '초안', color: 'bg-yellow-100 text-yellow-800' },
      approval_pending: { label: '승인 대기', color: 'bg-orange-100 text-orange-800' },
      approved: { label: '승인됨', color: 'bg-blue-100 text-blue-800' },
      rejected: { label: '반려됨', color: 'bg-red-100 text-red-800' },
      published: { label: '공개됨', color: 'bg-green-100 text-green-800' },
      closed: { label: '마감됨', color: 'bg-gray-100 text-gray-800' },
    };

    const config = statusConfig[status as keyof typeof statusConfig] || 
                  { label: status, color: 'bg-gray-100 text-gray-800' };

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${config.color}`}>
        {config.label}
      </span>
    );
  };

  if (loading) {
    return (
      <div className="p-8">
        <div className="max-w-6xl mx-auto">
          <div className="text-center py-12">
            <p className="text-gray-500">RFP 목록을 불러오는 중...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="p-8">
      <div className="max-w-6xl mx-auto">
        <div className="flex justify-between items-center mb-6">
          <div>
            <h1 className="text-3xl font-bold text-gray-800">RFP 관리</h1>
            <p className="text-gray-600">생성된 RFP 목록을 확인하고 관리합니다.</p>
          </div>
          <Link href="/agency/rfps/create">
            <Button className="bg-blue-600 hover:bg-blue-700">
              🎯 새 RFP 생성
            </Button>
          </Link>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <p className="text-red-600 text-sm">{error}</p>
          </div>
        )}

        {rfps.length === 0 ? (
          <div className="text-center py-12">
            <div className="mb-4">
              <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span className="text-2xl">📝</span>
              </div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">아직 RFP가 없습니다</h3>
              <p className="text-gray-500 mb-6">첫 번째 RFP를 생성하여 시작해보세요.</p>
              <Link href="/agency/rfps/create">
                <Button className="bg-blue-600 hover:bg-blue-700">
                  첫 번째 RFP 생성하기
                </Button>
              </Link>
            </div>
          </div>
        ) : (
          <div className="space-y-4">
            {rfps.map((rfp) => (
              <Card key={rfp.id} className="hover:shadow-md transition-shadow">
                <CardHeader>
                  <div className="flex justify-between items-start">
                    <div className="flex-1">
                      <CardTitle className="text-lg">
                        {rfp.project?.project_name || 'RFP'}
                      </CardTitle>
                      <div className="flex items-center space-x-4 mt-2">
                        {getStatusBadge(rfp.current_status)}
                        <span className="text-sm text-gray-500">
                          발주 형태: {
                            rfp.issue_type === 'integrated' ? '통합 발주' :
                            rfp.issue_type === 'separated_by_element' ? '요소별 분리 발주' :
                            rfp.issue_type === 'separated_by_group' ? '그룹별 분리 발주' : 
                            rfp.issue_type
                          }
                        </span>
                      </div>
                    </div>
                    <div className="text-right text-sm text-gray-500">
                      생성일: {rfp.created_at ? new Date(rfp.created_at).toLocaleDateString() : ''}
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label className="text-sm font-medium text-gray-500">위치</label>
                      <p className="text-sm text-gray-900">{rfp.project?.location || '미정'}</p>
                    </div>
                    <div>
                      <label className="text-sm font-medium text-gray-500">행사 기간</label>
                      <p className="text-sm text-gray-900">
                        {rfp.project?.start_datetime && rfp.project?.end_datetime ? (
                          `${new Date(rfp.project.start_datetime).toLocaleDateString()} ~ ${new Date(rfp.project.end_datetime).toLocaleDateString()}`
                        ) : '미정'}
                      </p>
                    </div>
                    <div>
                      <label className="text-sm font-medium text-gray-500">공고 마감</label>
                      <p className="text-sm text-gray-900">
                        {rfp.closing_at ? new Date(rfp.closing_at).toLocaleDateString() : '미정'}
                      </p>
                    </div>
                  </div>
                  
                  {rfp.project?.budget_including_vat && (
                    <div className="mt-4">
                      <label className="text-sm font-medium text-gray-500">예산</label>
                      <p className="text-sm text-gray-900">
                        {rfp.project.budget_including_vat.toLocaleString()}원 (VAT 포함)
                      </p>
                    </div>
                  )}

                  {rfp.rfp_description && (
                    <div className="mt-4">
                      <label className="text-sm font-medium text-gray-500">설명</label>
                      <p className="text-sm text-gray-900 line-clamp-2">{rfp.rfp_description}</p>
                    </div>
                  )}

                  {rfp.elements && rfp.elements.length > 0 && (
                    <div className="mt-4">
                      <label className="text-sm font-medium text-gray-500">포함 요소</label>
                      <div className="flex flex-wrap gap-1 mt-1">
                        {rfp.elements.map((element, index) => (
                          <span key={index} className="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                            {element.element_type}
                          </span>
                        ))}
                      </div>
                    </div>
                  )}

                  <div className="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-200">
                    <Button variant="outline" size="sm">
                      상세 보기
                    </Button>
                    {rfp.current_status === 'draft' && (
                      <Button variant="outline" size="sm">
                        편집
                      </Button>
                    )}
                    {rfp.current_status === 'approved' && (
                      <Button size="sm" className="bg-green-600 hover:bg-green-700">
                        공고 게시
                      </Button>
                    )}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </div>
  );
} 