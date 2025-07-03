# RFP 생성 프로세스 개발 계획

## 개요
이 문서는 EventBuilder 시스템의 새로운 RFP 생성 프로세스를 위한 체계적인 개발 계획을 제시합니다. 총 5단계의 스텝으로 구성된 사용자 친화적인 RFP 생성 프로세스를 통해 대행사가 효율적으로 RFP를 생성하고 관리할 수 있도록 합니다.

## 전체 프로세스 흐름

```
Step 1: RFP 기본 정보 입력
    ↓
Step 2: 요소 선택 (추천 로직 포함)
    ↓
Step 3: 각 요소별 상세 정보 입력
    ↓
Step 4: 카테고리별 예산 배정 및 발주 방식 결정
    ↓
Step 5: 승인 프로세스
    ↓
공고 게시 → 제안서 접수 → 평가 → 계약 체결 → 스케줄 관리
```

---

## Step 1: RFP 기본 정보 입력

### 목적
행사의 기본 정보와 공통 설정을 입력하여 RFP의 기초를 구축합니다.

### 사용할 테이블
- **projects** (프로젝트 정보)
- **rfps** (RFP 기본 정보)

### 입력 항목
- **행사 정보**
  - 행사명 (`projects.project_name`)
  - 행사 기간 (`projects.start_datetime`, `projects.end_datetime`)
  - 준비 기간 (`projects.preparation_start_datetime`)
  - 철거 완료 시간 (`projects.breakdown_end_datetime`)
  - 실내/실외 여부 (`projects.is_indoor`)
  - 행사 장소 (`projects.location`)

- **클라이언트 정보**
  - 클라이언트명 (`projects.client_name`)
  - 담당자 (`projects.client_contact_person`)
  - 연락처 (`projects.client_contact_number`)

- **대행사 정보**
  - 주담당자 (`projects.main_agency_contact_user_id`)
  - 부담당자 (`projects.sub_agency_contact_user_id`)
  - 총 예산 (`projects.budget_including_vat`)

- **RFP 기본 설정**
  - 제안서 마감일 (`rfps.closing_at`)
  - RFP 설명 (`rfps.rfp_description`)

### 비즈니스 로직
1. **유효성 검증**
   - 행사 날짜 유효성 (시작일 < 종료일)
   - 담당자 권한 확인 (해당 대행사 멤버인지 확인)
   - 예산 범위 검증

2. **자동 설정**
   - RFP 상태를 'draft'로 설정
   - 생성자 정보 자동 입력
   - 고유 ID 생성

### API 엔드포인트
- `POST /api/rfps` - 기본 정보로 RFP 생성
- `PUT /api/rfps/{id}/basic-info` - 기본 정보 수정

### 프론트엔드 컴포넌트
- `RfpBasicInfoForm.tsx` - 기본 정보 입력 폼
- `DateTimePicker.tsx` - 날짜/시간 선택 컴포넌트
- `LocationSelector.tsx` - 장소 선택 컴포넌트

---

## Step 2: 요소 선택 (추천 로직 포함)

### 목적
AI 기반 추천 시스템을 통해 적합한 카테고리와 요소를 선택합니다.

### 사용할 테이블
- **categories** (카테고리 정보)
- **element_definitions** (요소 정의)
- **category_recommendations** (카테고리 추천)
- **element_recommendations** (요소 추천)
- **smart_recommendation_rules** (스마트 추천 규칙)

### 추천 로직 구현

#### 1. 기본 추천 로직
```sql
-- 행사 유형별 기본 카테고리 추천
SELECT c.* FROM categories c
WHERE c.event_types @> '["{event_type}"]'
ORDER BY c.popularity_score DESC
```

#### 2. 상황별 추천 로직
```sql
-- 실내/실외 기반 추천
SELECT ed.* FROM element_definitions ed
WHERE ed.event_types @> '["{indoor_outdoor}"]'
AND ed.complexity_level IN (SELECT level FROM user_complexity_preference)
```

#### 3. 스마트 추천 규칙
```sql
-- 규칙 기반 추천
SELECT srr.recommendations FROM smart_recommendation_rules srr
WHERE srr.is_active = true
AND srr.conditions @> '{"event_type": "{type}", "budget_range": "{range}"}'
ORDER BY srr.priority DESC
```

