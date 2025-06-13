'use client';

import React, { useState, useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import ProfileForm from '@/components/profile/ProfileForm';
import { useRouter } from 'next/navigation'; // useRouter 임포트

import { useTranslation } from '@/lib/i18n'; // useTranslation 훅 import

export default function ProfilePage() {
  const { user, isLoading, error, getUser, isAuthenticated, isInitialLoading } = useAuth(); // isInitialLoading 추가
  const [isEditing, setIsEditing] = useState(false);
  const router = useRouter();
  const t = useTranslation(); // useTranslation 훅 사용

  useEffect(() => {
    // 토큰은 있지만 user 정보가 아직 로드되지 않은 경우 getUser 호출
    if (isAuthenticated && !user && !isLoading && isInitialLoading) {
      getUser();
    }
    // 인증되지 않았거나, 초기 로딩이 완료되었는데 user가 없는 경우 로그인 페이지로 리다이렉트
    if (!isAuthenticated && !isInitialLoading && !isLoading) {
      router.push('/login');
    }
  }, [user, isLoading, getUser, isAuthenticated, isInitialLoading, router]);

  if (isLoading || isInitialLoading || !isAuthenticated) { // 초기 로딩 및 인증 상태 추가
    return (
      <div className="flex justify-center items-center min-h-screen bg-background">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        <span className="ml-4 text-muted-foreground">{t('common.loading')}</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className="max-w-4xl mx-auto p-8 bg-card shadow-md rounded-lg mt-8">
        <h1 className="text-2xl font-bold text-red-600 mb-4">{t('common.error_occurred')}</h1>
        <p className="text-muted-foreground">{t('profile.fetch_user_info_failed')}{error}</p>
        <button
          onClick={() => getUser()}
          className="mt-4 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90"
        >
          {t('common.retry_button')}
        </button>
      </div>
    );
  }

  // user 객체가 없으면 (위에서 isLoading, error 처리 후) 로그인 페이지로 리다이렉트
  if (!user) {
    // 이 경우는 사실상 위의 !isAuthenticated 조건에서 처리되지만, 혹시 모를 상황 대비
    router.push('/login');
    return null; // 리다이렉트 중에는 아무것도 렌더링하지 않음
  }

  return (
    <div className="max-w-4xl mx-auto p-8 bg-card shadow-md rounded-lg mt-8">
      <h1 className="text-heading-xl mb-6">{t('profile.title')}</h1>

      {!isEditing ? (
        <div className="space-y-4">
          <div className="flex items-center">
            <label className="w-24 text-label">{t('profile.name_label')}</label>
            <span className="text-foreground text-lg">{user.name}</span>
          </div>
          <div className="flex items-center">
            <label className="w-24 text-label">{t('profile.email_label')}</label>
            <span className="text-foreground text-lg">{user.email}</span>
          </div>
          <div className="flex items-center">
            <label className="w-24 text-label">{t('profile.joined_date_label')}</label>
            <span className="text-foreground text-lg">
              {new Date(user.created_at).toLocaleDateString('ko-KR')}
            </span>
          </div>
          <button
            onClick={() => setIsEditing(true)}
            className="mt-6 px-6 py-3 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
          >
            {t('profile.edit_profile_button')}
          </button>
        </div>
      ) : (
        <ProfileForm user={user} onCancel={() => setIsEditing(false)} />
      )}
    </div>
  );
}