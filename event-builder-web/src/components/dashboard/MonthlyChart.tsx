'use client';

import React from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { useTranslation } from '@/lib/i18n';

interface MonthlyChartData {
  month: string;
  count: number;
  displayMonth: string;
}

interface MonthlyChartProps {
  data: MonthlyChartData[];
}

export default function MonthlyChart({ data }: MonthlyChartProps) {
  const { t } = useTranslation();
  return (
    <div className="bg-card rounded-lg shadow-sm border border p-6">
      <h3 className="text-heading-md mb-4">
        {t('dashboard.monthlyRfpChartTitle')}
      </h3>
      <div className="h-64">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={data}>
            <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
            <XAxis
              dataKey="displayMonth"
              tick={{ fontSize: 12 }}
              stroke="hsl(var(--muted-foreground))"
            />
            <YAxis
              tick={{ fontSize: 12 }}
              stroke="hsl(var(--muted-foreground))"
            />
            <Tooltip
              contentStyle={{
                backgroundColor: 'hsl(var(--background))',
                border: '1px solid hsl(var(--border))',
                borderRadius: 'var(--radius)',
                fontSize: '14px'
              }}
              formatter={(value: number) => [value, t('dashboard.rfpCount')]}
              labelFormatter={(label: string) => `${label}`}
            />
            <Bar
              dataKey="count"
              fill="hsl(var(--primary))"
              radius={[4, 4, 0, 0]}
              name={t('dashboard.rfpCount')}
            />
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}