### 비즈니스 로직
1. **단계별 추천**
   - 1단계: 기본 카테고리 추천
   - 2단계: 선택된 카테고리 기반 관련 카테고리 추천
   - 3단계: 카테고리별 핵심 요소 추천
   - 4단계: 선택된 요소 기반 보완 요소 추천

2. **추천 점수 계산**
   ```typescript
   const recommendationScore = 
     (popularity_score * 0.3) + 
     (compatibility_score * 0.4) + 
     (user_history_score * 0.3);
   ```

3. **필터링 옵션**
   - 예산 범위별 필터링
   - 복잡도별 필터링
   - 카테고리별 필터링

### API 엔드포인트
- `GET /api/categories/recommendations` - 카테고리 추천 목록
- `GET /api/element-definitions/recommendations` - 요소 추천 목록
- `POST /api/rfps/{id}/elements/select` - 요소 선택 저장
- `GET /api/categories/{id}/related` - 관련 카테고리 조회

### 프론트엔드 컴포넌트
- `ElementRecommendationEngine.tsx` - 추천 엔진 메인 컴포넌트
- `CategorySelector.tsx` - 카테고리 선택 컴포넌트
- `ElementCard.tsx` - 요소 카드 컴포넌트
- `RecommendationFilters.tsx` - 추천 필터 컴포넌트

---

## Step 3: 각 요소별 상세 정보 입력

### 목적
선택된 각 요소에 대한 상세한 사양과 요구사항을 입력합니다.

### 사용할 테이블
- **rfp_elements** (요소별 상세 정보)
- **element_definitions** (요소 정의 참조)

### 입력 항목
- **기본 정보**
  - 요소 타입 (`rfp_elements.element_type`)
  - 총 수량 (`rfp_elements.total_quantity`)
  - 기본 수량 (`rfp_elements.base_quantity`)

- **상세 사양**
  - 사양 필드 (`rfp_elements.spec_fields`)
  - 변형 사양 (`rfp_elements.spec_variants`)
  - 특별 요구사항 (`rfp_elements.special_requirements`)

- **예산 정보**
  - 할당 예산 (`rfp_elements.allocated_budget`)
  - 선급금 비율 (`rfp_elements.prepayment_ratio`)
  - 선급금 지급일 (`rfp_elements.prepayment_due_date`)
  - 잔금 비율 (`rfp_elements.balance_ratio`)
  - 잔금 지급일 (`rfp_elements.balance_due_date`)

### 비즈니스 로직
1. **동적 스펙 관리**
   ```typescript
   interface SpecField {
     field_name: string;
     field_type: 'text' | 'number' | 'select' | 'multiselect';
     required: boolean;
     default_value?: any;
     options?: string[];
   }
   ```

2. **변형 관리**
   ```typescript
   interface SpecVariant {
     variant_name: string;
     base_quantity: number;
     additional_specs: Record<string, any>;
   }
   ```

3. **예산 검증**
   - 전체 예산 대비 할당 예산 검증
   - 선급금 + 잔금 비율 = 100% 검증
   - 지급일 유효성 검증

### API 엔드포인트
- `GET /api/element-definitions/{id}/template` - 요소 템플릿 조회
- `PUT /api/rfp-elements/{id}/specifications` - 사양 정보 업데이트
- `POST /api/rfp-elements/{id}/variants` - 변형 추가
- `DELETE /api/rfp-elements/{id}/variants/{variantId}` - 변형 삭제

### 프론트엔드 컴포넌트
- `ElementDetailForm.tsx` - 요소 상세 정보 입력 폼
- `DynamicSpecForm.tsx` - 동적 사양 입력 폼
- `VariantManager.tsx` - 변형 관리 컴포넌트
- `BudgetAllocator.tsx` - 예산 할당 컴포넌트

---

## Step 4: 카테고리별 예산 배정 및 발주 방식 결정

### 목적
카테고리별로 예산을 배정하고 발주 방식(통합공고 vs 분리공고)을 결정합니다.

### 사용할 테이블
- **rfps** (발주 방식 업데이트)
- **rfp_elements** (예산 정보 업데이트)

