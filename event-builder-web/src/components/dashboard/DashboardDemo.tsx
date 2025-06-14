'use client';

import React from 'react';
import StatsCard from './StatsCard';
import MonthlyRfpChart from './MonthlyRfpChart';
import TopFeaturesChart from './TopFeaturesChart';
import { useTranslation } from '@/lib/i18n';

export default function DashboardDemo() {
  const { t } = useTranslation();

  // 테스트용 데이터
  const mockStats = {
    totalRfps: 10,
    completedRfps: 6,
    monthlyRfpCounts: {
      "2025-01": 2,
      "2025-02": 2,
      "2025-03": 2,
      "2025-04": 2,
      "2025-05": 0,
      "2025-06": 0
    },
    topFeatures: [
      { id: 1, name: t('features.event_schedule'), icon: "calendar", usage_count: 8 },
      { id: 2, name: t('features.audio_equipment'), icon: "speaker", usage_count: 4 },
      { id: 5, name: t('features.catering_service'), icon: "utensils", usage_count: 3 },
      { id: 3, name: t('features.stage_facilities'), icon: "stage", usage_count: 3 },
      { id: 4, name: t('features.lighting_equipment'), icon: "lightbulb", usage_count: 2 }
    ]
  };
  return (
    <div className="min-h-screen bg-background p-6">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-2xl font-bold text-foreground mb-8">
          {t('dashboard.demoTitle')}
        </h1>
        
        {/* 통계 카드들 */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatsCard
            title={t('dashboard.totalRfpsTitle')}
            value={mockStats.totalRfps}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            }
            color="text-primary"
            bgColor="bg-primary/10"
            description={t('dashboard.totalRfpsDescription')}
          />
          
          <StatsCard
            title={t('dashboard.completedRfpsTitle')}
            value={mockStats.completedRfps}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
            }
            color="text-green-600"
            bgColor="bg-green-50"
            description={t('dashboard.completedRfpsDescription')}
          />
          
          <StatsCard
            title={t('dashboard.completionRateTitle')}
            value={`${Math.round((mockStats.completedRfps / mockStats.totalRfps) * 100)}%`}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            }
            color="text-purple-600"
            bgColor="bg-purple-50"
            trend={{ value: 15, isPositive: true }}
          />
          
          <StatsCard
            title={t('dashboard.thisMonthCreatedTitle')}
            value={mockStats.monthlyRfpCounts["2025-06"] || 0}
            icon={
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            }
            color="text-orange-600"
            bgColor="bg-orange-50"
            description={t('dashboard.thisMonthCreatedDescription')}
          />
        </div>
        
        {/* 차트들 */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <MonthlyRfpChart monthlyRfpCounts={mockStats.monthlyRfpCounts} />
          <TopFeaturesChart topFeatures={mockStats.topFeatures} />
        </div>
      </div>
    </div>
  );
}