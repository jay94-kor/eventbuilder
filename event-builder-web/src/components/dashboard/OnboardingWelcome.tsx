'use client';

import React from 'react';
import {
  SparklesIcon,
  PresentationChartBarIcon,
  CalendarDaysIcon,
  ChartBarIcon
} from '@heroicons/react/24/outline';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/lib/i18n';

interface OnboardingWelcomeProps {
  userName?: string;
}

const OnboardingWelcome: React.FC<OnboardingWelcomeProps> = ({ userName = '사용자' }) => {
  const t = useTranslation();

  const features = [
    {
      icon: PresentationChartBarIcon,
      title: t('dashboard.easy_rfp_title'),
      description: t('dashboard.easy_rfp_description'),
      gradient: 'from-blue-500 to-cyan-500',
      bgGradient: 'from-blue-50 to-cyan-50'
    },
    {
      icon: CalendarDaysIcon,
      title: t('dashboard.systematic_schedule_title'),
      description: t('dashboard.systematic_schedule_description'),
      gradient: 'from-green-500 to-emerald-500',
      bgGradient: 'from-green-50 to-emerald-50'
    },
    {
      icon: ChartBarIcon,
      title: t('dashboard.detailed_analytics_title'),
      description: t('dashboard.detailed_analytics_description'),
      gradient: 'from-purple-500 to-pink-500',
      bgGradient: 'from-purple-50 to-pink-50'
    }
  ];

  return (
    <div className="relative overflow-hidden">
      <div className="absolute inset-0 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-3xl"></div>
      <div className="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full opacity-10 transform translate-x-16 -translate-y-16"></div>
      <div className="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-purple-400 to-pink-500 rounded-full opacity-10 transform -translate-x-8 translate-y-8"></div>
      
      <Card className="relative p-8 mb-8 border-0 shadow-2xl bg-white/80 backdrop-blur-sm">
        <CardHeader className="text-center mb-8">
          <div className="flex justify-center mb-6">
            <div className="relative">
              <div className="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3">
                <SparklesIcon className="h-10 w-10 text-white" />
              </div>
              <div className="absolute -top-2 -right-2 h-8 w-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-md animate-bounce">
                <span className="text-white text-xs font-bold">✨</span>
              </div>
            </div>
          </div>
          
          <CardTitle className="text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">
            {t('dashboard.welcome_message', { userName: userName })} 🎉
          </CardTitle>
          
          <CardDescription className="text-gray-600 text-xl max-w-2xl mx-auto leading-relaxed">
            {t('dashboard.welcome_description')}
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
            {features.map((feature, index) => (
              <Card
                key={index}
                className={`relative overflow-hidden text-center p-8 bg-gradient-to-br ${feature.bgGradient} border-0 shadow-lg hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 group`}
              >
                <CardContent className="p-0">
                  <div className="flex justify-center mb-6">
                    <div className={`w-16 h-16 bg-gradient-to-br ${feature.gradient} rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300`}>
                      <feature.icon className="h-8 w-8 text-white" />
                    </div>
                  </div>
                  <CardTitle className="text-xl font-bold text-gray-900 mb-3">
                    {feature.title}
                  </CardTitle>
                  <CardDescription className="text-gray-600 leading-relaxed">
                    {feature.description}
                  </CardDescription>
                </CardContent>
                
                <div className="absolute top-0 right-0 w-20 h-20 bg-white/20 rounded-full -mr-10 -mt-10"></div>
              </Card>
            ))}
          </div>

          <Card className="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-600 border-0 text-center shadow-xl">
            <CardContent className="relative p-8">
              <div className="flex justify-center mb-4">
                <div className="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                  <span className="text-white text-2xl">💡</span>
                </div>
              </div>
              <h4 className="text-2xl font-bold text-white mb-3">
                {t('dashboard.start_half_message_title')}
              </h4>
              <p className="text-blue-100 text-lg max-w-2xl mx-auto leading-relaxed">
                {t('dashboard.start_half_message_description')}
              </p>
            </CardContent>
          </Card>
        </CardContent>
      </Card>
    </div>
  );
};

export default OnboardingWelcome; 