### 주요 기능
1. **카테고리별 예산 집계**
   ```sql
   SELECT 
     ed.category_id,
     c.name as category_name,
     SUM(re.allocated_budget) as total_budget,
     COUNT(re.id) as element_count
   FROM rfp_elements re
   JOIN element_definitions ed ON re.element_definition_id = ed.id
   JOIN categories c ON ed.category_id = c.id
   WHERE re.rfp_id = ?
   GROUP BY ed.category_id, c.name
   ```

2. **발주 방식 결정**
   - `integrated`: 통합공고 (모든 카테고리를 하나의 공고로)
   - `separated_by_element`: 요소별 분리공고
   - `separated_by_group`: 그룹별 분리공고

### 비즈니스 로직
1. **예산 유효성 검증**
   - 카테고리별 예산 합계 ≤ 전체 예산
   - 최소 예산 요구사항 충족 여부

2. **발주 방식 추천**
   ```typescript
   const recommendIssueType = (categories: Category[], totalBudget: number) => {
     if (categories.length <= 2) return 'integrated';
     if (totalBudget > 1000000000) return 'separated_by_group';
     return 'separated_by_element';
   };
   ```

3. **자동 그룹핑**
   - 관련성이 높은 카테고리 자동 그룹핑
   - 예산 규모별 그룹핑

### API 엔드포인트
- `GET /api/rfps/{id}/budget-summary` - 예산 요약 조회
- `PUT /api/rfps/{id}/issue-type` - 발주 방식 설정
- `POST /api/rfps/{id}/budget-allocation` - 예산 배정
- `GET /api/rfps/{id}/issue-type/recommendation` - 발주 방식 추천

### 프론트엔드 컴포넌트
- `BudgetSummary.tsx` - 예산 요약 컴포넌트
- `IssueTypeSelector.tsx` - 발주 방식 선택 컴포넌트
- `CategoryGrouping.tsx` - 카테고리 그룹핑 컴포넌트
- `BudgetAllocationChart.tsx` - 예산 배정 차트

---

## Step 5: 승인 프로세스

### 목적
최종 결재권자(대표이사)의 승인을 통해 RFP를 최종 확정합니다.

### 사용할 테이블
- **rfp_approvals** (승인 프로세스)
- **rfps** (상태 업데이트)
- **users** (승인자 정보)

### 승인 워크플로우
1. **승인 요청 생성**
   ```sql
   INSERT INTO rfp_approvals (
     rfp_id, requested_by_user_id, status, requested_at
   ) VALUES (?, ?, 'pending', NOW())
   ```

2. **승인자 확인**
   ```sql
   SELECT u.* FROM users u
   JOIN agency_members am ON u.id = am.user_id
   JOIN user_roles ur ON u.id = ur.user_id
   JOIN roles r ON ur.role_id = r.id
   WHERE am.agency_id = ? AND r.name = 'ceo'
   ```

3. **승인 처리**
   - 승인 시: RFP 상태를 'published'로 변경
   - 거부 시: 'draft' 상태 유지, 수정 요청 사항 기록

### 비즈니스 로직
1. **승인 권한 검증**
   - 대표이사 역할 확인
   - 해당 대행사 소속 확인

2. **승인 전 체크리스트**
   - 모든 필수 정보 입력 완료
   - 예산 배정 완료
   - 법적 요구사항 충족

3. **알림 시스템**
   - 승인 요청 알림
   - 승인 완료 알림
   - 거부 시 사유 알림

### API 엔드포인트
- `POST /api/rfps/{id}/approval-request` - 승인 요청
- `PUT /api/rfp-approvals/{id}/approve` - 승인 처리
- `PUT /api/rfp-approvals/{id}/reject` - 거부 처리
- `GET /api/rfps/{id}/approval-status` - 승인 상태 조회

### 프론트엔드 컴포넌트
- `ApprovalRequestForm.tsx` - 승인 요청 폼
- `ApprovalStatusCard.tsx` - 승인 상태 카드
- `ApprovalHistoryList.tsx` - 승인 이력 목록
- `ApprovalActions.tsx` - 승인 액션 컴포넌트

---

## Step 5 완료 후: 공고 게시

