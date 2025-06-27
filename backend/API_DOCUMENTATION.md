# Bidly Backend API 문서

> **행사 기획 대행사와 용역사를 연결하는 입찰 플랫폼 API**  
> Version: 1.0.0  
> 최종 업데이트: 2025년 6월 27일

## 📋 목차

1. [개요](#개요)
2. [인증](#인증)
3. [기본 정보](#기본-정보)
4. [API 엔드포인트](#api-엔드포인트)
   - [인증 API](#인증-api)
   - [RFP 관리 API](#rfp-관리-api)
   - [공고 관리 API](#공고-관리-api)
   - [제안서 관리 API](#제안서-관리-api)
   - [계약 관리 API](#계약-관리-api)
   - [스케줄 관리 API](#스케줄-관리-api)
   - [평가 관리 API](#평가-관리-api)
   - [요소 정의 API](#요소-정의-api)
5. [데이터 모델](#데이터-모델)
6. [에러 처리](#에러-처리)
7. [개발 환경 설정](#개발-환경-설정)

---

## 개요

Bidly는 행사 기획 대행사와 용역사를 연결하는 입찰 플랫폼입니다. 이 API를 통해 RFP 생성, 공고 발행, 제안서 제출, 계약 관리 등의 모든 비즈니스 프로세스를 처리할 수 있습니다.

### 주요 기능
- 🏢 **대행사**: RFP 생성, 공고 발행, 제안서 평가, 계약 관리
- 🏭 **용역사**: 공고 조회, 제안서 제출, 계약 이행
- 👨‍💼 **관리자**: 전체 시스템 관리, 사용자 관리, 평가 시스템 운영

---

## 인증

### Bearer Token 인증
모든 API 요청(로그인 제외)에는 Authorization 헤더가 필요합니다.

```http
Authorization: Bearer {your_token_here}
```

### 사용자 타입
- `admin`: 시스템 관리자
- `agency_member`: 대행사 멤버
- `vendor_member`: 용역사 멤버

---

## 기본 정보

- **Base URL**: `http://localhost:8000`
- **Content-Type**: `application/json`
- **날짜 형식**: ISO 8601 (`2024-01-01T09:00:00Z`)
- **ID 형식**: UUID v4 (`01234567-89ab-cdef-0123-456789abcdef`)

---

## API 엔드포인트

## 인증 API

### 로그인
사용자 로그인 및 토큰 발급

```http
POST /api/login
```

**Request Body:**
```json
{
  "email": "admin@bidly.com",
  "password": "bidlyadmin123!"
}
```

**Response (200):**
```json
{
  "user": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "name": "관리자",
    "email": "admin@bidly.com",
    "user_type": "admin"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz",
  "message": "로그인 성공"
}
```

### 로그아웃
현재 토큰 폐기

```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "로그아웃 성공"
}
```

### 현재 사용자 정보
인증된 사용자 정보 조회

```http
GET /api/user
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "user": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "name": "관리자",
    "email": "admin@bidly.com",
    "user_type": "admin"
  }
}
```

---

## RFP 관리 API

### RFP 생성
새로운 RFP(Request for Proposal) 생성

```http
POST /api/rfps
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "project_name": "2024 신년 행사",
  "start_datetime": "2024-02-01T09:00:00Z",
  "end_datetime": "2024-02-01T18:00:00Z",
  "preparation_start_datetime": "2024-01-30T08:00:00Z",
  "철수_end_datetime": "2024-02-02T12:00:00Z",
  "client_name": "ABC 회사",
  "client_contact_person": "김담당자",
  "client_contact_number": "010-1234-5678",
  "is_indoor": true,
  "location": "서울시 강남구 코엑스",
  "budget_including_vat": 50000000,
  "issue_type": "integrated",
  "rfp_description": "신년 행사를 위한 종합 이벤트 기획",
  "closing_at": "2024-01-25T17:00:00Z",
  "elements": [
    {
      "element_type": "stage",
      "details": {
        "size": "10m x 8m",
        "height": "1.2m"
      },
      "allocated_budget": 10000000,
      "prepayment_ratio": 0.3,
      "prepayment_due_date": "2024-01-28",
      "balance_ratio": 0.7,
      "balance_due_date": "2024-02-05"
    }
  ]
}
```

**발주 타입 (issue_type):**
- `integrated`: 통합 발주
- `separated_by_element`: 요소별 분리 발주
- `separated_by_group`: 부분 묶음 발주

### RFP 목록 조회
대행사별 RFP 목록 조회 (페이지네이션)

```http
GET /api/rfps
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "RFP 목록을 성공적으로 불러왔습니다.",
  "rfps": {
    "data": [
      {
        "id": "01234567-89ab-cdef-0123-456789abcdef",
        "current_status": "draft",
        "issue_type": "integrated",
        "closing_at": "2024-01-25T17:00:00Z",
        "project": { /* 프로젝트 정보 */ },
        "elements": [ /* RFP 요소들 */ ]
      }
    ],
    "current_page": 1,
    "total": 25
  }
}
```

### RFP 상세 조회
특정 RFP 상세 정보 조회

```http
GET /api/rfps/{rfp_id}
Authorization: Bearer {token}
```

---

## 공고 관리 API

### RFP 공고 발행
승인된 RFP를 입찰 공고로 발행

```http
POST /api/rfps/{rfp_id}/publish
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "closing_at": "2024-01-30T17:00:00Z",
  "estimated_price": 45000000,
  "channel_type": "public",
  "contact_info_private": false,
  "evaluation_criteria": {
    "price_weight": 40,
    "portfolio_weight": 35,
    "additional_weight": 25,
    "price_deduction_rate": 5,
    "price_rank_deduction_points": [10, 20, 30]
  }
}
```

**채널 타입:**
- `public`: 공용 채널 (모든 용역사 접근 가능)
- `agency_private`: 대행사 전용 채널 (승인된 용역사만 접근)

### 공고 목록 조회
입찰 공고 목록 조회

```http
GET /api/announcements
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "입찰 공고 목록을 성공적으로 불러왔습니다.",
  "announcements": {
    "data": [
      {
        "id": "01234567-89ab-cdef-0123-456789abcdef",
        "title": "2024 신년 행사 - 무대 용역 입찰",
        "description": "신년 행사를 위한 종합 이벤트 기획...",
        "estimated_price": 45000000,
        "closing_at": "2024-01-30T17:00:00Z",
        "channel_type": "public",
        "status": "open",
        "rfp": { /* RFP 정보 */ },
        "agency": { /* 대행사 정보 */ }
      }
    ]
  }
}
```

### 공고 상세 조회
특정 공고 상세 정보 조회

```http
GET /api/announcements/{announcement_id}
Authorization: Bearer {token}
```

---

## 제안서 관리 API

### 제안서 제출
특정 공고에 제안서 제출 (용역사만 가능)

```http
POST /api/announcements/{announcement_id}/proposals
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "proposed_price": 42000000,
  "proposal_text": "저희 회사는 10년간의 무대 설치 경험을 바탕으로..."
}
```

### 제안서 목록 조회
공고별 제출된 제안서 목록 (대행사/관리자만)

```http
GET /api/announcements/{announcement_id}/proposals
Authorization: Bearer {token}
```

### 제안서 상세 조회
특정 제안서 상세 정보

```http
GET /api/proposals/{proposal_id}
Authorization: Bearer {token}
```

### 제안서 낙찰
제안서 낙찰 처리 (대행사/관리자만)

```http
POST /api/proposals/{proposal_id}/award
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "final_price": 40000000
}
```

### 제안서 유찰
제안서 유찰 처리

```http
POST /api/proposals/{proposal_id}/reject
Authorization: Bearer {token}
```

### 예비 순위 설정
제안서 예비 순위 설정

```http
PATCH /api/proposals/{proposal_id}/set-reserve-rank
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "reserve_rank": 1
}
```

### 예비 제안서 승격
예비 제안서를 정식 낙찰자로 승격

```http
POST /api/proposals/{proposal_id}/promote-from-reserve
Authorization: Bearer {token}
```

---

## 계약 관리 API

### 계약 목록 조회
계약 목록 조회

```http
GET /api/contracts
Authorization: Bearer {token}
```

### 계약 상세 조회
특정 계약 상세 정보

```http
GET /api/contracts/{contract_id}
Authorization: Bearer {token}
```

### 계약 결제 상태 업데이트
계약의 결제 상태 업데이트

```http
PATCH /api/contracts/{contract_id}/update-payment-status
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "payment_status": "prepayment_paid"
}
```

**결제 상태:**
- `pending`: 대기 중
- `prepayment_paid`: 선금 지급됨
- `balance_paid`: 잔금 지급됨
- `all_paid`: 모두 지급됨

---

## 스케줄 관리 API

### 스케줄 목록 조회
프로젝트/공고별 스케줄 목록

```http
GET /api/schedules?schedulable_type=App\Models\Project&schedulable_id={project_id}
Authorization: Bearer {token}
```

**Query Parameters:**
- `schedulable_type`: `App\Models\Project` 또는 `App\Models\Announcement`
- `schedulable_id`: 프로젝트 또는 공고 ID

### 스케줄 생성
새 스케줄 생성

```http
POST /api/schedules
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "schedulable_type": "App\\Models\\Project",
  "schedulable_id": "01234567-89ab-cdef-0123-456789abcdef",
  "title": "무대 설치",
  "description": "메인 무대 설치 작업",
  "start_datetime": "2024-02-01T08:00:00Z",
  "end_datetime": "2024-02-01T12:00:00Z",
  "location": "코엑스 홀 A",
  "status": "planned",
  "type": "installation"
}
```

**스케줄 타입:**
- `meeting`, `delivery`, `installation`, `dismantling`, `rehearsal`
- `event_execution`, `setup`, `testing`, `load_in`, `load_out`
- `storage`, `breakdown`, `cleaning`, `training`, `briefing`
- `pickup`, `transportation`, `site_visit`, `concept_meeting`
- `technical_rehearsal`, `dress_rehearsal`, `final_inspection`, `wrap_up`

### 스케줄 상세 조회
특정 스케줄 상세 정보

```http
GET /api/schedules/{schedule_id}
Authorization: Bearer {token}
```

### 스케줄 수정
스케줄 정보 수정

```http
PUT /api/schedules/{schedule_id}
Authorization: Bearer {token}
```

### 스케줄 삭제
스케줄 삭제

```http
DELETE /api/schedules/{schedule_id}
Authorization: Bearer {token}
```

### 스케줄 첨부파일 업로드
스케줄에 파일 첨부

```http
POST /api/schedules/{schedule_id}/attachments
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Form Data:**
- `file`: 업로드할 파일 (이미지, 최대 10MB)

### 스케줄 첨부파일 목록
스케줄의 첨부파일 목록 조회

```http
GET /api/schedules/{schedule_id}/attachments
Authorization: Bearer {token}
```

### 첨부파일 다운로드
첨부파일 다운로드

```http
GET /api/schedule-attachments/{attachment_id}
Authorization: Bearer {token}
```

### 첨부파일 삭제
첨부파일 삭제

```http
DELETE /api/schedule-attachments/{attachment_id}
Authorization: Bearer {token}
```

---

## 평가 관리 API

### 심사위원 배정
공고에 심사위원 배정

```http
POST /api/announcements/{announcement_id}/assign-evaluators
Authorization: Bearer {token}
```

### 점수 제출
제안서에 평가 점수 제출

```http
POST /api/proposals/{proposal_id}/submit-score
Authorization: Bearer {token}
```

### 평가 현황 조회
공고별 평가 현황 조회

```http
GET /api/announcements/{announcement_id}/evaluation-summary
Authorization: Bearer {token}
```

### 내 평가 과제 조회
현재 사용자의 평가 과제 목록

```http
GET /api/my-evaluations
Authorization: Bearer {token}
```

---

## 요소 정의 API

### 요소 정의 목록
RFP 요소 정의 목록 조회

```http
GET /api/element-definitions
Authorization: Bearer {token}
```

### 요소 정의 생성
새 요소 정의 생성 (관리자만)

```http
POST /api/element-definitions
Authorization: Bearer {token}
```

### 요소 정의 수정
요소 정의 수정 (관리자만)

```http
PUT /api/element-definitions/{element_id}
Authorization: Bearer {token}
```

### 요소 정의 삭제
요소 정의 삭제 (관리자만)

```http
DELETE /api/element-definitions/{element_id}
Authorization: Bearer {token}
```

---

## 데이터 모델

### RFP 상태 (current_status)
- `draft`: 초안
- `approval_pending`: 결재 대기
- `approved`: 승인됨
- `rejected`: 반려됨
- `published`: 공고됨
- `closed`: 마감됨

### 공고 상태 (status)
- `open`: 열림
- `closed`: 닫힘
- `awarded`: 낙찰됨

### 제안서 상태 (status)
- `submitted`: 제출됨
- `under_review`: 검토 중
- `awarded`: 낙찰됨
- `rejected`: 거절됨

### 스케줄 상태 (status)
- `planned`: 계획됨
- `ongoing`: 진행 중
- `completed`: 완료됨
- `cancelled`: 취소됨

---

## 에러 처리

### HTTP 상태 코드
- `200`: 성공
- `201`: 생성 성공
- `400`: 잘못된 요청
- `401`: 인증 실패
- `403`: 권한 없음
- `404`: 리소스를 찾을 수 없음
- `409`: 상태 충돌
- `422`: 유효성 검사 실패
- `500`: 서버 오류

### 에러 응답 형식
```json
{
  "message": "에러 메시지",
  "errors": {
    "field_name": ["필드별 상세 에러 메시지"]
  }
}
```

---

## 개발 환경 설정

### 로컬 개발 서버 실행
```bash
cd backend
php artisan serve
```

### 데이터베이스 초기화
```bash
php artisan migrate:fresh --seed
```

### 테스트 계정
- **관리자**: `admin@bidly.com` / `bidlyadmin123!`
- **대행사**: `agency-a-master@example.com` / `password123!`
- **용역사**: `vendor-x-master@example.com` / `password123!`

### API 테스트
```bash
php artisan test
```

### Swagger 문서 확인
- URL: `http://localhost:8000/api/documentation`

---

## 📞 지원

개발 관련 문의사항이 있으시면 백엔드 팀에 연락해주세요.

- **이메일**: support@bidly.com
- **개발팀**: Backend Development Team

---

**마지막 업데이트**: 2025년 6월 27일  
**API 버전**: 1.0.0 