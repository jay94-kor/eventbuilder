# 중앙 집중식 CSS 관리 시스템 구축 계획

## 1. 디자인 토큰의 중앙 집중화 및 명확화

*   **목표:** 모든 시각적 요소를 CSS 변수 또는 디자인 토큰으로 중앙 집중화하여 일관성을 확보합니다.
*   **세부 계획:**
    *   **디자인 토큰 소스 파일 정의:** `event-builder-web/src/styles/design-tokens.css` (CSS 변수 정의) 및 `event-builder-web/src/lib/design-tokens.ts` (JS/TS에서 접근 가능한 토큰 정의) 파일을 생성합니다.
    *   **`globals.css` 리팩토링:** 현재 `globals.css`에 정의된 모든 CSS 변수(`--radius`, `--spacing`, `--color`, `--shadow`, `--transition` 등)를 `event-builder-web/src/styles/design-tokens.css`로 이동합니다.
    *   **Tailwind CSS 설정 업데이트:** `tailwind.config.js`에서 `event-builder-web/src/styles/design-tokens.css`에 정의된 CSS 변수를 참조하도록 설정합니다.
    *   **JS/TS 접근을 위한 토큰 정의:** `event-builder-web/src/lib/design-tokens.ts` 파일에 디자인 토큰을 JavaScript/TypeScript 객체로 정의하여 컴포넌트에서 타입 안전하게 접근할 수 있도록 합니다. (예: `colors.primary`, `spacing.md` 등)
    *   **OKLCH 색상 형식 유지:** 현재 사용 중인 OKLCH 색상 형식을 유지하여 색상 관리의 유연성을 확보합니다.
    *   **`border/50` 오류 해결:** `globals.css`에서 `border/50`과 같이 Tailwind가 직접 인식하지 못하는 구문은 `border-[oklch(var(--border)/0.5)]`와 같이 CSS 변수를 직접 참조하는 방식으로 변경하여 디자인 토큰 시스템 내에서 해결합니다.

## 2. CSS 린팅 규칙 적용 및 자동화

*   **목표:** 불필요한 오류를 최소화하고 코드 품질 및 유지보수성을 향상시킵니다.
*   **세부 계획:**
    *   **Stylelint 설정 강화:** `event-builder-web/.stylelintrc.json` 파일을 업데이트하여 디자인 토큰 사용 강제, 특정 CSS 속성 순서 지정, 불필요한 `@apply` 사용 제한 등 엄격한 린팅 규칙을 추가합니다.
    *   **Pre-commit Hook 설정:** Git pre-commit hook (예: Husky + lint-staged)을 설정하여 커밋 전에 자동으로 Stylelint를 실행하고, 규칙 위반 시 커밋을 방지합니다.
    *   **CI/CD 통합:** CI/CD 파이프라인에 Stylelint 검사를 추가하여 배포 전에 코드 품질을 검증합니다.

## 3. 재사용 가능한 UI 컴포넌트 라이브러리 구축

*   **목표:** 사용자 경험(UX)을 최적화하고 모든 UI 요소의 통일성을 보장합니다.
*   **세부 계획:**
    *   **기존 컴포넌트 재구성:** `event-builder-web/src/components/ui` 및 기타 컴포넌트 디렉토리의 기존 컴포넌트들을 디자인 시스템 원칙에 따라 재구성합니다. (예: `Button`, `Card`, `Input`, `Badge` 등)
    *   **디자인 시스템 유틸리티 활용:** `event-builder-web/src/lib/design-system.ts`에 정의된 유틸리티 함수들을 활용하여 컴포넌트의 스타일을 일관되게 적용합니다.
    *   **Storybook 도입 (선택 사항):** Storybook을 도입하여 컴포넌트들을 시각적으로 문서화하고, 다양한 상태 및 변형을 테스트할 수 있는 환경을 구축합니다.
    *   **컴포넌트 가이드라인 문서화:** 각 컴포넌트의 사용법, 속성, 예시 등을 포함하는 가이드라인 문서를 작성합니다.

## 작업 흐름

```mermaid
graph TD
    A[요청 분석 및 정보 수집] --> B[디자인 토큰 중앙 집중화 및 명확화];
    B --> C[CSS 린팅 규칙 적용 및 자동화];
    C --> D[재사용 가능한 UI 컴포넌트 라이브러리 구축];
    D --> E[계획 검토 및 사용자 승인];
    E -- 승인 --> F[Code 모드 전환 및 구현];
    E -- 수정 요청 --> B;
    F --> G[작업 완료 및 결과 제시];