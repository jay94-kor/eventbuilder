'use client';

import React from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';

interface TopFeature {
  id: number;
  name: string;
  icon: string;
  usage_count: number;
}

interface TopFeaturesChartProps {
  topFeatures: TopFeature[];
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

export default function TopFeaturesChart({ topFeatures }: TopFeaturesChartProps) {
  const { t } = useTranslation();

  // 더미 데이터 생성 함수
  const generateDummyData = () => {
    const dummyFeatures: TopFeature[] = [
      { id: 1, name: t('dashboard.dummy_feature_venue'), icon: 'building', usage_count: 15 },
      { id: 2, name: t('dashboard.dummy_feature_catering'), icon: 'utensils', usage_count: 12 },
      { id: 3, name: t('dashboard.dummy_feature_tech'), icon: 'projector', usage_count: 10 },
      { id: 4, name: t('dashboard.dummy_feature_staff'), icon: 'user-friends', usage_count: 8 },
      { id: 5, name: t('dashboard.dummy_feature_design'), icon: 'paint-brush', usage_count: 7 },
    ];
    return dummyFeatures.map((feature, index) => ({
      ...feature,
      fill: COLORS[index % COLORS.length]
    }));
  };

  const hasData = topFeatures.length > 0 && topFeatures.some(f => f.usage_count > 0);
  const chartData = hasData
    ? topFeatures.map((feature, index) => ({
        ...feature,
        fill: COLORS[index % COLORS.length]
      }))
    : generateDummyData(); // 데이터가 없으면 더미 데이터 사용

  return (
    <Card className="p-6">
      <CardHeader className="pb-4">
        <CardTitle className="text-heading-md">
          {t('dashboard.top_features_title')}
        </CardTitle>
      </CardHeader>
      <CardContent>

      <div className="h-64 relative">
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={chartData}
              cx="50%"
              cy="50%"
              outerRadius={80}
              dataKey="usage_count"
              label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
              labelLine={false}
            >
              {chartData.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={entry.fill} />
              ))}
            </Pie>
            <Tooltip
              formatter={(value: number) => [value, t('dashboard.usage_count_label')]}
              contentStyle={{
                backgroundColor: 'hsl(var(--background))',
                border: '1px solid hsl(var(--border))',
                borderRadius: 'var(--radius)',
                fontSize: '14px'
              }}
            />
          </PieChart>
        </ResponsiveContainer>

        {!hasData && (
          <div className="absolute inset-0 flex items-center justify-center bg-background bg-opacity-90 rounded-lg">
            <div className="text-center text-muted-foreground">
              <div className="text-4xl mb-2">✨</div>
              <p className="text-lg font-semibold">{t('dashboard.no_data_chart_title')}</p>
              <p className="text-description mt-1">{t('dashboard.no_data_chart_desc')}</p>
            </div>
          </div>
        )}
      </div>
      </CardContent>

      {/* 하단 범례 */}
      <CardFooter className="mt-4 space-y-2">
        {chartData.map((feature) => (
          <div key={feature.id} className="flex items-center justify-between text-sm">
            <div className="flex items-center gap-2">
              <div
                className="w-3 h-3 rounded-full"
                style={{ backgroundColor: feature.fill }}
              />
              <span className="text-muted-foreground">{feature.name}</span>
            </div>
            <span className="font-medium text-foreground">{feature.usage_count}{t('common.unit_times')}</span>
          </div>
        ))}
      </CardFooter>
    </Card>
  );
} 