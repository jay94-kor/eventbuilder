# 국제화(i18n) 번역 작업 TODO 리스트

이 문서는 프론트엔드 국제화(i18n) 재설계 및 번역 작업을 단계별로 추적하기 위한 TODO 리스트입니다.

## 1. 핵심 국제화 로직 및 언어 딕셔너리 파일 설정

- [x] `event-builder-web/src/lib/i18n.ts` 파일의 `allContent` 중복 정의 문제 해결 및 새로운 번역 로직 적용.
    - `useLanguageStore` 임포트 확인.
    - `LocalizedContent` 인터페이스 유지.
    - `rfpContent` 및 `headerContent`와 같은 기존 숫자 ID 기반 딕셔너리 제거.
    - [x] `useTranslation` 훅을 언어별 파일에서 동적으로 번역을 로드하도록 업데이트.
    - `useEffect` 및 `useState`를 사용하여 번역 로드 및 상태 관리.
    - [x] 중첩된 키 경로를 따라 텍스트를 찾는 `getTranslation` 헬퍼 함수 구현.

## 2. 언어별 딕셔너리 파일 내용 채우기

- [x] `event-builder-web/src/lib/locales/ko.ts` 파일 생성 및 한국어 텍스트 추가.
- [x] `event-builder-web/src/lib/locales/en.ts` 파일 생성 및 영어 텍스트 추가.
- [x] `event-builder-web/src/lib/locales/zh.ts` 파일 생성 및 중국어 텍스트 추가.
- [x] `event-builder-web/src/lib/locales/ja.ts` 파일 생성 및 일본어 텍스트 추가.

## 3. 컴포넌트 및 페이지 번역 적용

- [x] `event-builder-web/src/app/(auth)/login/page.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
    - Zod 스키마 유효성 검사 메시지 번역 적용.
- [x] `event-builder-web/src/app/(auth)/register/page.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
    - Zod 스키마 유효성 검사 메시지 번역 적용.
- [x] `event-builder-web/src/components/layout/Header.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
    - 언어 선택 드롭다운 옵션 텍스트 번역 적용.
- [x] `event-builder-web/src/app/(main)/dashboard/page.tsx` 번역 적용.
- [x] `event-builder-web/src/app/(main)/profile/page.tsx` 번역 적용.
- [x] `event-builder-web/src/app/(main)/rfp/create/page.tsx` 번역 적용.
- [x] `event-builder-web/src/components/rfp/CategorySection.tsx` 번역 적용.
- [x] `event-builder-web/src/components/rfp/FeatureCard.tsx` 번역 적용.
- \[x\] 그 외 하드코딩된 텍스트가 있는 모든 프론트엔드 파일 식별 및 번역 적용.ㅇ
- [x] `event-builder-web/src/app/layout.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/app/page.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/app/(main)/layout.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/app/(main)/rfp/[id]/page.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/app/(main)/rfp/[id]/edit/page.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/app/(main)/rfp/create/configure/page.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/app/test-dynamic-form/page.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/components/common/Button.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/common/ErrorBoundary.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/ui/input.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/DashboardDemo.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/MonthlyChart.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/MonthlyRfpChart.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/OnboardingRfpCard.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/OnboardingWelcome.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/StartRfpButton.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/StatsCard.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/dashboard/TopFeaturesChart.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/features/FeatureCard.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/features/FeatureCategorySection.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/layout/Footer.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/profile/ProfileForm.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/providers/ClientProvider.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/rfp/DynamicFeatureForm.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- \[x\] `event-builder-web/src/components/rfp/RecommendationAlert.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.
- [x] `event-builder-web/src/components/ui/toast.tsx` 번역 적용.
    - `useTranslation()` 훅 임포트 및 사용.
    - 모든 하드코딩된 텍스트를 `t('key.path.to.text')` 형태로 교체.

## 4. 테스트 및 검증

i18n으로 변경한다음에 각각의 언어(4개의 언어가있음)에다가도 들어가야 하는 내용들을 추가해서 missing text 오류가 없도록 해줘.

- \[x\] 개발 서버 실행 후 모든 번역된 페이지 및 컴포넌트 확인.
- \[x\] 언어 변경 기능 테스트.
- \[x\] 폼 유효성 검사 메시지 등 동적 텍스트 번역 확인.
- \[x\] 누락된 번역 키 또는 오역 검토.