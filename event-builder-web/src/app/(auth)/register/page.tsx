'use client';

import React, { useState } from 'react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useRouter, useSearchParams } from 'next/navigation'
import Link from 'next/link'
import { registerUser, type RegisterData } from '@/lib/api'
import { useTranslation } from '@/lib/i18n' // useTranslation 훅 import
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'sonner'; // sonner의 toast 함수 임포트

// Zod 스키마 정의
const baseRegisterSchema = z.object({
  name: z.string(),
  email: z.string(),
  password: z.string(),
  password_confirmation: z.string(),
});

type RegisterFormData = z.infer<typeof baseRegisterSchema>; // baseRegisterSchema에서 타입 추론

export default function RegisterPage() {
  const router = useRouter()
  const searchParams = useSearchParams();
  const [isLoading, setIsLoading] = useState(false)
  const { t } = useTranslation(); // useTranslation 훅 사용

  // URL에서 리다이렉트 경로 가져오기
  const redirectTo = searchParams.get('redirect');

  // 번역된 메시지를 변수에 저장
  const nameRequiredMessage = t('auth.register.name_required');
  const nameTooLongMessage = t('auth.register.name_too_long');
  const emailRequiredMessage = t('auth.register.email_required');
  const emailInvalidMessage = t('auth.register.email_invalid');
  const passwordMinLengthMessage = t('auth.register.password_min_length');
  const passwordConfirmRequiredMessage = t('auth.register.password_confirm_required');
  const passwordMismatchMessage = t('auth.register.password_mismatch');

  // Zod 스키마 정의 (번역된 메시지 변수 사용)
  const memoizedRegisterSchema = React.useMemo(() => {
    return baseRegisterSchema.extend({
      name: baseRegisterSchema.shape.name
        .min(1, nameRequiredMessage)
        .max(255, nameTooLongMessage),
      email: baseRegisterSchema.shape.email
        .min(1, emailRequiredMessage)
        .email(emailInvalidMessage),
      password: baseRegisterSchema.shape.password
        .min(8, passwordMinLengthMessage),
      password_confirmation: baseRegisterSchema.shape.password_confirmation
        .min(1, passwordConfirmRequiredMessage)
    }).refine((data) => data.password === data.password_confirmation, {
      message: passwordMismatchMessage,
      path: ["password_confirmation"]
    });
  }, [
    nameRequiredMessage,
    nameTooLongMessage,
    emailRequiredMessage,
    emailInvalidMessage,
    passwordMinLengthMessage,
    passwordConfirmRequiredMessage,
    passwordMismatchMessage,
  ]);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<RegisterFormData>({
    resolver: zodResolver(memoizedRegisterSchema)
  })

  const onSubmit = async (data: RegisterFormData) => {
    setIsLoading(true)

    try {
      await registerUser(data as RegisterData)

      toast.success(t('auth.register.success_message'), {
        duration: 2000,
      });

      // 2초 후 로그인 페이지로 이동 (리다이렉트 경로가 있으면 쿼리 파라미터로 전달)
      setTimeout(() => {
        const loginUrl = redirectTo ? `/login?redirect=${encodeURIComponent(redirectTo)}` : '/login';
        router.push(loginUrl);
      }, 2000)

    } catch (error: unknown) {
      console.error('Registration error:', error)

      // API 오류 메시지 추출
      let errorMessage = t('auth.register.error_message')

      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
        if (axiosError.response?.data?.message) {
          errorMessage = axiosError.response.data.message
        } else if (axiosError.response?.data?.errors) {
          // 첫 번째 오류 메시지 사용
          const firstError = Object.values(axiosError.response.data.errors)[0] as string[]
          if (firstError && firstError.length > 0) {
            errorMessage = firstError[0]
          }
        }
      }

      toast.error(errorMessage, {
        duration: 5000,
      });
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 via-blue-50 to-purple-50 px-4">
      <div className="max-w-md w-full space-y-8">
        <Card className="border-0 shadow-2xl bg-white/95 backdrop-blur-sm">
          <CardHeader className="text-center space-y-4 pb-8">
            <div className="mx-auto w-16 h-16 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4">
              <span className="text-white text-2xl">✨</span>
            </div>
            <CardTitle className="text-3xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
              {t('auth.register.title')}
            </CardTitle>
            <CardDescription className="text-lg text-gray-600">
              {t('auth.register.subtitle')}
            </CardDescription>
          </CardHeader>

          <CardContent className="space-y-6">
            {/* 회원가입 폼 */}
            <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            {/* 이름 필드 */}
            <div className="space-y-2">
              <label htmlFor="name" className="block text-sm font-semibold text-gray-700 flex items-center">
                <span className="mr-2">👤</span>
                {t('auth.register.name_label')}
              </label>
              <Input
                {...register('name')}
                type="text"
                id="name"
                aria-invalid={errors.name ? "true" : undefined}
                aria-describedby={errors.name ? "name-error" : undefined}
                className={`h-12 border-2 transition-all duration-300 ${
                  errors.name
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50/50'
                    : 'border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 hover:border-gray-300'
                }`}
                placeholder={t('auth.register.name_placeholder')}
                disabled={isLoading}
              />
              {errors.name && (
                <p id="name-error" className="mt-2 text-sm text-red-600 flex items-center">
                  <span className="mr-1">⚠️</span>
                  {errors.name.message}
                </p>
              )}
            </div>

            {/* 이메일 필드 */}
            <div className="space-y-2">
              <label htmlFor="email" className="block text-sm font-semibold text-gray-700 flex items-center">
                <span className="mr-2">📧</span>
                {t('auth.register.email_label')}
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
                    : 'border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 hover:border-gray-300'
                }`}
                placeholder={t('auth.register.email_placeholder')}
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
                {t('auth.register.password_label')}
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
                    : 'border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 hover:border-gray-300'
                }`}
                placeholder={t('auth.register.password_placeholder')}
                disabled={isLoading}
              />
              {errors.password && (
                <p id="password-error" className="mt-2 text-sm text-red-600 flex items-center">
                  <span className="mr-1">⚠️</span>
                  {errors.password.message}
                </p>
              )}
            </div>

            {/* 비밀번호 확인 필드 */}
            <div className="space-y-2">
              <label htmlFor="password_confirmation" className="block text-sm font-semibold text-gray-700 flex items-center">
                <span className="mr-2">🔐</span>
                {t('auth.register.confirm_password_label')}
              </label>
              <Input
                {...register('password_confirmation')}
                type="password"
                id="password_confirmation"
                aria-invalid={errors.password_confirmation ? "true" : undefined}
                aria-describedby={errors.password_confirmation ? "password-confirmation-error" : undefined}
                className={`h-12 border-2 transition-all duration-300 ${
                  errors.password_confirmation
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50/50'
                    : 'border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 hover:border-gray-300'
                }`}
                placeholder={t('auth.register.confirm_password_placeholder')}
                disabled={isLoading}
              />
              {errors.password_confirmation && (
                <p id="password-confirmation-error" className="mt-2 text-sm text-red-600 flex items-center">
                  <span className="mr-1">⚠️</span>
                  {errors.password_confirmation.message}
                </p>
              )}
            </div>

            {/* 제출 버튼 */}
            <div className="pt-2">
              <Button
                type="submit"
                disabled={isLoading}
                className="w-full h-12 bg-gradient-to-r from-emerald-600 to-blue-600 hover:from-emerald-700 hover:to-blue-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-[1.02] focus:scale-[0.98] shadow-lg hover:shadow-xl"
              >
                {isLoading ? (
                  <span role="status" aria-live="polite" className="flex items-center justify-center">
                    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {t('auth.register.registering')}
                  </span>
                ) : (
                  <>
                    <span className="mr-2">✨</span>
                    {t('auth.register.button_text')}
                  </>
                )}
              </Button>
            </div>

            {/* 로그인 링크 */}
            <div className="text-center pt-4">
              <p className="text-gray-600">
                {t('auth.register.has_account')}{' '}
                <Link 
                  href={redirectTo ? `/login?redirect=${encodeURIComponent(redirectTo)}` : '/login'} 
                  className="font-semibold text-emerald-600 hover:text-emerald-700 transition-colors"
                >
                  {t('auth.register.login_link')}
                </Link>
              </p>
            </div>
          </form>
          </CardContent>
        </Card>
      </div>
    </div>
  )
} 