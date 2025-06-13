'use client';

import React from 'react';
import { OnboardingRfp, statusColorMap, cardColorMap } from '@/lib/onboardingData';
import { CalendarIcon, DocumentTextIcon, ClockIcon } from '@heroicons/react/24/outline';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { cn } from '@/lib/design-system';

interface OnboardingRfpCardProps {
  rfp: OnboardingRfp;
  delay?: number; // 애니메이션 지연 시간 (ms)
}

const OnboardingRfpCard: React.FC<OnboardingRfpCardProps> = ({ rfp, delay = 0 }) => {
  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('ko-KR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const isUpcoming = new Date(rfp.event_date) > new Date();
  const isPast = new Date(rfp.event_date) < new Date();

  return (
    <Card
      className={cn(
        "overflow-hidden transform hover:scale-105 hover:shadow-xl transition-all duration-300 ease-in-out animate-fade-in-up",
        `animation-delay-${delay}` // Tailwind CSS JIT 컴파일러가 인식하도록 커스텀 클래스 사용
      )}
      style={{
        animationDelay: `${delay}ms`,
        animationFillMode: 'both'
      }}
    >
      {/* 컬러 헤더 */}
      <div className={cn("h-3", cardColorMap[rfp.color])} />

      <CardHeader className="pb-4">
        {/* 상태 배지 */}
        <div className="flex items-center justify-between">
          <span className={cn(
            "inline-flex items-center px-3 py-1 rounded-full text-xs font-medium",
            statusColorMap[rfp.status]
          )}>
            <div className="h-1.5 w-1.5 rounded-full bg-current mr-1.5" />
            {rfp.status}
          </span>

          {/* 날짜 표시 */}
          <div className="flex items-center text-sm text-muted-foreground">
            <CalendarIcon className="h-4 w-4 mr-1" />
            <span className={isPast ? 'line-through opacity-60' : ''}>
              {formatDate(rfp.event_date)}
            </span>
          </div>
        </div>

        {/* 제목 */}
        <CardTitle className="text-lg font-semibold text-foreground mt-3 line-clamp-2">
          {rfp.title}
        </CardTitle>

        {/* 설명 */}
        {rfp.description && (
          <CardDescription className="mt-2 text-sm text-muted-foreground line-clamp-2">
            {rfp.description}
          </CardDescription>
        )}
      </CardHeader>

      <CardContent className="pt-0">
        {/* 진행률 표시 (진행중인 경우) */}
        {rfp.status === '진행중' && (
          <div className="mt-4">
            <div className="flex items-center justify-between text-xs text-muted-foreground mb-1">
              <span>진행률</span>
              <span>75%</span>
            </div>
            <div className="w-full bg-muted rounded-full h-2">
              <div
                className="bg-primary h-2 rounded-full transition-all duration-500 ease-out"
                style={{ width: '75%' }}
              />
            </div>
          </div>
        )}
      </CardContent>

      <CardFooter className="flex items-center justify-between pt-4 border-t border">
        <div className="flex items-center text-xs text-muted-foreground">
          <DocumentTextIcon className="h-4 w-4 mr-1" />
          <span>RFP #{rfp.id.toString().padStart(3, '0')}</span>
        </div>

        {/* 시간 표시 */}
        <div className="flex items-center text-xs text-muted-foreground">
          <ClockIcon className="h-4 w-4 mr-1" />
          {isUpcoming && (
            <span className="text-green-600 font-medium">예정</span>
          )}
          {isPast && (
            <span className="text-muted-foreground">완료</span>
          )}
          {!isUpcoming && !isPast && (
            <span className="text-primary font-medium">오늘</span>
          )}
        </div>
      </CardFooter>

      {/* 호버 오버레이 */}
      <div className={cn(
        "absolute inset-0 opacity-0 hover:opacity-100 transition-opacity duration-300",
        cardColorMap[rfp.color], "bg-opacity-90 flex items-center justify-center"
      )}>
        <div className="text-white text-center">
          <DocumentTextIcon className="h-8 w-8 mx-auto mb-2" />
          <p className="text-sm font-medium">미리보기</p>
          <p className="text-xs opacity-80">클릭하여 자세히 보기</p>
        </div>
      </div>
    </Card>
  );
};

export default OnboardingRfpCard;