'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation'; // useRouter 임포트
import { useAuth } from '@/hooks/useAuth';
import { fetchMyRfps, fetchDashboardStats, Rfp, DashboardStats, markUserOnboarded } from '@/lib/api';
import { StatsCard, MonthlyRfpChart, TopFeaturesChart } from '@/components/dashboard';

// 온보딩 관련 컴포넌트 import
import OnboardingRfpCard from '@/components/dashboard/OnboardingRfpCard';
import StartRfpButton from '@/components/dashboard/StartRfpButton';
import { onboardingRfpData } from '@/lib/onboardingData';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import { useTranslation } from '@/lib/i18n'; // useTranslation 훅 import

export default function DashboardPage() {
  const router = useRouter(); // useRouter 훅 호출
  const { user, updateUser } = useAuth(); // setOnboarded 제거, getUser 제거
  const { t } = useTranslation();

  const [rfps, setRfps] = useState<Rfp[]>([]);
  const [dashboardStats, setDashboardStats] = useState<DashboardStats | null>(null);
  const [rfpsLoading, setRfpsLoading] = useState(true);
  const [statsLoading, setStatsLoading] = useState(true);
  const [rfpsError, setRfpsError] = useState<string | null>(null);
  const [statsError, setStatsError] = useState<string | null>(null);

  // 온보딩 토글 상태 관리
  const [showOnboarding, setShowOnboarding] = useState(() => {
    // 초기값: 사용자가 온보딩을 완료하지 않았고 건너뛰지도 않았을 때 true
    return user && !user.onboarded && !user.skip_onboarding;
  });

  // user 상태가 변경될 때 온보딩 상태 업데이트
  useEffect(() => {
    if (user) {
      setShowOnboarding(!user.onboarded && !user.skip_onboarding);
    }
  }, [user?.onboarded, user?.skip_onboarding]);

  // 대시보드 데이터 로드 (통계 및 RFP 목록)
  useEffect(() => {
    let isMounted = true; // cleanup을 위한 플래그
    
    const loadDashboardData = async () => {
      if (!user || !isMounted) return;

      setRfpsLoading(true);
      setStatsLoading(true);
      setRfpsError(null);
      setStatsError(null);

      try {
        console.log('Dashboard: Fetching dashboard stats and RFPs...');
        const startTime = performance.now();

        const [statsResponse, rfpsResponse] = await Promise.all([
          fetchDashboardStats(),
          fetchMyRfps()
        ]);

        const endTime = performance.now();
        console.log(`Dashboard: API calls completed in ${endTime - startTime} ms`);

        if (!isMounted) return; // 컴포넌트가 언마운트되었으면 상태 업데이트 중단

        // 통계 데이터 처리
        if (statsResponse.success) {
          setDashboardStats(statsResponse.data);
        } else {
          setStatsError(statsResponse.message || t('dashboard.stats_load_error'));
        }

        // RFP 목록 처리
        if (rfpsResponse.success) {
          setRfps(rfpsResponse.data);
          // RFP가 하나라도 있으면 온보딩 완료 처리 (단, user 상태 업데이트는 하지 않음)
          if (rfpsResponse.data.length > 0 && !user.onboarded) {
            console.log('Dashboard: Marking user as onboarded due to existing RFPs.');
            markUserOnboarded().catch(err => console.error('Failed to mark user as onboarded:', err));
          }
        } else {
          setRfpsError(rfpsResponse.message || t('dashboard.rfp_list_load_error'));
        }

      } catch (err: unknown) {
        console.error('Dashboard: Failed to load dashboard data:', err);
        if (isMounted) {
          setRfpsError(t('dashboard.data_fetch_error'));
          setStatsError(t('dashboard.data_fetch_error'));
        }
      } finally {
        if (isMounted) {
          setRfpsLoading(false);
          setStatsLoading(false);
          console.log('Dashboard: Loading finished.');
        }
      }
    };

    // user가 있을 때만 실행
    if (user) {
      loadDashboardData();
    }

    // cleanup 함수
    return () => {
      isMounted = false;
    };
  }, [user?.id, t]); // user.id와 t 함수만 의존성으로 사용

  const handleSkipOnboarding = async () => {
    if (user) {
      try {
        // skip_onboarding 상태 업데이트
        await updateUser({ skip_onboarding: true });
        // onboarded 상태 업데이트 (별도의 API 호출)
        await markUserOnboarded();
        // 새로고침 대신 대시보드로 이동
        router.push('/dashboard');
      } catch (error) {
        console.error('온보딩 상태 업데이트 실패:', error);
        // 오류 처리 (예: 사용자에게 메시지 표시)
      }
    }
  };

  // 유틸리티 함수들
  const formatDate = (dateString?: string) => {
    if (!dateString) return t('common.date_undetermined');
    return new Date(dateString).toLocaleDateString('ko-KR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'draft':
        return t('common.status_draft');
      case 'completed':
        return t('common.status_completed');
      case 'archived':
        return t('common.status_archived');
      default:
        return t('common.status_unknown');
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'draft':
        return 'bg-yellow-100 text-yellow-800';
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'archived':
        return 'bg-muted text-muted-foreground';
      default:
        return 'bg-muted text-muted-foreground';
    }
  };

  // 완료율 계산
  const completionRate = dashboardStats && dashboardStats.total_rfps > 0
    ? Math.round((dashboardStats.completed_rfps / dashboardStats.total_rfps) * 100)
    : 0;

  // 공통 스켈레톤 UI 컴포넌트
  const CommonSkeleton = () => (
    <div className="min-h-screen bg-background">
      {/* 헤더 스켈레톤 */}
      <header className="bg-card shadow">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-4">
            <div className="h-6 bg-muted rounded w-24 animate-pulse"></div>
            <div className="flex items-center space-x-4">
              <div className="h-4 bg-muted rounded w-32 animate-pulse"></div>
              <div className="h-8 bg-muted rounded w-16 animate-pulse"></div>
            </div>
          </div>
        </div>
      </header>

      {/* 메인 콘텐츠 스켈레톤 */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <div className="h-8 bg-muted rounded w-48 mb-6 animate-pulse"></div>
          
          {/* 카드들 스켈레톤 */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(6)].map((_, index) => (
              <div key={index} className="bg-card p-6 rounded-lg shadow animate-pulse">
                <div className="h-4 bg-muted rounded w-3/4 mb-4"></div>
                <div className="h-20 bg-muted rounded mb-4"></div>
                <div className="h-4 bg-muted rounded w-1/2"></div>
              </div>
            ))}
          </div>
        </div>
      </main>
    </div>
  );

  // 로딩 중이면 공통 스켈레톤 UI 표시
  if (statsLoading) {
    return <CommonSkeleton />;
  }

  return (
    <div className="min-h-screen bg-background">

      {/* 메인 콘텐츠 - 조건부 렌더링 */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* 온보딩/대시보드 토글 버튼 */}
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-2xl font-bold text-foreground">
            {showOnboarding ? t('dashboard.onboarding_toggle_onboarding') : t('dashboard.onboarding_toggle_dashboard')}
          </h1>
          <div className="flex items-center gap-4">
            {/* 온보딩 토글 스위치 */}
            <div className="flex items-center gap-3">
              <span className={`text-sm font-medium transition-colors ${!showOnboarding ? 'text-foreground' : 'text-muted-foreground'}`}>
                {t('dashboard.onboarding_toggle_dashboard')}
              </span>
              <button
                onClick={() => setShowOnboarding(!showOnboarding)}
                role="switch"
                aria-checked={showOnboarding}
                aria-label={showOnboarding ? t('dashboard.onboarding_toggle_onboarding') : t('dashboard.onboarding_toggle_dashboard')}
                className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 ${
                  showOnboarding ? 'bg-blue-600' : 'bg-gray-200'
                }`}
              >
                <span
                  className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
                    showOnboarding ? 'translate-x-6' : 'translate-x-1'
                  }`}
                />
              </button>
              <span className={`text-sm font-medium transition-colors ${showOnboarding ? 'text-foreground' : 'text-muted-foreground'}`}>
                {t('dashboard.onboarding_toggle_onboarding')}
              </span>
            </div>
          </div>
        </div>

        {/* 에러 상태 표시 */}
        {statsError && (
          <div role="alert" aria-live="assertive" className="bg-destructive/10 border border-destructive/20 rounded-md p-4 mb-6">
            <div className="flex">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <p className="text-sm text-destructive">{statsError}</p>
              </div>
            </div>
          </div>
        )}

        {/* 조건부 렌더링: 신규 사용자 vs 기존 사용자 */}
        {showOnboarding ? (
          /* ========== 신규 사용자 온보딩 화면 ========== */
          <div className="space-y-8">
            {/* 온보딩 닫기 버튼 - 상단 우측에 더 눈에 잘 띄게 */}
            <div className="flex justify-end mb-4">
              <button
                onClick={handleSkipOnboarding}
                className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200 shadow-sm"
              >
                <span>{t('dashboard.onboarding_skip_button')}</span>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            {/* 환영 메시지 */}
            <div className="relative overflow-hidden mb-8">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-3xl"></div>
              <div className="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full opacity-10 transform translate-x-16 -translate-y-16"></div>
              <div className="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-purple-400 to-pink-500 rounded-full opacity-10 transform -translate-x-8 translate-y-8"></div>
              
              <Card className="relative p-8 border-0 shadow-2xl bg-white/80 backdrop-blur-sm">
                <CardHeader className="text-center mb-8">
                  <div className="flex justify-center mb-6">
                    <div className="relative">
                      <div className="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3">
                        <span className="text-white text-3xl">✨</span>
                      </div>
                      <div className="absolute -top-2 -right-2 h-8 w-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-md animate-bounce">
                        <span className="text-white text-xs font-bold">🎉</span>
                      </div>
                    </div>
                  </div>
                  
                  <CardTitle className="text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">
                    {t('dashboard.onboarding_welcome_title', { userName: user?.name || t('common.user') })}
                  </CardTitle>
                  
                  <CardDescription className="text-gray-600 text-xl max-w-2xl mx-auto leading-relaxed">
                    {t('dashboard.onboarding_welcome_description')}
                  </CardDescription>
                </CardHeader>

                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                    <Card className="relative overflow-hidden text-center p-8 bg-gradient-to-br from-blue-50 to-cyan-50 border-0 shadow-lg hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 group">
                      <CardContent className="p-0">
                        <div className="flex justify-center mb-6">
                          <div className="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span className="text-white text-2xl">📋</span>
                          </div>
                        </div>
                        <CardTitle className="text-xl font-bold text-gray-900 mb-3">
                          {t('dashboard.onboarding_easy_rfp_title')}
                        </CardTitle>
                        <CardDescription className="text-gray-600 leading-relaxed">
                          {t('dashboard.onboarding_easy_rfp_description')}
                        </CardDescription>
                      </CardContent>
                      <div className="absolute top-0 right-0 w-20 h-20 bg-white/20 rounded-full -mr-10 -mt-10"></div>
                    </Card>

                    <Card className="relative overflow-hidden text-center p-8 bg-gradient-to-br from-green-50 to-emerald-50 border-0 shadow-lg hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 group">
                      <CardContent className="p-0">
                        <div className="flex justify-center mb-6">
                          <div className="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span className="text-white text-2xl">📅</span>
                          </div>
                        </div>
                        <CardTitle className="text-xl font-bold text-gray-900 mb-3">
                          {t('dashboard.onboarding_systematic_schedule_title')}
                        </CardTitle>
                        <CardDescription className="text-gray-600 leading-relaxed">
                          {t('dashboard.onboarding_systematic_schedule_description')}
                        </CardDescription>
                      </CardContent>
                      <div className="absolute top-0 right-0 w-20 h-20 bg-white/20 rounded-full -mr-10 -mt-10"></div>
                    </Card>

                    <Card className="relative overflow-hidden text-center p-8 bg-gradient-to-br from-purple-50 to-pink-50 border-0 shadow-lg hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 group">
                      <CardContent className="p-0">
                        <div className="flex justify-center mb-6">
                          <div className="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span className="text-white text-2xl">📊</span>
                          </div>
                        </div>
                        <CardTitle className="text-xl font-bold text-gray-900 mb-3">
                          {t('dashboard.onboarding_detailed_analysis_title')}
                        </CardTitle>
                        <CardDescription className="text-gray-600 leading-relaxed">
                          {t('dashboard.onboarding_detailed_analysis_description')}
                        </CardDescription>
                      </CardContent>
                      <div className="absolute top-0 right-0 w-20 h-20 bg-white/20 rounded-full -mr-10 -mt-10"></div>
                    </Card>
                  </div>

                  <Card className="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-600 border-0 text-center shadow-xl">
                    <CardContent className="relative p-8">
                      <div className="flex justify-center mb-4">
                        <div className="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                          <span className="text-white text-2xl">💡</span>
                        </div>
                      </div>
                      <h4 className="text-2xl font-bold text-white mb-3">
                        {t('dashboard.onboarding_start_now_title')}
                      </h4>
                      <p className="text-blue-100 text-lg max-w-2xl mx-auto leading-relaxed">
                        {t('dashboard.onboarding_start_now_description')}
                      </p>
                    </CardContent>
                  </Card>
                </CardContent>
              </Card>
            </div>

            {/* 예시 RFP 카드들 */}
            <div>
              <h2 className="text-heading-lg mb-6 text-center">
                {t('dashboard.onboarding_examples_title')}
              </h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                {onboardingRfpData.map((rfp, index) => (
                  <div 
                    key={rfp.id} 
                    className="relative cursor-default"
                    title={t('dashboard.onboarding_example_card_title')}
                  >
                    <OnboardingRfpCard 
                      rfp={rfp} 
                      delay={index * 100} // 순차적 애니메이션
                    />
                    {/* 예시 표시 오버레이 */}
                    <div className="absolute top-3 right-3 bg-yellow-400 text-yellow-800 text-xs font-bold px-2 py-1 rounded-full">
                      {t('dashboard.onboarding_example_badge')}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* CTA 버튼 */}
            <div className="text-center">
              <StartRfpButton />
            </div>

            {/* 추가 정보 섹션 */}
            <div className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8 text-center">
              <h3 className="text-xl font-semibold text-foreground mb-4">
                {t('dashboard.onboarding_capabilities_title')}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm text-muted-foreground">
                <div>
                  <div className="text-2xl mb-2">📝</div>
                  <h4 className="font-medium mb-1">{t('dashboard.onboarding_easy_planning_title')}</h4>
                  <p>{t('dashboard.onboarding_easy_planning_desc')}</p>
                </div>
                <div>
                  <div className="text-2xl mb-2">🎯</div>
                  <h4 className="font-medium mb-1">{t('dashboard.onboarding_custom_recommendations_title')}</h4>
                  <p>{t('dashboard.onboarding_custom_recommendations_desc')}</p>
                </div>
                <div>
                  <div className="text-2xl mb-2">📊</div>
                  <h4 className="font-medium mb-1">{t('dashboard.onboarding_systematic_management_title')}</h4>
                  <p>{t('dashboard.onboarding_systematic_management_desc')}</p>
                </div>
              </div>
            </div>
          </div>
        ) : (
          /* ========== 기존 사용자 대시보드 화면 ========== */
          <>
            {/* 통계 섹션 */}
            <div className="mb-8">
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-heading-lg">{t('dashboard.overview_title')}</h2>
              </div>
              
              {/* 통계 카드들 - 첫 번째 줄 */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {dashboardStats && (
                  <>
                    <StatsCard
                      title={t('dashboard.total_rfps_title')}
                      value={dashboardStats.total_rfps}
                      icon="📋"
                      color="text-blue-600"
                      bgColor="bg-blue-50"
                      description={t('dashboard.total_rfps_desc')}
                    />
                    <StatsCard
                      title={t('dashboard.completed_rfps_title')}
                      value={dashboardStats.completed_rfps}
                      icon="✅"
                      color="text-green-600"
                      bgColor="bg-green-50"
                      description={t('dashboard.completed_rfps_desc')}
                    />
                    <StatsCard
                      title={t('dashboard.completion_rate_title')}
                      value={`${completionRate}%`}
                      icon="📊"
                      color="text-purple-600"
                      bgColor="bg-purple-50"
                      description={t('dashboard.completion_rate_desc')}
                    />
                    <StatsCard
                      title={t('dashboard.this_month_created_title')}
                      value={Object.values(dashboardStats.monthly_rfp_counts).reduce((sum, count) => sum + count, 0)}
                      icon="📅"
                      color="text-orange-600"
                      bgColor="bg-orange-50"
                      description={t('dashboard.this_month_created_desc')}
                    />
                  </>
                )}
              </div>

              {/* 차트들 - 두 번째 줄 */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                {/* 월별 RFP 생성 차트 */}
                <div className="bg-card p-6 rounded-lg shadow">
                  <h3 className="text-lg font-medium text-foreground mb-4">{t('dashboard.monthly_rfp_trend_title')}</h3>
                  {dashboardStats ? (
                    <MonthlyRfpChart monthlyRfpCounts={dashboardStats.monthly_rfp_counts} />
                  ) : (
                    <div className="h-64 flex items-center justify-center text-muted-foreground">
                      {t('common.chart_load_error')}
                    </div>
                  )}
                </div>

                {/* 인기 Feature 차트 */}
                <div className="bg-card p-6 rounded-lg shadow">
                  <h3 className="text-lg font-medium text-foreground mb-4">{t('dashboard.top_features_title')}</h3>
                  {dashboardStats ? (
                    <TopFeaturesChart topFeatures={dashboardStats.top_features} />
                  ) : (
                    <div className="h-64 flex items-center justify-center text-muted-foreground">
                      {t('common.chart_load_error')}
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* RFP 목록 섹션 */}
            <div className="mb-6 flex justify-between items-center">
              <h2 className="text-heading-lg">{t('dashboard.my_rfp_list_title')}</h2>
              <Link
                href="/rfp/create"
                className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
              >
                {t('dashboard.create_new_rfp_button')}
              </Link>
            </div>

            {/* RFP 목록 로딩 상태 */}
            {rfpsLoading && (
              <div className="flex justify-center items-center py-12">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <span className="ml-2 text-muted-foreground">{t('dashboard.rfp_list_loading')}</span>
              </div>
            )}

            {/* RFP 목록 에러 상태 */}
            {rfpsError && (
              <div role="alert" aria-live="assertive" className="bg-destructive/10 border border-destructive/20 rounded-md p-4 mb-6">
                <div className="flex">
                  <div className="flex-shrink-0">
                    <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                    </svg>
                  </div>
                  <div className="ml-3">
                    <p className="text-sm text-destructive">{rfpsError}</p>
                  </div>
                </div>
              </div>
            )}

            {/* RFP 목록 */}
            {!rfpsLoading && !rfpsError && (
              <>
                {rfps.length === 0 ? (
                  <div className="text-center py-12">
                    <svg
                      className="mx-auto h-12 w-12 text-muted-foreground"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                      />
                    </svg>
                    <h3 className="mt-2 text-sm font-medium text-foreground">
                      {t('dashboard.no_rfps_title')}
                    </h3>
                    <p className="mt-1 text-description">
                      {t('dashboard.no_rfps_desc')}
                    </p>
                    <div className="mt-6">
                      <Link
                        href="/rfp/create"
                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
                      >
                        {t('dashboard.create_rfp_button')}
                      </Link>
                    </div>
                  </div>
                ) : (
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {rfps.map((rfp) => (
                      <Link href={`/rfp/${rfp.id}`} key={rfp.id}>
                        <div
                          className="bg-card h-full overflow-hidden shadow rounded-lg hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col"
                        >
                          <div className="p-6 flex-grow">
                            <div className="flex items-start justify-between mb-4">
                              <h3 className="text-lg font-semibold text-foreground truncate pr-4">
                                {rfp.title}
                              </h3>
                              <span
                                className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(
                                  rfp.status
                                )}`}
                              >
                                {getStatusText(rfp.status)}
                              </span>
                            </div>
                            
                            <div className="space-y-2 text-sm text-muted-foreground">
                              <div className="flex items-center">
                                <svg className="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{formatDate(rfp.event_date)}</span>
                              </div>
                              <div className="flex items-center">
                                <svg className="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>{rfp.selections?.length || 0}{t('dashboard.rfp_item_count_suffix')}</span>
                              </div>
                            </div>
                          </div>
                          <div className="bg-muted px-6 py-3 text-xs text-muted-foreground">
                            {t('dashboard.last_updated_prefix')}{formatDate(rfp.updated_at)}
                          </div>
                        </div>
                      </Link>
                    ))}
                  </div>
                )}
              </>
            )}
          </>
        )}
      </main>
    </div>
  );
}