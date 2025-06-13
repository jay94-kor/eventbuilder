'use client';

import React from 'react';
import Link from 'next/link';
import { PlusCircleIcon, ArrowRightIcon, SparklesIcon } from '@heroicons/react/24/outline';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/design-system';

interface StartRfpButtonProps {
  className?: string;
  size?: 'default' | 'lg';
  onClick?: () => void;
}

const StartRfpButton: React.FC<StartRfpButtonProps> = ({
  className = '',
  size = 'lg',
  onClick
}) => {
  return (
    <div className="text-center py-8">
      <Link href="/rfp/create/basic-info">
        <Button
          className={cn(
            "font-bold text-lg px-10 py-6 rounded-2xl transition-all duration-500 ease-out transform hover:scale-110 hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50 group relative overflow-hidden",
            "bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600",
            "hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700",
            "text-white shadow-xl border-0",
            className
          )}
          size={size}
          onClick={onClick}
        >
          {/* 백그라운드 애니메이션 파형 */}
          <div className="absolute inset-0">
            <div className="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out" />
          </div>

          {/* 반짝이는 효과들 */}
          <div className="absolute inset-0 overflow-hidden">
            <SparklesIcon className="absolute top-2 right-4 h-4 w-4 text-yellow-300 opacity-0 group-hover:opacity-100 animate-ping transition-opacity duration-300 delay-100" />
            <SparklesIcon className="absolute bottom-3 left-6 h-3 w-3 text-yellow-200 opacity-0 group-hover:opacity-100 animate-pulse transition-opacity duration-300 delay-300" />
            <SparklesIcon className="absolute top-1/2 left-1/4 h-2 w-2 text-white opacity-0 group-hover:opacity-100 animate-bounce transition-opacity duration-300 delay-500" />
          </div>

          {/* 메인 콘텐츠 */}
          <div className="relative z-10 flex items-center">
            <div className="flex items-center justify-center w-8 h-8 bg-white/20 rounded-full mr-4 group-hover:rotate-180 transition-transform duration-500">
              <PlusCircleIcon className="h-5 w-5" />
            </div>
            
            <span className="font-bold">
              나의 첫 RFP 작성하러 가기
            </span>

            <ArrowRightIcon className="h-6 w-6 ml-4 group-hover:translate-x-2 transition-transform duration-300" />
          </div>
        </Button>
      </Link>

      {/* 매력적인 부제목 */}
      <div className="mt-6 text-center">
        <p className="text-gray-600 text-lg mb-4 max-w-md mx-auto leading-relaxed">
          🚀 <span className="font-semibold">몇 분 만에</span> 전문적인 행사 기획안을 완성하세요!
        </p>
        <p className="text-sm text-gray-500">
          블록을 선택하고 내용을 입력하기만 하면 
          <span className="font-medium text-blue-600"> AI가 맞춤 추천</span>까지!
        </p>
      </div>

      {/* 개선된 특징 카드들 */}
      <div className="mt-8 grid grid-cols-3 gap-4 max-w-md mx-auto">
        <div className="bg-gradient-to-br from-green-50 to-emerald-50 p-4 rounded-xl text-center border border-green-100 transform hover:scale-105 transition-transform duration-200">
          <div className="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full mx-auto mb-2 flex items-center justify-center">
            <span className="text-white text-sm font-bold">⚡</span>
          </div>
          <div className="text-xs font-semibold text-green-700">5분 내 완성</div>
        </div>
        
        <div className="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-xl text-center border border-blue-100 transform hover:scale-105 transition-transform duration-200">
          <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mx-auto mb-2 flex items-center justify-center">
            <span className="text-white text-sm font-bold">📋</span>
          </div>
          <div className="text-xs font-semibold text-blue-700">전문 템플릿</div>
        </div>
        
        <div className="bg-gradient-to-br from-purple-50 to-pink-50 p-4 rounded-xl text-center border border-purple-100 transform hover:scale-105 transition-transform duration-200">
          <div className="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full mx-auto mb-2 flex items-center justify-center">
            <span className="text-white text-sm font-bold">🎯</span>
          </div>
          <div className="text-xs font-semibold text-purple-700">맞춤 추천</div>
        </div>
      </div>
    </div>
  );
};

export default StartRfpButton;