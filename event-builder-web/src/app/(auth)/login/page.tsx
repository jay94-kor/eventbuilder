'use client';

import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useRouter, useSearchParams } from 'next/navigation';
import Link from 'next/link';
import { loginUser, type LoginData } from '@/lib/api';
import { useTranslation } from '@/lib/i18n';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'sonner';
import { useAuthStore } from '@/stores/authStore'; // useAuthStore 임포트

const baseLoginSchema = z.object({
  email: z.string(),
  password: z.string(),
});

type LoginFormData = z.infer<typeof baseLoginSchema>;

export default function LoginPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [isLoading, setIsLoading] = useState(false);
  const { t } = useTranslation();
  const { setToken, getUser } = useAuthStore(); // setToken, getUser 액션 가져오기

  // URL에서 리다이렉트 경로 가져오기
  const redirectTo = searchParams.get('redirect') || '/dashboard';

  // 번역된 메시지를 변수에 저장
  const emailRequiredMessage = t('auth.login.email_required');
  const emailInvalidMessage = t('auth.login.email_invalid');
  const passwordRequiredMessage = t('auth.login.password_required');

  // Zod 스키마 정의 (번역된 메시지 변수 사용)
  const loginSchema = baseLoginSchema.extend({
    email: baseLoginSchema.shape.email
      .min(1, emailRequiredMessage)
      .email(emailInvalidMessage),
    password: baseLoginSchema.shape.password
      .min(1, passwordRequiredMessage)
  });

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema) // useMemo로 생성된 스키마 전달
  });

  const onSubmit = async (data: LoginFormData) => {
    setIsLoading(true);

    try {
      const response = await loginUser(data as LoginData);

      // Zustand 스토어에 토큰 설정
      setToken(response.data.token);

      // 사용자 정보 가져오기 (onboarded 상태 포함)
      await getUser(); // getUser는 user 객체를 스토어에 업데이트

      toast.success(t('auth.login.success_message'), {
        duration: 2000,
      });

      // 2초 후 원래 페이지 또는 대시보드로 이동
      setTimeout(() => {
        router.push(redirectTo);
      }, 2000);

    } catch (error: unknown) {
      console.error('Login error:', error);

      // API 오류 메시지 추출
      let errorMessage = t('auth.login.error_message');

      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { data?: { message?: string } } };
        if (axiosError.response?.data?.message) {
          errorMessage = axiosError.response.data.message;
        }
      }

      toast.error(errorMessage, {
        duration: 5000,
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 px-4">
      <div className="max-w-md w-full space-y-8">
        <Card className="border-0 shadow-2xl bg-white/95 backdrop-blur-sm">
          <CardHeader className="text-center space-y-4 pb-8">
            <div className="mx-auto w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4">
              <span className="text-white text-2xl">🔐</span>
            </div>
            <CardTitle className="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              {t('auth.login.title')}
            </CardTitle>
            <CardDescription className="text-lg text-gray-600">
              {t('auth.login.subtitle')}
            </CardDescription>
          </CardHeader>

          <CardContent className="space-y-6">
            {/* 로그인 폼 */}
            <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            {/* 이메일 필드 */}
            <div className="space-y-2">
              <label htmlFor="email" className="block text-sm font-semibold text-gray-700 flex items-center">
                <span className="mr-2">📧</span>
                {t('auth.login.email_label')}
              </label>
              <Input
                {...register('email')}
                type="email"
                id="email"
                aria-invalid={errors.email ? "true" : undefined}
                aria-describedby={errors.email ? "email-error" : undefined}
                className={`h-12 border-2 transition-all duration-300 ${
                  errors.email
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50/50'
                    : 'border-gray-200 focus:border-blue-500 focus:ring-blue-500/20 hover:border-gray-300'
                }`}
                placeholder={t('auth.login.email_placeholder')}
                disabled={isLoading}
              />
              {errors.email && (
                <p id="email-error" className="mt-2 text-sm text-red-600 flex items-center">
                  <span className="mr-1">⚠️</span>
                  {errors.email.message}
                </p>
              )}
            </div>

            {/* 비밀번호 필드 */}
            <div className="space-y-2">
              <label htmlFor="password" className="block text-sm font-semibold text-gray-700 flex items-center">
                <span className="mr-2">🔒</span>
                {t('auth.login.password_label')}
              </label>
              <Input
                {...register('password')}
                type="password"
                id="password"
                aria-invalid={errors.password ? "true" : undefined}
                aria-describedby={errors.password ? "password-error" : undefined}
                className={`h-12 border-2 transition-all duration-300 ${
                  errors.password
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50/50'
                    : 'border-gray-200 focus:border-blue-500 focus:ring-blue-500/20 hover:border-gray-300'
                }`}
                placeholder={t('auth.login.password_placeholder')}
                disabled={isLoading}
              />
              {errors.password && (
                <p id="password-error" className="mt-2 text-sm text-red-600 flex items-center">
                  <span className="mr-1">⚠️</span>
                  {errors.password.message}
                </p>
              )}
            </div>

            {/* 제출 버튼 */}
            <div className="pt-2">
              <Button
                type="submit"
                disabled={isLoading}
                className="w-full h-12 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:scale-[0.98] shadow-lg hover:shadow-xl"
              >
                {isLoading ? (
                  <span role="status" aria-live="polite" className="flex items-center justify-center">
                    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {t('auth.login.logging_in')}
                  </span>
                ) : (
                  <>
                    <span className="mr-2">🚀</span>
                    {t('auth.login.button_text')}
                  </>
                )}
              </Button>
            </div>

            {/* 회원가입 링크 */}
            <div className="text-center pt-4">
              <p className="text-gray-600">
                {t('auth.login.no_account_question')}{' '}
                <Link 
                  href={redirectTo !== '/dashboard' ? `/register?redirect=${encodeURIComponent(redirectTo)}` : '/register'} 
                  className="font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                >
                  {t('auth.login.register_link')}
                </Link>
              </p>
            </div>
          </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}