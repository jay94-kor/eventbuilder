'use client';

import { useState, useEffect } from 'react';
import { useTranslation } from '@/lib/i18n';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import RfpCard from '@/components/rfp/RfpCard';
import { Skeleton } from '@/components/ui/skeleton';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Terminal } from 'lucide-react'; // Terminal 아이콘 추가
import { useAuth } from '@/hooks/useAuth'; // useAuth 훅 추가

interface Rfp {
  id: string;
  title: string;
  event_date: string | null;
  created_at: string;
  status: 'draft' | 'completed' | 'archived';
}

export default function RfpListPage() {
  const t = useTranslation();
  const { token } = useAuth(); // useAuth 훅에서 token 가져오기
  const [rfps, setRfps] = useState<Rfp[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    console.log("Auth Token:", token); // 토큰 값 콘솔 출력
  }, [token]);

  useEffect(() => {
    const fetchRfps = async () => {
      try {
        setLoading(true);
        const headers: HeadersInit = {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        };

        if (token) {
          headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch('/api/rfps', {
          headers: headers,
        });
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || 'RFP 목록을 불러오지 못했습니다.');
        }
        const data = await response.json();
        setRfps(data.data);
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'RFP 목록을 불러오지 못했습니다.';
        setError(errorMessage);
      } finally {
        setLoading(false);
      }
    };

    fetchRfps();
  }, []);

  if (loading) {
    return (
      <div className="container mx-auto p-4">
        <h1 className="text-3xl font-bold mb-6">{t('rfp_list.title')}</h1>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[...Array(3)].map((_, i) => (
            <Skeleton key={i} className="w-full h-48" />
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto p-4">
        <Alert variant="destructive">
          <Terminal className="h-4 w-4" />
          <AlertTitle>{t('common.error_occurred')}</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-4">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">{t('rfp_list.title')}</h1>
        <Link href="/rfp/create">
          <Button>{t('rfp_list.create_new_rfp')}</Button>
        </Link>
      </div>
      {rfps.length === 0 ? (
        <div className="text-center py-10">
          <h2 className="text-2xl font-semibold mb-2">{t('dashboard.no_rfps_title')}</h2>
          <p className="text-muted-foreground mb-6">{t('dashboard.no_rfps_desc')}</p>
          <Link href="/rfp/create">
            <Button size="lg">{t('dashboard.create_rfp_button')}</Button>
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {rfps.map((rfp) => (
            <RfpCard key={rfp.id} rfp={rfp} />
          ))}
        </div>
      )}
    </div>
  );
}