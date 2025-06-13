# RFP 수정 및 기능 선택 페이지 개선 계획

## 목표:

1.  **RFP 수정 페이지 구현:** 대시보드에서 RFP 상세 페이지의 '수정' 버튼 클릭 시, RFP 생성 페이지를 재활용하여 기존 RFP 데이터가 미리 채워진 상태로 수정할 수 있도록 합니다.
2.  **기능 선택 페이지 개선:** 현재 RFP 생성 시 기능 선택 페이지의 디자인을 개선하고, Filament에서 추가된 모든 기능이 프론트엔드에 올바르게 표시되도록 합니다.

## 상세 계획:

### 1. RFP 수정 페이지 개선 (RFP 생성 페이지 재활용)

*   **현재 상태 분석:**
    *   RFP 생성 페이지: `event-builder-web/src/app/(main)/rfp/create/page.tsx`
    *   RFP 수정 페이지: `event-builder-web/src/app/(main)/rfp/[id]/edit/page.tsx`
    *   RFP 폼 컴포넌트: `event-builder-web/src/components/rfp/RfpForm.tsx`
    *   API 통신: `event-builder-web/src/lib/api.ts`
    *   RFP 타입 정의: `event-builder-web/src/types/rfp.ts`
    *   RFP 컨트롤러 (백엔드): `event-builder-api/app/Http/Controllers/Api/RfpController.php`

*   **계획 단계:**
    *   **단계 1: RFP 데이터 불러오기:**
        *   `event-builder-web/src/app/(main)/rfp/[id]/edit/page.tsx`에서 `id` 파라미터를 사용하여 특정 RFP 데이터를 백엔드 API로부터 불러오는 로직을 구현합니다.
        *   `event-builder-web/src/lib/api.ts`에 RFP 상세 정보를 가져오는 API 호출 함수를 추가하거나 기존 함수를 활용합니다.
        *   백엔드 `event-builder-api/app/Http/Controllers/Api/RfpController.php`에 특정 RFP를 조회하는 엔드포인트가 있는지 확인하고, 없다면 추가합니다.
    *   **단계 2: RFP 폼 컴포넌트 재활용 및 데이터 주입:**
        *   `event-builder-web/src/components/rfp/RfpForm.tsx` 컴포넌트가 `initialData` 또는 유사한 prop을 받아 폼 필드를 미리 채울 수 있도록 수정합니다.
        *   `event-builder-web/src/app/(main)/rfp/[id]/edit/page.tsx`에서 불러온 RFP 데이터를 `RfpForm` 컴포넌트에 `initialData`로 전달합니다.
    *   **단계 3: 폼 제출 로직 변경 (수정 vs 생성):**
        *   `RfpForm` 컴포넌트 내에서 폼 제출 시, `initialData`의 존재 여부(즉, 수정 모드인지 생성 모드인지)에 따라 다른 API 엔드포인트(생성 또는 수정)를 호출하도록 로직을 분기합니다.
        *   수정 모드일 경우, `PUT` 또는 `PATCH` 요청을 사용하여 백엔드 API에 RFP 데이터를 업데이트합니다.
        *   백엔드 `event-builder-api/app/Http/Controllers/Api/RfpController.php`에 RFP 업데이트를 위한 엔드포인트가 있는지 확인하고, 없다면 추가합니다.
    *   **단계 4: UI/UX 개선:**
        *   수정 페이지임을 명확히 알 수 있도록 페이지 제목이나 버튼 텍스트를 "RFP 수정" 등으로 변경합니다.

### 2. 기능 선택 페이지 개선 (디자인 및 모든 기능 표시)

