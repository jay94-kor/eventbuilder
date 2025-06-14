# Event Builder Web

이 프로젝트는 이벤트 생성 및 관리를 위한 웹 프론트엔드 애플리케이션입니다. [Next.js](https://nextjs.org)를 기반으로 구축되었으며, 사용자 친화적인 인터페이스와 반응형 디자인을 제공합니다.

## 기술 스택

- **프레임워크**: Next.js (15.3.3)
- **UI 라이브러리**: Shadcn UI
- **스타일링**: Tailwind CSS (3.4.17), PostCSS
- **상태 관리**: Zustand (5.0.5)
- **폼 관리**: React Hook Form (7.57.0), Zod (3.25.56)
- **날짜 처리**: date-fns (4.1.0), dayjs (1.11.13)
- **아이콘**: Heroicons (2.2.0), Lucide React (0.514.0), Tabler Icons React (3.34.0)
- **폰트**: Geist (1.4.2)
- **API 통신**: Axios (백엔드 API와 연동)
- **인증**: js-cookie (3.0.5)
- **차트**: Recharts (2.15.3)
- **알림**: Sonner (2.0.5)

## 시작하기

개발 서버를 실행하려면 다음 단계를 따르세요:

1. **종속성 설치**:
   ```bash
   npm install
   ```

2. **개발 서버 실행**:
   ```bash
   npm run dev
   ```
   또는 Turbopack을 사용하여 더 빠르게 개발 서버를 실행할 수 있습니다:
   ```bash
   npm run dev:turbo
   ```

3. 브라우저에서 [http://localhost:3000](http://localhost:3000)을 엽니다.

페이지는 `src/app/page.tsx` 파일을 수정하여 편집할 수 있으며, 파일을 편집하면 자동으로 업데이트됩니다.

이 프로젝트는 [`next/font`](https://nextjs.org/docs/app/building-your-application/optimizing/fonts)를 사용하여 Vercel의 새로운 폰트 패밀리인 [Geist](https://vercel.com/font)를 자동으로 최적화하고 로드합니다.

## UI/UX 가이드라인

이 프로젝트는 일관된 UI/UX를 위해 다음 가이드라인을 따릅니다:

- **UI 프레임워크**: [Shadcn UI](https://ui.shadcn.com/) 컴포넌트를 사용하여 일관된 디자인과 재사용 가능한 UI 요소를 구축합니다.
- **컬러 관리**: 모든 애플리케이션 컬러는 `tailwind.config.js` 또는 전역 CSS 변수를 통해 중앙에서 관리됩니다. 새로운 컬러를 추가하거나 기존 컬러를 수정할 때는 이 파일을 업데이트해야 합니다.
- **폰트 및 브랜딩 요소**: 폰트 사이즈, 폰트 패밀리, 간격 등 모든 브랜딩 관련 스타일은 `tailwind.config.js` 또는 `src/app/globals.css`와 같은 전역 스타일 파일에서 정의하고 관리합니다.

이 가이드라인을 준수하여 프로젝트 전반에 걸쳐 시각적 일관성을 유지하고 개발 효율성을 높입니다.

## API 연동

이 프로젝트는 `next.config.ts` 파일에 설정된 `rewrites`를 통해 백엔드 API 서버와 통신합니다. 기본적으로 `http://localhost:8000/api/:path*`로 프록시됩니다.

```typescript
// next.config.ts
const nextConfig: NextConfig = {
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'http://localhost:8000/api/:path*', // 백엔드 API 서버 주소
      },
    ];
  },
};
```

백엔드 API 서버가 다른 주소에서 실행되는 경우, `next.config.ts` 파일의 `destination` 값을 수정해야 합니다.

## 코드 품질 및 스타일

- **린트**: ESLint를 사용하여 JavaScript/TypeScript 코드의 품질을 유지합니다.
  ```bash
  npm run lint
  ```
- **CSS 린트**: Stylelint를 사용하여 CSS 코드의 스타일 일관성을 유지합니다.
  ```bash
  npm run lint:css # CSS 파일 자동 수정
  npm run lint:css-check # CSS 파일 스타일 검사
  ```

## 더 알아보기

Next.js에 대한 자세한 내용은 다음 리소스를 참조하세요:

- [Next.js Documentation](https://nextjs.org/docs) - Next.js 기능 및 API에 대해 알아봅니다.
- [Learn Next.js](https://nextjs.org/learn) - 대화형 Next.js 튜토리얼.

[Next.js GitHub 저장소](https://github.com/vercel/next.js)에서 피드백과 기여를 환영합니다!

## 국제화(i18n)

`useTranslation` 훅을 사용할 때 `TypeError: t is not a function` 오류가 발생하는 경우, 이는 `useTranslation()` 훅의 반환 값을 잘못 처리했기 때문입니다. `useTranslation()`은 `t` 함수를 포함하는 객체를 반환하므로, `t`를 직접 사용하려면 구조 분해 할당을 통해 `t` 함수를 추출해야 합니다.

**잘못된 사용 예시:**
```typescript
const t = useTranslation();
t('some.key');
```

**올바른 사용 예시:**
```typescript
const { t } = useTranslation();
t('some.key');
```

특히 Zod 스키마와 같이 훅이 허용되지 않는 컨텍스트에서 번역된 메시지를 사용해야 하는 경우, `useTranslation` 훅을 컴포넌트의 최상위 레벨에서 호출하고, 번역된 메시지를 변수에 저장한 다음, 이 변수를 Zod 스키마에 전달하는 방식을 사용해야 합니다.

**Zod 스키마 내에서 올바른 사용 예시:**
```typescript
import React from 'react';
import { useTranslation } from '@/lib/i18n';
import { z } from 'zod';

export default function MyComponent() {
  const { t } = useTranslation();

  const requiredMessage = t('validation.required');
  const invalidEmailMessage = t('validation.invalid_email');

  const mySchema = React.useMemo(() => {
    return z.object({
      email: z.string()
        .min(1, requiredMessage)
        .email(invalidEmailMessage),
    });
  }, [requiredMessage, invalidEmailMessage]);

  // ...
}
```

## Vercel에 배포

Next.js 앱을 배포하는 가장 쉬운 방법은 Next.js 개발팀에서 만든 [Vercel Platform](https://vercel.com/new?utm_medium=default-template&filter=next.js&utm_source=create-next-app&utm_campaign=create-next-app-readme)을 사용하는 것입니다.

자세한 내용은 [Next.js 배포 문서](https://nextjs.org/docs/app/building-your-application/deploying)를 참조하세요.
