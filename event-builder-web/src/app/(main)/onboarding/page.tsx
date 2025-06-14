'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuthStore } from '@/stores/authStore';
import { markUserOnboarded, updateUser } from '@/lib/api';
import { onboardingRfpData } from '@/lib/onboardingData';
import OnboardingWelcome from '@/components/dashboard/OnboardingWelcome';
import OnboardingRfpCard from '@/components/dashboard/OnboardingRfpCard';
import StartRfpButton from '@/components/dashboard/StartRfpButton';
import { Checkbox } from '@/components/ui/checkbox';
import { toast } from 'sonner';
import { useTranslation } from '@/lib/i18n'; // useTranslation 훅 import

export default function OnboardingPage() {
  const router = useRouter();
  const { user, setOnboarded, setSkipOnboarding, isLoading } = useAuthStore();
  const [isRedirecting, setIsRedirecting] = useState(false);
  const { t } = useTranslation(); // useTranslation 훅 사용
 
   useEffect(() => {
     // 사용자가 이미 온보딩되었거나 온보딩을 건너뛰기로 설정했다면 대시보드로 리다이렉트
     if (user && (user.onboarded || user.skip_onboarding)) {
      console.log('Redirecting to dashboard - onboarded:', user.onboarded, 'skip_onboarding:', user.skip_onboarding);
      setIsRedirecting(true);
      setTimeout(() => {
        router.push('/dashboard');
      }, 1000);
    }
  }, [user, router]);

  const handleSkipOnboarding = async (checked: boolean) => {
    if (!user) return;

    try {
      await updateUser({ skip_onboarding: checked });
      setSkipOnboarding(checked);
      toast.success(
        checked ? t('dashboard.skip_onboarding_confirm_title') : t('dashboard.onboarding_reset_title'),
        {
          description: checked ? t('dashboard.skip_onboarding_confirm_description') : t('dashboard.onboarding_reset_description'),
          action: {
            label: t('common.yes'),
            onClick: () => {
              if (checked) {
                router.push('/dashboard');
              }
            },
          },
          cancel: {
            label: t('common.no'),
            onClick: () => {
              // 사용자가 '아니오'를 선택하면 체크박스 상태를 되돌림
              // 이 부분은 UI 상태를 직접 조작해야 하므로, 필요하다면 useAuthStore에 해당 액션을 추가해야 합니다.
              // 현재는 단순히 토스트를 닫는 것으로 처리합니다.
            },
          },
          duration: 5000, // 사용자가 선택할 시간을 충분히 줍니다.
        }
      );
    } catch (error) {
      console.error('Failed to update skip_onboarding status:', error);
      toast.error(t('dashboard.onboarding_update_failed'));
    }
  };

  const handleStartRfp = async () => {
    if (!user) return;

    try {
      await markUserOnboarded();
      setOnboarded(true);
      toast.success(t('dashboard.onboarding_complete_success'));
      router.push('/rfp/create');
    } catch (error) {
      console.error('Failed to mark user as onboarded:', error);
      toast.error(t('dashboard.onboarding_complete_failed'));
    }
  };

  // 로딩 상태 표시
  if (isLoading) {
    return (
      <div className="container mx-auto px-4 py-8 text-center">
        <div role="status" aria-live="polite" className="bg-card/95 backdrop-blur-md border border/70 shadow-md p-8 rounded-xl max-w-md mx-auto">
          <div className="w-12 h-12 mx-auto mb-4 bg-brand-light rounded-full flex items-center justify-center">
            <div className="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
          </div>
          <p className="text-brand font-medium">{t('onboarding_page.loading_user_info')}</p>
        </div>
      </div>
    );
  }

  // 리다이렉트 중 상태 표시
  if (isRedirecting) {
    return (
      <div className="container mx-auto px-4 py-8 text-center">
        <div role="status" aria-live="polite" className="bg-card/95 backdrop-blur-md border border/70 shadow-md p-8 rounded-xl max-w-md mx-auto">
          <div className="w-12 h-12 mx-auto mb-4 bg-brand-light rounded-full flex items-center justify-center">
            <div className="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
          </div>
          <p className="text-brand font-medium">{t('onboarding_page.redirecting_to_dashboard')}</p>
        </div>
      </div>
    );
  }

  // 사용자가 없는 경우
  if (!user) {
    return (
      <div className="container mx-auto px-4 py-8 text-center">
        <div className="bg-card/95 backdrop-blur-md border border/70 shadow-md p-8 rounded-xl max-w-md mx-auto">
          <h2 className="text-xl font-semibold mb-4">{t('onboarding_page.login_required_title')}</h2>
          <p className="text-muted-foreground mb-4">{t('onboarding_page.login_required_description')}</p>
          <button
            onClick={() => router.push('/login')}
            className="bg-primary text-primary-foreground px-6 py-3 rounded-lg font-medium transition-all duration-300 ease-out hover:bg-primary/90 hover:shadow-brand"
          >
            {t('onboarding_page.login_button')}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <OnboardingWelcome userName={user?.name || t('common.new_user')} />

      <section className="my-8">
        <h2 className="text-2xl font-bold text-foreground mb-4">{t('onboarding_page.main_features_title')}</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div className="bg-card p-6 rounded-lg shadow-md">
            <h3 className="text-xl font-semibold mb-2">{t('onboarding_page.easy_rfp_creation_title')}</h3>
            <p className="text-muted-foreground">{t('onboarding_page.easy_rfp_creation_description')}</p>
          </div>
          <div className="bg-card p-6 rounded-lg shadow-md">
            <h3 className="text-xl font-semibold mb-2">{t('onboarding_page.custom_feature_recommendation_title')}</h3>
            <p className="text-muted-foreground">{t('onboarding_page.custom_feature_recommendation_description')}</p>
          </div>
          <div className="bg-card p-6 rounded-lg shadow-md">
            <h3 className="text-xl font-semibold mb-2">{t('onboarding_page.budget_management_and_statistics_title')}</h3>
            <p className="text-muted-foreground">{t('onboarding_page.budget_management_and_statistics_description')}</p>
          </div>
        </div>
      </section>

      <section className="my-8">
        <h2 className="text-2xl font-bold text-foreground mb-4">{t('onboarding_page.example_rfp_list_title')}</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {onboardingRfpData.map((rfp, index) => (
            <OnboardingRfpCard key={index} rfp={rfp} />
          ))}
        </div>
      </section>

      <section className="my-8 text-center">
        <h2 className="text-2xl font-bold text-foreground mb-4">{t('onboarding_page.start_now_title')}</h2>
        <StartRfpButton onClick={handleStartRfp} />
        <div className="mt-4 flex items-center justify-center space-x-2">
          <Checkbox
            id="skip-onboarding"
            checked={user?.skip_onboarding || false}
            onCheckedChange={(checked) => handleSkipOnboarding(checked as boolean)}
          />
          <label
            htmlFor="skip-onboarding"
            className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
          >
            {t('onboarding_page.do_not_show_again')}
          </label>
        </div>
      </section>

      {/* 디버깅 정보 (개발 환경에서만 표시) */}
      {process.env.NODE_ENV === 'development' && (
        <div className="mt-8 p-4 bg-gray-100 rounded-lg text-sm">
          <h3 className="font-semibold mb-2">{t('onboarding_page.debugging_info_title')}</h3>
          <p>{t('onboarding_page.user_id')} {user?.id}</p>
          <p>{t('onboarding_page.user_name')} {user?.name}</p>
          <p>{t('onboarding_page.onboarding_completed')} {user?.onboarded ? t('onboarding_page.yes') : t('onboarding_page.no')}</p>
          <p>{t('onboarding_page.onboarding_skipped')} {user?.skip_onboarding ? t('onboarding_page.yes') : t('onboarding_page.no')}</p>
        </div>
      )}
    </div>
  );
}