*   **현재 상태 분석:**
    *   RFP 폼 컴포넌트: `event-builder-web/src/components/rfp/RfpForm.tsx` (기능 선택 로직 포함 가능성)
    *   RFP 생성 페이지: `event-builder-web/src/app/(main)/rfp/create/page.tsx` (기능 선택 컴포넌트 렌더링)
    *   API 통신: `event-builder-web/src/lib/api.ts` (기능 목록을 가져오는 API 호출)
    *   RFP 타입 정의: `event-builder-web/src/types/rfp.ts` (기능 관련 타입 정의)
    *   백엔드 기능 컨트롤러: `event-builder-api/app/Http/Controllers/Api/FeatureController.php`
    *   Filament 기능 리소스: `event-builder-api/app/Filament/Resources/FeatureResource.php`

*   **계획 단계:**
    *   **단계 1: 백엔드 기능 목록 확인:**
        *   `event-builder-api/app/Http/Controllers/Api/FeatureController.php`에서 모든 기능 목록을 올바르게 반환하는지 확인합니다.
        *   Filament에서 추가된 모든 기능이 데이터베이스에 저장되어 있는지 확인합니다. (필요시 데이터베이스 스키마 및 시더 확인)
        *   API 응답이 프론트엔드에서 예상하는 형식과 일치하는지 확인합니다.
    *   **단계 2: 프론트엔드 API 호출 및 데이터 처리 확인:**
        *   `event-builder-web/src/lib/api.ts`에서 기능 목록을 가져오는 API 호출이 올바르게 이루어지고 있는지 확인합니다.
        *   `event-builder-web/src/components/rfp/RfpForm.tsx` 또는 관련 컴포넌트에서 API 응답을 올바르게 처리하고 상태에 저장하는지 확인합니다.
    *   **단계 3: 기능 선택 UI/UX 개선:**
        *   현재 기능 선택 UI가 어떤 방식으로 구현되어 있는지 `event-builder-web/src/components/rfp/RfpForm.tsx`를 읽어 확인합니다.
        *   사용자 피드백("디자인도 별로야")을 바탕으로, 기능 선택 UI를 더 직관적이고 시각적으로 매력적으로 개선합니다. (예: 체크박스 그룹, 카드 형태, 검색/필터링 기능 추가 등)
        *   Filament에서 관리되는 기능 카테고리 정보가 있다면 이를 활용하여 기능을 그룹화하여 표시하는 것을 고려합니다.

## Mermaid 다이어그램:

```mermaid
graph TD
    subgraph RFP 수정 페이지 개선
        A[대시보드 RFP 카드] --> B{수정 버튼 클릭};
        B --> C[RFP 상세 페이지];
        C --> D[수정 버튼 클릭];
        D --> E[event-builder-web/src/app/(main)/rfp/[id]/edit/page.tsx];
        E -- id 파라미터 전달 --> F[RFP 데이터 불러오기 (API 호출)];
        F --> G[event-builder-web/src/lib/api.ts];
        G --> H[event-builder-api/app/Http/Controllers/Api/RfpController.php];
        H --> I[RFP 데이터 반환];
        I --> J[event-builder-web/src/app/(main)/rfp/[id]/edit/page.tsx];
        J -- initialData 전달 --> K[event-builder-web/src/components/rfp/RfpForm.tsx (재활용)];
        K --> L[폼 필드 미리 채우기];
        L --> M[사용자 수정];
        M --> N[폼 제출];
        N -- 수정 API 호출 --> G;
        G --> H;
        H --> O[RFP 데이터 업데이트];
    end

    subgraph 기능 선택 페이지 개선
        P[Filament] --> Q[Feature 데이터 관리];
        Q --> R[DB 저장];
        R --> S[event-builder-api/app/Http/Controllers/Api/FeatureController.php];
        S --> T[모든 Feature 데이터 반환];
        T --> U[event-builder-web/src/lib/api.ts];
        U --> V[event-builder-web/src/components/rfp/RfpForm.tsx];
        V --> W[기능 목록 렌더링];
        W -- 디자인 개선 및 누락 기능 표시 --> X[개선된 기능 선택 UI];
    end