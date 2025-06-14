'use client';

import React from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';

interface MonthlyRfpChartProps {
  monthlyRfpCounts: Record<string, number>;
}

interface ChartData {
  month: string;
  count: number;
  displayMonth: string;
}

// 차트 색상 팔레트
const COLORS = [
  '#3b82f6', // blue-500
  '#10b981', // emerald-500
  '#8b5cf6', // violet-500
  '#f59e0b', // amber-500
  '#ef4444', // red-500
  '#06b6d4', // cyan-500
  '#84cc16', // lime-500
  '#f97316', // orange-500
  '#ec4899', // pink-500
  '#6366f1', // indigo-500
];

import { useTranslation } from '@/lib/i18n'; // useTranslation 훅 import

export default function MonthlyRfpChart({ monthlyRfpCounts }: MonthlyRfpChartProps) {
  const { t } = useTranslation();

  // 더미 데이터 생성 함수
  const generateDummyData = () => {
    const dummyData: ChartData[] = [];
    const today = new Date();
    for (let i = 5; i >= 0; i--) {
      const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
      const month = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
      const displayMonth = `${date.getMonth() + 1}월`;
      dummyData.push({
        month,
        count: Math.floor(Math.random() * 10) + 1, // 1에서 10 사이의 랜덤 값
        displayMonth,
      });
    }
    return dummyData;
  };

  const hasData = Object.keys(monthlyRfpCounts).some(key => monthlyRfpCounts[key] > 0);
  const chartData: ChartData[] = hasData
    ? Object.entries(monthlyRfpCounts).map(([month, count]) => {
        const [, monthNum] = month.split('-');
        const displayMonth = `${parseInt(monthNum)}월`;
        return { month, count, displayMonth };
      })
    : generateDummyData(); // 데이터가 없으면 더미 데이터 사용

  // 최대값 계산 (Y축 범위 설정용)
  const maxCount = Math.max(...chartData.map(d => d.count));
  const yAxisMax = Math.max(5, Math.ceil(maxCount * 1.2)); // 최소 5, 최대값의 120%

  return (
    <Card className="p-6">
      {/* 헤더 */}
      <CardHeader className="flex flex-row items-center justify-between pb-2">
        <CardTitle className="text-heading-md">
          {t('dashboard.monthly_rfp_trend_title')}
        </CardTitle>
        <CardDescription className="text-sm text-muted-foreground">
          {t('dashboard.monthly_rfp_trend_desc')}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="text-right mb-4">
          <p className="text-sm text-muted-foreground">{t('dashboard.total_created')}</p>
          <p className="text-2xl font-bold text-primary">
            {chartData.reduce((sum, item) => sum + item.count, 0)}{t('common.unit_count')}
          </p>
        </div>

      {/* 차트 */}
      <div className="h-64">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={chartData} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
            <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
            <XAxis
              dataKey="displayMonth"
              tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
              axisLine={{ stroke: 'hsl(var(--border))' }}
              tickLine={{ stroke: 'hsl(var(--border))' }}
            />
            <YAxis
              tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
              axisLine={{ stroke: 'hsl(var(--border))' }}
              tickLine={{ stroke: 'hsl(var(--border))' }}
              domain={[0, yAxisMax]}
            />
            <Tooltip
              contentStyle={{
                backgroundColor: 'hsl(var(--background))',
                border: '1px solid hsl(var(--border))',
                borderRadius: 'var(--radius)',
                fontSize: '14px',
                boxShadow: 'var(--shadow)'
              }}
              formatter={(value: number) => [value, t('dashboard.rfp_count_label')]}
              labelFormatter={(label: string) => `${label}`}
            />
            <Bar
              dataKey="count"
              fill={COLORS[0]} // 첫 번째 색상 사용
              radius={[4, 4, 0, 0]}
              name={t('dashboard.rfp_count_label')}
            />
          </BarChart>
        </ResponsiveContainer>

</div>
        {!hasData && (
          <div className="absolute inset-0 flex items-center justify-center bg-background bg-opacity-90 rounded-lg">
            <div className="text-center text-muted-foreground">
              <div className="text-4xl mb-2">✨</div>
              <p className="text-lg font-semibold">{t('dashboard.no_data_chart_title')}</p>
              <p className="text-sm mt-1">{t('dashboard.no_data_chart_desc')}</p>
            </div>
          </div>
        )}
      </CardContent>
      {hasData && (
        <CardFooter className="flex items-center justify-between text-sm text-muted-foreground pt-4 border-t">
          <span>
            {t('dashboard.average_rfp_count')}: {(chartData.reduce((sum, item) => sum + item.count, 0) / chartData.length).toFixed(1)}{t('common.unit_count')}/{t('common.unit_month')}
          </span>
          <span>
            {t('dashboard.max_rfp_count')}: {Math.max(...chartData.map(d => d.count))}{t('common.unit_count')}
          </span>
        </CardFooter>
      )}
    </Card>
  );
} 