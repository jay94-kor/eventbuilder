'use client';

import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/design-system';
import { useTranslation } from '@/lib/i18n';

interface StatsCardProps {
  title: string;
  value: number | string;
  icon?: React.ReactNode;
  color?: string;
  bgColor?: string;
  description?: string;
  trend?: {
    value: number;
    isPositive: boolean;
  };
}

export default function StatsCard({
  title,
  value,
  icon,
  color = "text-blue-600",
  bgColor = "bg-blue-50",
  description,
  trend
}: StatsCardProps) {
  // 컬러에 따른 그라데이션 매핑
  const getGradientClasses = (color: string) => {
    if (color.includes('blue')) return 'from-blue-500 to-blue-600';
    if (color.includes('green')) return 'from-green-500 to-green-600';
    if (color.includes('purple')) return 'from-purple-500 to-purple-600';
    if (color.includes('orange')) return 'from-orange-500 to-orange-600';
    return 'from-blue-500 to-blue-600';
  };

  const getBgGradientClasses = (bgColor: string) => {
    if (bgColor.includes('blue')) return 'from-blue-50 to-blue-100';
    if (bgColor.includes('green')) return 'from-green-50 to-green-100';
    if (bgColor.includes('purple')) return 'from-purple-50 to-purple-100';
    if (bgColor.includes('orange')) return 'from-orange-50 to-orange-100';
    return 'from-blue-50 to-blue-100';
  };

  const { t } = useTranslation();

  return (
    <Card className="relative overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-0 shadow-lg">
      {/* 배경 그라데이션 */}
      <div className={`absolute inset-0 bg-gradient-to-br ${getBgGradientClasses(bgColor)} opacity-50`}></div>
      
      {/* 장식적 요소 */}
      <div className="absolute top-0 right-0 w-20 h-20 bg-white/30 rounded-full -mr-10 -mt-10"></div>
      <div className="absolute bottom-0 left-0 w-16 h-16 bg-white/20 rounded-full -ml-8 -mb-8"></div>
      
      <CardHeader className="relative flex flex-row items-center justify-between space-y-0 pb-3">
        <CardTitle className="text-sm font-semibold text-gray-700">{t(title)}</CardTitle>
        {icon && (
          <div className={`flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br ${getGradientClasses(color)} shadow-lg`}>
            <div className="text-white text-lg">
              {icon}
            </div>
          </div>
        )}
      </CardHeader>
      
      <CardContent className="relative">
        <div className="text-4xl font-bold text-gray-900 mb-2">{value}</div>
        
        {description && (
          <CardDescription className="text-gray-600 text-sm leading-relaxed">{t(description)}</CardDescription>
        )}
        
        {trend && (
          <div className={cn(
            "mt-3 flex items-center gap-2 text-sm font-medium px-3 py-1 rounded-full w-fit",
            trend.isPositive
              ? 'text-green-700 bg-green-100 border border-green-200'
              : 'text-red-700 bg-red-100 border border-red-200'
          )}>
            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              {trend.isPositive ? (
                <path fillRule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clipRule="evenodd" />
              ) : (
                <path fillRule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clipRule="evenodd" />
              )}
            </svg>
            {trend.isPositive ? '+' : ''}{trend.value}%
          </div>
        )}
      </CardContent>
    </Card>
  );
}