### 목적
승인된 RFP를 바탕으로 공고를 게시하고 평가자를 지정합니다.

### 사용할 테이블
- **announcements** (공고 정보)
- **announcement_evaluators** (평가자 지정)

### 자동화 프로세스
1. **공고 자동 생성**
   ```sql
   INSERT INTO announcements (
     rfp_id, title, description, status, 
     published_at, closing_at, evaluation_criteria
   ) SELECT 
     r.id, 
     CONCAT(p.project_name, ' - ', '용역 제안서 모집'),
     r.rfp_description,
     'active',
     NOW(),
     r.closing_at,
     '{"criteria": ["기술력", "경험", "가격", "일정"]}'
   FROM rfps r
   JOIN projects p ON r.project_id = p.id
   WHERE r.id = ? AND r.status = 'published'
   ```

2. **평가자 자동 배정**
   - 프로젝트 주담당자 자동 배정
   - 부담당자 자동 배정
   - 추가 평가자 선택 기능

### API 엔드포인트
- `POST /api/announcements/auto-create` - 공고 자동 생성
- `POST /api/announcements/{id}/evaluators` - 평가자 추가
- `GET /api/announcements/{id}/summary` - 공고 요약

---

## 이후 프로세스: 제안서 접수 및 평가

### 관련 테이블
- **proposals** (제안서)
- **evaluations** (평가)
- **contracts** (계약)
- **schedules** (스케줄)

### 주요 기능
1. **제안서 접수**
   - 온라인 제안서 제출
   - 파일 첨부 지원
   - 제출 마감일 관리

2. **평가 시스템**
   - 다중 평가자 지원
   - 가중치 기반 점수 계산
   - 평가 결과 자동 집계

3. **계약 관리**
   - 낙찰자 선정
   - 계약 조건 관리
   - 스케줄 자동 생성

---

## 개발 우선순위

### Phase 1: 기본 RFP 생성 (4주)
- Step 1: 기본 정보 입력
- Step 2: 요소 선택 (기본 기능)
- Step 3: 상세 정보 입력 (기본 기능)

### Phase 2: 고급 기능 (4주)
- Step 2: 추천 로직 구현
- Step 3: 동적 스펙 관리
- Step 4: 예산 배정 및 발주 방식

### Phase 3: 승인 및 공고 (3주)
- Step 5: 승인 프로세스
- 공고 자동 생성
- 알림 시스템

### Phase 4: 제안서 관리 (4주)
- 제안서 접수 시스템
- 평가 시스템
- 계약 관리

---

## 기술 스택

### 백엔드
- **Framework**: Laravel 11
- **Database**: PostgreSQL
- **API**: RESTful API
- **Authentication**: Laravel Sanctum
- **Queue**: Redis

### 프론트엔드
- **Framework**: Next.js 14
- **UI Library**: Tailwind CSS + shadcn/ui
- **State Management**: Zustand
- **Forms**: React Hook Form + Zod
- **Charts**: Recharts

### 개발 도구
- **Testing**: PHPUnit, Jest
- **Documentation**: Swagger/OpenAPI
- **Deployment**: Docker
- **CI/CD**: GitHub Actions

---

## 성능 최적화 고려사항

### 데이터베이스 최적화
- 적절한 인덱스 설정
- 쿼리 최적화
- 캐싱 전략

### 프론트엔드 최적화
- 코드 스플리팅
- 이미지 최적화
- API 응답 캐싱

### 확장성 고려
- 마이크로서비스 아키텍처 준비
- 수평 확장 가능한 구조
- 모니터링 및 로깅 시스템

---

## 품질 보증

### 테스트 전략
- **Unit Tests**: 개별 함수 및 컴포넌트
- **Integration Tests**: API 엔드포인트
- **E2E Tests**: 사용자 시나리오
- **Performance Tests**: 부하 테스트

### 코드 품질
- **ESLint/Prettier**: 코드 스타일 통일
- **TypeScript**: 타입 안정성
- **Code Review**: 동료 검토
- **Documentation**: 상세한 문서화

이 개발 계획을 통해 사용자 친화적이고 효율적인 RFP 생성 시스템을 구축할 수 있습니다.