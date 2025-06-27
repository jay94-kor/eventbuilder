--
-- PostgreSQL database dump
--

-- Dumped from database version 14.18 (Homebrew)
-- Dumped by pg_dump version 14.18 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: announcement_channel_type_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.announcement_channel_type_enum AS ENUM (
    'agency_private',
    'public'
);


--
-- Name: TYPE announcement_channel_type_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.announcement_channel_type_enum IS '공고 채널 타입: 대행사 전용 채널, 공용 채널';


--
-- Name: announcement_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.announcement_status_enum AS ENUM (
    'open',
    'closed',
    'awarded'
);


--
-- Name: TYPE announcement_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.announcement_status_enum IS '공고 상태: 열림, 닫힘, 낙찰됨';


--
-- Name: approval_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.approval_status_enum AS ENUM (
    'pending',
    'approved',
    'rejected'
);


--
-- Name: TYPE approval_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.approval_status_enum IS '결재 상태: 대기 중, 승인됨, 반려됨';


--
-- Name: evaluator_assignment_type_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.evaluator_assignment_type_enum AS ENUM (
    'random',
    'designated'
);


--
-- Name: TYPE evaluator_assignment_type_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.evaluator_assignment_type_enum IS '심사위원 배정 방식: 무작위 배정, 지정 배정';


--
-- Name: notification_type_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.notification_type_enum AS ENUM (
    'new_bid_available',
    'proposal_submitted',
    'rfp_approved',
    'contract_status_update',
    'rfp_rejected',
    'announcement_closed',
    'evaluation_step_passed',
    'evaluation_step_failed',
    'reserve_rank_assigned',
    'meeting_date_proposed',
    'meeting_date_selected'
);


--
-- Name: TYPE notification_type_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.notification_type_enum IS '알림 타입: 새 공고, 제안서 제출, RFP 승인됨, 계약 상태 업데이트, RFP 반려됨, 공고 마감됨';


--
-- Name: payment_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.payment_status_enum AS ENUM (
    'pending',
    'prepayment_paid',
    'balance_paid',
    'all_paid'
);


--
-- Name: TYPE payment_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.payment_status_enum IS '계약 대금 지급 상태: 대기 중, 선금 지급됨, 잔금 지급됨, 모두 지급됨';


--
-- Name: proposal_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.proposal_status_enum AS ENUM (
    'submitted',
    'under_review',
    'awarded',
    'rejected'
);


--
-- Name: TYPE proposal_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.proposal_status_enum IS '제안서 상태: 제출됨, 검토 중, 낙찰됨, 거절됨';


--
-- Name: rfp_element_type_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.rfp_element_type_enum AS ENUM (
    'stage',
    'sound',
    'lighting',
    'casting',
    'security',
    'video',
    'photo',
    'electric',
    'transport',
    'printing',
    'LED_screen',
    'equipment_rental'
);


--
-- Name: TYPE rfp_element_type_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.rfp_element_type_enum IS 'RFP 요소 타입: 무대, 음향, 조명, 섭외, 경호/의전/안전, 영상, 사진, 전기, 운송, 인쇄, LED 전광판, 물품 대여';


--
-- Name: rfp_issue_type_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.rfp_issue_type_enum AS ENUM (
    'integrated',
    'separated_by_element',
    'separated_by_group'
);


--
-- Name: TYPE rfp_issue_type_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.rfp_issue_type_enum IS 'RFP 발주 타입: 통합 발주, 요소별 분리 발주, 부분 묶음 발주';


--
-- Name: rfp_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.rfp_status_enum AS ENUM (
    'draft',
    'approval_pending',
    'approved',
    'rejected',
    'published',
    'closed'
);


--
-- Name: TYPE rfp_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.rfp_status_enum IS 'RFP 상태: 초안, 결재 대기, 승인됨, 반려됨, 공고 중, 마감됨';


--
-- Name: schedule_activity_type_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.schedule_activity_type_enum AS ENUM (
    'meeting',
    'delivery',
    'installation',
    'dismantling',
    'rehearsal',
    'event_execution',
    'setup',
    'testing',
    'load_in',
    'load_out',
    'storage',
    'breakdown',
    'cleaning',
    'training',
    'briefing',
    'pickup',
    'transportation',
    'site_visit',
    'concept_meeting',
    'technical_rehearsal',
    'dress_rehearsal',
    'final_inspection',
    'wrap_up'
);


--
-- Name: TYPE schedule_activity_type_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.schedule_activity_type_enum IS '스케줄 활동 유형: 회의, 배송, 설치, 철거, 리허설, 행사 실행 등';


--
-- Name: schedule_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.schedule_status_enum AS ENUM (
    'planned',
    'ongoing',
    'completed',
    'cancelled'
);


--
-- Name: TYPE schedule_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.schedule_status_enum IS '스케줄 상태: 계획됨, 진행 중, 완료됨, 취소됨';


--
-- Name: subscription_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.subscription_status_enum AS ENUM (
    'active',
    'inactive',
    'trial_expired',
    'payment_pending'
);


--
-- Name: TYPE subscription_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.subscription_status_enum IS '대행사 구독 상태: 활성, 비활성, 체험 기간 만료, 결제 대기 중';


--
-- Name: user_type_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.user_type_enum AS ENUM (
    'agency_member',
    'vendor_member',
    'admin'
);


--
-- Name: TYPE user_type_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.user_type_enum IS '사용자 타입: 대행사 직원, 용역사 직원, 관리자';


--
-- Name: vendor_account_status_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.vendor_account_status_enum AS ENUM (
    'active',
    'suspended',
    'permanently_banned'
);


--
-- Name: TYPE vendor_account_status_enum; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TYPE public.vendor_account_status_enum IS '용역사 계정 상태: 활성, 일시 정지됨, 영구 제명됨';


--
-- Name: update_timestamp(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.update_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: agencies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.agencies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    business_registration_number character varying(20) NOT NULL,
    address text,
    master_user_id uuid,
    subscription_status public.subscription_status_enum DEFAULT 'inactive'::public.subscription_status_enum NOT NULL,
    subscription_end_date timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE agencies; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.agencies IS '대행사 정보를 저장하는 테이블';


--
-- Name: COLUMN agencies.name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agencies.name IS '대행사 이름';


--
-- Name: COLUMN agencies.business_registration_number; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agencies.business_registration_number IS '사업자 등록 번호';


--
-- Name: COLUMN agencies.address; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agencies.address IS '대행사 주소';


--
-- Name: COLUMN agencies.master_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agencies.master_user_id IS '해당 대행사를 관리하는 마스터 사용자 ID';


--
-- Name: COLUMN agencies.subscription_status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agencies.subscription_status IS '대행사 구독 상태 (SUBSCRIPTION_STATUS_ENUM)';


--
-- Name: COLUMN agencies.subscription_end_date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agencies.subscription_end_date IS '구독 만료일';


--
-- Name: agency_approved_vendors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.agency_approved_vendors (
    agency_id uuid NOT NULL,
    vendor_id uuid NOT NULL,
    approved_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE agency_approved_vendors; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.agency_approved_vendors IS '특정 대행사가 승인한 용역사 목록을 관리하여 대행사 전용 채널 접근을 제어하는 테이블';


--
-- Name: COLUMN agency_approved_vendors.agency_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_approved_vendors.agency_id IS '대행사 ID (FK)';


--
-- Name: COLUMN agency_approved_vendors.vendor_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_approved_vendors.vendor_id IS '승인된 용역사 ID (FK)';


--
-- Name: COLUMN agency_approved_vendors.approved_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_approved_vendors.approved_at IS '승인된 일시';


--
-- Name: agency_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.agency_members (
    user_id uuid NOT NULL,
    agency_id uuid NOT NULL,
    "position" character varying(255),
    permissions jsonb DEFAULT '{}'::jsonb NOT NULL,
    joined_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE agency_members; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.agency_members IS '사용자와 대행사를 연결하고, 대행사 내에서의 멤버 정보를 저장하는 테이블';


--
-- Name: COLUMN agency_members.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_members.user_id IS '사용자 ID (FK)';


--
-- Name: COLUMN agency_members.agency_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_members.agency_id IS '대행사 ID (FK)';


--
-- Name: COLUMN agency_members."position"; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_members."position" IS '대행사 내 직책';


--
-- Name: COLUMN agency_members.permissions; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_members.permissions IS '해당 멤버에게 부여된 세부 권한 (JSONB)';


--
-- Name: COLUMN agency_members.joined_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.agency_members.joined_at IS '대행사에 합류한 일시';


--
-- Name: announcement_evaluators; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.announcement_evaluators (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    announcement_id uuid NOT NULL,
    user_id uuid NOT NULL,
    assignment_type public.evaluator_assignment_type_enum DEFAULT 'designated'::public.evaluator_assignment_type_enum NOT NULL,
    assigned_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    scope_type character varying(255) DEFAULT 'announcement'::character varying NOT NULL,
    CONSTRAINT announcement_evaluators_scope_type_check CHECK (((scope_type)::text = ANY ((ARRAY['project'::character varying, 'rfp'::character varying, 'announcement'::character varying])::text[])))
);


--
-- Name: TABLE announcement_evaluators; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.announcement_evaluators IS '공고에 배정된 심사위원 정보를 저장하는 테이블';


--
-- Name: COLUMN announcement_evaluators.announcement_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcement_evaluators.announcement_id IS '공고 ID (FK)';


--
-- Name: COLUMN announcement_evaluators.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcement_evaluators.user_id IS '심사위원 사용자 ID (FK)';


--
-- Name: COLUMN announcement_evaluators.assignment_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcement_evaluators.assignment_type IS '배정 방식 (EVALUATOR_ASSIGNMENT_TYPE_ENUM)';


--
-- Name: COLUMN announcement_evaluators.assigned_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcement_evaluators.assigned_at IS '심사위원 배정 일시';


--
-- Name: COLUMN announcement_evaluators.scope_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcement_evaluators.scope_type IS '배정 범위 (project: 프로젝트 전체, rfp: RFP 전체, announcement: 개별 공고)';


--
-- Name: announcements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.announcements (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    rfp_id uuid NOT NULL,
    rfp_element_id uuid,
    agency_id uuid NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    estimated_price numeric(15,2),
    closing_at timestamp(0) without time zone NOT NULL,
    channel_type public.announcement_channel_type_enum NOT NULL,
    contact_info_private boolean DEFAULT false NOT NULL,
    published_at timestamp(0) without time zone,
    status public.announcement_status_enum DEFAULT 'open'::public.announcement_status_enum NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    evaluation_criteria jsonb DEFAULT '{}'::jsonb NOT NULL,
    evaluation_steps json
);


--
-- Name: TABLE announcements; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.announcements IS '용역사에게 공개되는 입찰 공고 정보를 저장하는 테이블';


--
-- Name: COLUMN announcements.rfp_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.rfp_id IS '연결된 RFP ID (FK)';


--
-- Name: COLUMN announcements.rfp_element_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.rfp_element_id IS '특정 RFP 요소/그룹 ID (분리 발주 시) (FK)';


--
-- Name: COLUMN announcements.agency_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.agency_id IS '공고를 올린 대행사 ID (FK)';


--
-- Name: COLUMN announcements.title; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.title IS '공고 제목';


--
-- Name: COLUMN announcements.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.description IS '공고 상세 설명';


--
-- Name: COLUMN announcements.estimated_price; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.estimated_price IS '공고에 제시된 예상 금액';


--
-- Name: COLUMN announcements.closing_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.closing_at IS '제안서 제출 마감 일시';


--
-- Name: COLUMN announcements.channel_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.channel_type IS '공고 채널 타입 (ANNOUNCEMENT_CHANNEL_TYPE_ENUM)';


--
-- Name: COLUMN announcements.contact_info_private; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.contact_info_private IS '연락처 비공개 여부';


--
-- Name: COLUMN announcements.published_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.published_at IS '공고 게시 일시';


--
-- Name: COLUMN announcements.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.status IS '공고 상태 (ANNOUNCEMENT_STATUS_ENUM)';


--
-- Name: COLUMN announcements.evaluation_criteria; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.evaluation_criteria IS '공고 평가 기준 (점수 비중, 가격 점수 규칙 등)';


--
-- Name: COLUMN announcements.evaluation_steps; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.announcements.evaluation_steps IS '평가/협상 단계별 일정 정보 (JSON 배열)';


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: TABLE cache; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.cache IS 'Laravel 캐시 데이터를 저장하는 테이블';


--
-- Name: contracts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.contracts (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    announcement_id uuid NOT NULL,
    proposal_id uuid NOT NULL,
    vendor_id uuid NOT NULL,
    final_price numeric(15,2) NOT NULL,
    contract_file_path character varying(255),
    contract_signed_at timestamp(0) without time zone,
    prepayment_amount numeric(15,2),
    prepayment_paid_at timestamp(0) without time zone,
    balance_amount numeric(15,2),
    balance_paid_at timestamp(0) without time zone,
    payment_status public.payment_status_enum DEFAULT 'pending'::public.payment_status_enum NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    proposed_meeting_dates json,
    selected_meeting_date timestamp(0) without time zone,
    meeting_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    CONSTRAINT contracts_meeting_status_check CHECK (((meeting_status)::text = ANY ((ARRAY['pending'::character varying, 'dates_proposed'::character varying, 'date_selected'::character varying, 'meeting_completed'::character varying])::text[])))
);


--
-- Name: TABLE contracts; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.contracts IS '낙찰된 제안에 대한 계약 정보를 저장하는 테이블';


--
-- Name: COLUMN contracts.announcement_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.announcement_id IS '연결된 공고 ID (FK)';


--
-- Name: COLUMN contracts.proposal_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.proposal_id IS '낙찰된 단 하나의 제안서 ID (FK)';


--
-- Name: COLUMN contracts.vendor_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.vendor_id IS '계약된 용역사 ID (FK)';


--
-- Name: COLUMN contracts.final_price; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.final_price IS '최종 계약 금액';


--
-- Name: COLUMN contracts.contract_file_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.contract_file_path IS '계약서 파일 경로';


--
-- Name: COLUMN contracts.contract_signed_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.contract_signed_at IS '계약 체결 일시';


--
-- Name: COLUMN contracts.prepayment_amount; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.prepayment_amount IS '실제 선금 지급액';


--
-- Name: COLUMN contracts.prepayment_paid_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.prepayment_paid_at IS '선금 지급 완료 일시';


--
-- Name: COLUMN contracts.balance_amount; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.balance_amount IS '실제 잔금 지급액';


--
-- Name: COLUMN contracts.balance_paid_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.balance_paid_at IS '잔금 지급 완료 일시';


--
-- Name: COLUMN contracts.payment_status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.payment_status IS '대금 지급 상태 (PAYMENT_STATUS_ENUM)';


--
-- Name: COLUMN contracts.proposed_meeting_dates; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.proposed_meeting_dates IS '대행사가 제안한 미팅 일정들 (JSON 배열)';


--
-- Name: COLUMN contracts.selected_meeting_date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.selected_meeting_date IS '용역사가 선택한 최종 미팅 일정';


--
-- Name: COLUMN contracts.meeting_status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.contracts.meeting_status IS '미팅 진행 상태';


--
-- Name: element_definitions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.element_definitions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    element_type public.rfp_element_type_enum NOT NULL,
    display_name character varying(255) NOT NULL,
    description text,
    input_schema jsonb DEFAULT '{}'::jsonb NOT NULL,
    default_details_template jsonb DEFAULT '{}'::jsonb NOT NULL,
    recommended_elements jsonb DEFAULT '[]'::jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE element_definitions; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.element_definitions IS '운영자가 RFP 요소의 종류, 상세 스펙 입력 필드, 추천 조합 등을 관리하는 테이블';


--
-- Name: COLUMN element_definitions.element_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.element_definitions.element_type IS 'RFP 요소 타입 (RFP_ELEMENT_TYPE_ENUM)';


--
-- Name: COLUMN element_definitions.display_name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.element_definitions.display_name IS 'UI에 표시될 요소의 이름';


--
-- Name: COLUMN element_definitions.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.element_definitions.description IS '요소에 대한 설명';


--
-- Name: COLUMN element_definitions.input_schema; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.element_definitions.input_schema IS '해당 요소의 상세 스펙 입력 폼 정의 (JSON Schema)';


--
-- Name: COLUMN element_definitions.default_details_template; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.element_definitions.default_details_template IS 'rfp_elements.details 필드에 들어갈 기본 JSON 값 템플릿';


--
-- Name: COLUMN element_definitions.recommended_elements; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.element_definitions.recommended_elements IS '이 요소를 선택했을 때 추천할 다른 요소들의 목록';


--
-- Name: evaluations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.evaluations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    proposal_id uuid NOT NULL,
    evaluator_user_id uuid NOT NULL,
    price_score numeric(5,2),
    portfolio_score numeric(5,2),
    additional_score numeric(5,2),
    comment text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE evaluations; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.evaluations IS '심사위원이 제안서에 부여한 점수 및 평가 정보를 저장하는 테이블';


--
-- Name: COLUMN evaluations.proposal_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluations.proposal_id IS '평가 대상 제안서 ID (FK)';


--
-- Name: COLUMN evaluations.evaluator_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluations.evaluator_user_id IS '심사위원 사용자 ID (FK)';


--
-- Name: COLUMN evaluations.price_score; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluations.price_score IS '가격 점수 (0-100)';


--
-- Name: COLUMN evaluations.portfolio_score; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluations.portfolio_score IS '포트폴리오 점수 (0-100)';


--
-- Name: COLUMN evaluations.additional_score; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluations.additional_score IS '추가 제안 점수 (0-100)';


--
-- Name: COLUMN evaluations.comment; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluations.comment IS '심사 의견/코멘트';


--
-- Name: evaluator_histories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.evaluator_histories (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    evaluator_user_id uuid NOT NULL,
    announcement_id uuid NOT NULL,
    proposal_id uuid NOT NULL,
    element_type character varying(255) NOT NULL,
    project_id uuid NOT NULL,
    project_name character varying(255) NOT NULL,
    evaluation_score numeric(5,2),
    evaluation_completed boolean DEFAULT false NOT NULL,
    evaluation_completed_at timestamp(0) without time zone,
    evaluation_notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE evaluator_histories; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.evaluator_histories IS '심사위원의 평가 이력을 저장하여 전문성 추적 및 추천 시스템에 활용';


--
-- Name: COLUMN evaluator_histories.evaluator_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.evaluator_user_id IS '심사위원 사용자 ID (FK)';


--
-- Name: COLUMN evaluator_histories.announcement_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.announcement_id IS '평가한 공고 ID (FK)';


--
-- Name: COLUMN evaluator_histories.proposal_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.proposal_id IS '평가한 제안서 ID (FK)';


--
-- Name: COLUMN evaluator_histories.element_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.element_type IS '평가한 요소 타입 (stage, sound, lighting 등)';


--
-- Name: COLUMN evaluator_histories.project_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.project_id IS '해당 프로젝트 ID (FK)';


--
-- Name: COLUMN evaluator_histories.project_name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.project_name IS '프로젝트명 (검색 최적화용)';


--
-- Name: COLUMN evaluator_histories.evaluation_score; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.evaluation_score IS '부여한 총점';


--
-- Name: COLUMN evaluator_histories.evaluation_completed; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.evaluation_completed IS '평가 완료 여부';


--
-- Name: COLUMN evaluator_histories.evaluation_completed_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.evaluation_completed_at IS '평가 완료 일시';


--
-- Name: COLUMN evaluator_histories.evaluation_notes; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.evaluator_histories.evaluation_notes IS '평가 관련 메모';


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts integer NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: TABLE jobs; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.jobs IS 'Laravel 큐 작업 데이터를 저장하는 테이블';


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid NOT NULL,
    type public.notification_type_enum NOT NULL,
    message text NOT NULL,
    is_read boolean DEFAULT false NOT NULL,
    related_id uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    data json,
    read_at timestamp(0) without time zone
);


--
-- Name: TABLE notifications; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.notifications IS '사용자에게 전송되는 알림 정보를 저장하는 테이블';


--
-- Name: COLUMN notifications.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notifications.user_id IS '알림을 받을 사용자 ID (FK)';


--
-- Name: COLUMN notifications.type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notifications.type IS '알림 타입 (NOTIFICATION_TYPE_ENUM)';


--
-- Name: COLUMN notifications.message; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notifications.message IS '알림 메시지 내용';


--
-- Name: COLUMN notifications.is_read; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notifications.is_read IS '알림 확인 여부';


--
-- Name: COLUMN notifications.related_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notifications.related_id IS '관련 레코드의 ID (예: rfp_id, announcement_id)';


--
-- Name: COLUMN notifications.data; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.notifications.data IS '알림 관련 추가 데이터 (JSON 객체)';


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE personal_access_tokens; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.personal_access_tokens IS '개인 접근 토큰을 저장하는 테이블';


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: projects; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.projects (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    project_name character varying(255) NOT NULL,
    start_datetime timestamp(0) without time zone NOT NULL,
    end_datetime timestamp(0) without time zone NOT NULL,
    preparation_start_datetime timestamp(0) without time zone,
    "철수_end_datetime" timestamp(0) without time zone,
    client_name character varying(255),
    client_contact_person character varying(255),
    client_contact_number character varying(20),
    main_agency_contact_user_id uuid NOT NULL,
    sub_agency_contact_user_id uuid,
    agency_id uuid NOT NULL,
    is_indoor boolean NOT NULL,
    location character varying(255) NOT NULL,
    budget_including_vat numeric(15,2),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE projects; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.projects IS '행사의 기본 정보를 저장하는 테이블';


--
-- Name: COLUMN projects.project_name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.project_name IS '행사 이름';


--
-- Name: COLUMN projects.start_datetime; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.start_datetime IS '행사 시작 일시';


--
-- Name: COLUMN projects.end_datetime; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.end_datetime IS '행사 종료 일시';


--
-- Name: COLUMN projects.preparation_start_datetime; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.preparation_start_datetime IS '준비 시작 일시';


--
-- Name: COLUMN projects."철수_end_datetime"; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects."철수_end_datetime" IS '철수 마감 일시';


--
-- Name: COLUMN projects.client_name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.client_name IS '클라이언트 이름';


--
-- Name: COLUMN projects.client_contact_person; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.client_contact_person IS '클라이언트 담당자';


--
-- Name: COLUMN projects.client_contact_number; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.client_contact_number IS '클라이언트 담당자 연락처';


--
-- Name: COLUMN projects.main_agency_contact_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.main_agency_contact_user_id IS '사내 행사 담당자 (정) ID';


--
-- Name: COLUMN projects.sub_agency_contact_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.sub_agency_contact_user_id IS '사내 행사 담당자 (부) ID';


--
-- Name: COLUMN projects.agency_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.agency_id IS '해당 프로젝트를 생성한 대행사 ID (FK)';


--
-- Name: COLUMN projects.is_indoor; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.is_indoor IS '행사 실내/실외 여부 (true: 실내, false: 실외)';


--
-- Name: COLUMN projects.location; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.location IS '행사 장소';


--
-- Name: COLUMN projects.budget_including_vat; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.projects.budget_including_vat IS '총 예산 (부가세 포함)';


--
-- Name: proposals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.proposals (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    announcement_id uuid NOT NULL,
    vendor_id uuid NOT NULL,
    proposed_price numeric(15,2),
    proposal_text text,
    proposal_file_path character varying(255),
    status public.proposal_status_enum DEFAULT 'submitted'::public.proposal_status_enum NOT NULL,
    reserve_rank integer,
    submitted_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    evaluation_process_status json,
    presentation_order integer,
    presentation_scheduled_at timestamp(0) without time zone,
    presentation_duration_minutes integer
);


--
-- Name: TABLE proposals; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.proposals IS '용역사가 입찰 공고에 제출한 제안서 정보를 저장하는 테이블';


--
-- Name: COLUMN proposals.announcement_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.announcement_id IS '연결된 공고 ID (FK)';


--
-- Name: COLUMN proposals.vendor_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.vendor_id IS '제안서를 제출한 용역사 ID (FK)';


--
-- Name: COLUMN proposals.proposed_price; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.proposed_price IS '용역사가 제안한 금액';


--
-- Name: COLUMN proposals.proposal_text; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.proposal_text IS '제안서 내용 (텍스트)';


--
-- Name: COLUMN proposals.proposal_file_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.proposal_file_path IS '첨부된 제안서 파일의 저장 경로';


--
-- Name: COLUMN proposals.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.status IS '제안서 상태 (PROPOSAL_STATUS_ENUM)';


--
-- Name: COLUMN proposals.reserve_rank; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.reserve_rank IS '예비 순위 (1, 2, 3, ...)';


--
-- Name: COLUMN proposals.submitted_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.submitted_at IS '제안서 제출 일시';


--
-- Name: COLUMN proposals.evaluation_process_status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.evaluation_process_status IS '단계별 평가 진행 상태 (JSON 객체)';


--
-- Name: COLUMN proposals.presentation_order; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.presentation_order IS '발표 순서 (랜덤 배정)';


--
-- Name: COLUMN proposals.presentation_scheduled_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.presentation_scheduled_at IS '발표 예정 일시';


--
-- Name: COLUMN proposals.presentation_duration_minutes; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.proposals.presentation_duration_minutes IS '발표 할당 시간(분)';


--
-- Name: rfp_approvals; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rfp_approvals (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    rfp_id uuid NOT NULL,
    approver_user_id uuid NOT NULL,
    status public.approval_status_enum DEFAULT 'pending'::public.approval_status_enum NOT NULL,
    comment text,
    approved_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE rfp_approvals; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.rfp_approvals IS 'RFP에 대한 결재 이력을 저장하는 테이블';


--
-- Name: COLUMN rfp_approvals.rfp_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_approvals.rfp_id IS '결재 대상 RFP ID (FK)';


--
-- Name: COLUMN rfp_approvals.approver_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_approvals.approver_user_id IS '결재자 사용자 ID (FK)';


--
-- Name: COLUMN rfp_approvals.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_approvals.status IS '결재 상태 (APPROVAL_STATUS_ENUM)';


--
-- Name: COLUMN rfp_approvals.comment; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_approvals.comment IS '결재 시 남긴 코멘트 (예: 반려 사유)';


--
-- Name: COLUMN rfp_approvals.approved_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_approvals.approved_at IS '결재 완료 일시';


--
-- Name: rfp_elements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rfp_elements (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    rfp_id uuid NOT NULL,
    element_type public.rfp_element_type_enum NOT NULL,
    details jsonb,
    allocated_budget numeric(15,2),
    prepayment_ratio numeric(5,2),
    prepayment_due_date timestamp(0) without time zone,
    balance_ratio numeric(5,2),
    balance_due_date timestamp(0) without time zone,
    parent_rfp_element_id uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE rfp_elements; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.rfp_elements IS '각 RFP에 포함된 요소(무대, 음향 등)의 상세 정보를 저장하는 테이블';


--
-- Name: COLUMN rfp_elements.rfp_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.rfp_id IS '연결된 RFP ID (FK)';


--
-- Name: COLUMN rfp_elements.element_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.element_type IS '요소 타입 (RFP_ELEMENT_TYPE_ENUM)';


--
-- Name: COLUMN rfp_elements.details; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.details IS '각 요소별 상세 스펙 (JSONB)';


--
-- Name: COLUMN rfp_elements.allocated_budget; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.allocated_budget IS '이 요소에 배정된 예산';


--
-- Name: COLUMN rfp_elements.prepayment_ratio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.prepayment_ratio IS '선금 비율 (0~1 사이 값)';


--
-- Name: COLUMN rfp_elements.prepayment_due_date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.prepayment_due_date IS '선금 지급 예정일';


--
-- Name: COLUMN rfp_elements.balance_ratio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.balance_ratio IS '잔금 비율 (0~1 사이 값)';


--
-- Name: COLUMN rfp_elements.balance_due_date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.balance_due_date IS '잔금 지급 예정일';


--
-- Name: COLUMN rfp_elements.parent_rfp_element_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfp_elements.parent_rfp_element_id IS '부분 묶음 발주 시, 이 요소가 속한 그룹의 대표 요소 ID';


--
-- Name: rfps; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rfps (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    project_id uuid NOT NULL,
    current_status public.rfp_status_enum DEFAULT 'draft'::public.rfp_status_enum NOT NULL,
    created_by_user_id uuid NOT NULL,
    agency_id uuid NOT NULL,
    issue_type public.rfp_issue_type_enum NOT NULL,
    rfp_description text,
    closing_at timestamp(0) without time zone NOT NULL,
    published_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_client_name_public boolean DEFAULT true NOT NULL,
    is_budget_public boolean DEFAULT false NOT NULL
);


--
-- Name: TABLE rfps; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.rfps IS '제안요청서(RFP)의 메타 정보를 저장하는 테이블';


--
-- Name: COLUMN rfps.project_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.project_id IS '연결된 프로젝트 ID (FK)';


--
-- Name: COLUMN rfps.current_status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.current_status IS 'RFP 현재 상태 (RFP_STATUS_ENUM)';


--
-- Name: COLUMN rfps.created_by_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.created_by_user_id IS 'RFP를 생성한 사용자 ID (FK)';


--
-- Name: COLUMN rfps.agency_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.agency_id IS '해당 RFP를 생성한 대행사 ID (FK)';


--
-- Name: COLUMN rfps.issue_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.issue_type IS 'RFP 발주 타입 (RFP_ISSUE_TYPE_ENUM)';


--
-- Name: COLUMN rfps.rfp_description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.rfp_description IS '최종 생성된 RFP의 요약된 설명';


--
-- Name: COLUMN rfps.closing_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.closing_at IS '공고 마감 일시';


--
-- Name: COLUMN rfps.published_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.published_at IS '공고 게시 일시';


--
-- Name: COLUMN rfps.is_client_name_public; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.is_client_name_public IS '클라이언트명 공개 여부 (true: 공개, false: 비공개)';


--
-- Name: COLUMN rfps.is_budget_public; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.rfps.is_budget_public IS '예산 공개 여부 (true: 공개, false: 비공개)';


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    agency_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    permissions jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE roles; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.roles IS '대행사별 역할(Role) 정의 및 관련 권한을 저장하는 테이블';


--
-- Name: COLUMN roles.agency_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.roles.agency_id IS '해당 역할이 속한 대행사 ID (FK)';


--
-- Name: COLUMN roles.name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.roles.name IS '역할 이름 (예: RFP 편집자, 결재자)';


--
-- Name: COLUMN roles.permissions; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.roles.permissions IS '이 역할에 부여된 세부 권한 (JSONB)';


--
-- Name: schedule_attachments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedule_attachments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    schedule_id uuid NOT NULL,
    user_id uuid NOT NULL,
    file_path character varying(255) NOT NULL,
    file_name character varying(255) NOT NULL,
    file_type character varying(255) NOT NULL,
    file_size bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE schedule_attachments; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.schedule_attachments IS '스케줄에 첨부된 파일 정보를 저장하는 테이블';


--
-- Name: COLUMN schedule_attachments.schedule_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_attachments.schedule_id IS '스케줄 ID (FK)';


--
-- Name: COLUMN schedule_attachments.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_attachments.user_id IS '업로드한 사용자 ID (FK)';


--
-- Name: COLUMN schedule_attachments.file_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_attachments.file_path IS '파일 저장 경로';


--
-- Name: COLUMN schedule_attachments.file_name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_attachments.file_name IS '원본 파일명';


--
-- Name: COLUMN schedule_attachments.file_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_attachments.file_type IS '파일 MIME 타입';


--
-- Name: COLUMN schedule_attachments.file_size; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_attachments.file_size IS '파일 크기 (bytes)';


--
-- Name: schedule_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedule_logs (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    schedule_id uuid NOT NULL,
    user_id uuid,
    action character varying(255) NOT NULL,
    old_values jsonb,
    new_values jsonb,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE schedule_logs; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.schedule_logs IS '스케줄 변경 이력을 저장하는 테이블';


--
-- Name: COLUMN schedule_logs.schedule_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_logs.schedule_id IS '이력이 기록된 스케줄 ID (FK)';


--
-- Name: COLUMN schedule_logs.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_logs.user_id IS '변경을 수행한 사용자 ID (FK)';


--
-- Name: COLUMN schedule_logs.action; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_logs.action IS '수행된 작업 (예: created, updated, deleted, status_changed)';


--
-- Name: COLUMN schedule_logs.old_values; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_logs.old_values IS '변경 전 값들 (JSONB)';


--
-- Name: COLUMN schedule_logs.new_values; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedule_logs.new_values IS '변경 후 값들 (JSONB)';


--
-- Name: schedules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schedules (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    schedulable_type character varying(255) NOT NULL,
    schedulable_id uuid NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    start_datetime timestamp(0) without time zone NOT NULL,
    end_datetime timestamp(0) without time zone NOT NULL,
    location character varying(255),
    status public.schedule_status_enum DEFAULT 'planned'::public.schedule_status_enum NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    type public.schedule_activity_type_enum
);


--
-- Name: TABLE schedules; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.schedules IS '프로젝트 또는 공고에 대한 스케줄 정보를 저장하는 테이블';


--
-- Name: COLUMN schedules.title; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedules.title IS '스케줄 제목';


--
-- Name: COLUMN schedules.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedules.description IS '스케줄 상세 설명';


--
-- Name: COLUMN schedules.start_datetime; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedules.start_datetime IS '스케줄 시작 일시';


--
-- Name: COLUMN schedules.end_datetime; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedules.end_datetime IS '스케줄 종료 일시';


--
-- Name: COLUMN schedules.location; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedules.location IS '스케줄 장소';


--
-- Name: COLUMN schedules.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedules.status IS '스케줄 상태';


--
-- Name: COLUMN schedules.type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.schedules.type IS '스케줄 활동 유형';


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id uuid,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: TABLE sessions; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.sessions IS '사용자 세션 정보를 저장하는 테이블';


--
-- Name: user_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_roles (
    user_id uuid NOT NULL,
    role_id uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE user_roles; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.user_roles IS '사용자에게 할당된 역할을 연결하는 테이블';


--
-- Name: COLUMN user_roles.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.user_roles.user_id IS '사용자 ID (FK)';


--
-- Name: COLUMN user_roles.role_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.user_roles.role_id IS '역할 ID (FK)';


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    phone_number character varying(20),
    user_type public.user_type_enum NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE users; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.users IS '모든 시스템 사용자 정보를 저장하는 테이블';


--
-- Name: COLUMN users.email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.email IS '사용자 이메일 (로그인 ID)';


--
-- Name: COLUMN users.password; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.password IS '비밀번호 (해시값)';


--
-- Name: COLUMN users.name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.name IS '사용자 이름';


--
-- Name: COLUMN users.phone_number; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.phone_number IS '사용자 전화번호';


--
-- Name: COLUMN users.user_type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.users.user_type IS '사용자 타입 (USER_TYPE_ENUM)';


--
-- Name: vendor_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendor_members (
    user_id uuid NOT NULL,
    vendor_id uuid NOT NULL,
    "position" character varying(255),
    joined_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE vendor_members; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.vendor_members IS '사용자와 용역사를 연결하고, 용역사 내에서의 멤버 정보를 저장하는 테이블';


--
-- Name: COLUMN vendor_members.user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendor_members.user_id IS '사용자 ID (FK)';


--
-- Name: COLUMN vendor_members.vendor_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendor_members.vendor_id IS '용역사 ID (FK)';


--
-- Name: COLUMN vendor_members."position"; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendor_members."position" IS '용역사 내 직책';


--
-- Name: COLUMN vendor_members.joined_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendor_members.joined_at IS '용역사에 합류한 일시';


--
-- Name: vendors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendors (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    business_registration_number character varying(20) NOT NULL,
    address text,
    description text,
    specialties jsonb DEFAULT '[]'::jsonb NOT NULL,
    master_user_id uuid,
    status public.vendor_account_status_enum DEFAULT 'active'::public.vendor_account_status_enum NOT NULL,
    ban_reason text,
    banned_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: TABLE vendors; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.vendors IS '용역사 정보를 저장하는 테이블';


--
-- Name: COLUMN vendors.name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.name IS '용역사 이름';


--
-- Name: COLUMN vendors.business_registration_number; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.business_registration_number IS '사업자 등록 번호';


--
-- Name: COLUMN vendors.address; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.address IS '용역사 주소';


--
-- Name: COLUMN vendors.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.description IS '용역사 상세 설명';


--
-- Name: COLUMN vendors.specialties; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.specialties IS '용역사가 제공 가능한 서비스 요소 (JSONB 배열)';


--
-- Name: COLUMN vendors.master_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.master_user_id IS '용역사 마스터 사용자 ID';


--
-- Name: COLUMN vendors.status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.status IS '용역사 계정 상태 (VENDOR_ACCOUNT_STATUS_ENUM)';


--
-- Name: COLUMN vendors.ban_reason; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.ban_reason IS '계정 제명 사유';


--
-- Name: COLUMN vendors.banned_at; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.vendors.banned_at IS '계정 제명 일시';


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: agencies agencies_business_registration_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agencies
    ADD CONSTRAINT agencies_business_registration_number_unique UNIQUE (business_registration_number);


--
-- Name: agencies agencies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agencies
    ADD CONSTRAINT agencies_pkey PRIMARY KEY (id);


--
-- Name: agency_approved_vendors agency_approved_vendors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agency_approved_vendors
    ADD CONSTRAINT agency_approved_vendors_pkey PRIMARY KEY (agency_id, vendor_id);


--
-- Name: agency_members agency_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agency_members
    ADD CONSTRAINT agency_members_pkey PRIMARY KEY (user_id, agency_id);


--
-- Name: announcement_evaluators announcement_evaluators_announcement_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_evaluators
    ADD CONSTRAINT announcement_evaluators_announcement_id_user_id_unique UNIQUE (announcement_id, user_id);


--
-- Name: announcement_evaluators announcement_evaluators_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_evaluators
    ADD CONSTRAINT announcement_evaluators_pkey PRIMARY KEY (id);


--
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (id);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: contracts contracts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_pkey PRIMARY KEY (id);


--
-- Name: contracts contracts_proposal_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_proposal_id_unique UNIQUE (proposal_id);


--
-- Name: element_definitions element_definitions_element_type_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.element_definitions
    ADD CONSTRAINT element_definitions_element_type_unique UNIQUE (element_type);


--
-- Name: element_definitions element_definitions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.element_definitions
    ADD CONSTRAINT element_definitions_pkey PRIMARY KEY (id);


--
-- Name: evaluations evaluations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluations
    ADD CONSTRAINT evaluations_pkey PRIMARY KEY (id);


--
-- Name: evaluations evaluations_proposal_id_evaluator_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluations
    ADD CONSTRAINT evaluations_proposal_id_evaluator_user_id_unique UNIQUE (proposal_id, evaluator_user_id);


--
-- Name: evaluator_histories evaluator_histories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluator_histories
    ADD CONSTRAINT evaluator_histories_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: proposals proposals_announcement_id_vendor_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proposals
    ADD CONSTRAINT proposals_announcement_id_vendor_id_unique UNIQUE (announcement_id, vendor_id);


--
-- Name: proposals proposals_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proposals
    ADD CONSTRAINT proposals_pkey PRIMARY KEY (id);


--
-- Name: rfp_approvals rfp_approvals_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfp_approvals
    ADD CONSTRAINT rfp_approvals_pkey PRIMARY KEY (id);


--
-- Name: rfp_elements rfp_elements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfp_elements
    ADD CONSTRAINT rfp_elements_pkey PRIMARY KEY (id);


--
-- Name: rfps rfps_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfps
    ADD CONSTRAINT rfps_pkey PRIMARY KEY (id);


--
-- Name: roles roles_agency_id_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_agency_id_name_unique UNIQUE (agency_id, name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: schedule_attachments schedule_attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_attachments
    ADD CONSTRAINT schedule_attachments_pkey PRIMARY KEY (id);


--
-- Name: schedule_logs schedule_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_logs
    ADD CONSTRAINT schedule_logs_pkey PRIMARY KEY (id);


--
-- Name: schedules schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: user_roles user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (user_id, role_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vendor_members vendor_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_members
    ADD CONSTRAINT vendor_members_pkey PRIMARY KEY (user_id, vendor_id);


--
-- Name: vendors vendors_business_registration_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendors
    ADD CONSTRAINT vendors_business_registration_number_unique UNIQUE (business_registration_number);


--
-- Name: vendors vendors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendors
    ADD CONSTRAINT vendors_pkey PRIMARY KEY (id);


--
-- Name: evaluator_histories_element_type_evaluation_completed_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX evaluator_histories_element_type_evaluation_completed_index ON public.evaluator_histories USING btree (element_type, evaluation_completed);


--
-- Name: evaluator_histories_evaluator_user_id_element_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX evaluator_histories_evaluator_user_id_element_type_index ON public.evaluator_histories USING btree (evaluator_user_id, element_type);


--
-- Name: evaluator_histories_evaluator_user_id_evaluation_completed_at_i; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX evaluator_histories_evaluator_user_id_evaluation_completed_at_i ON public.evaluator_histories USING btree (evaluator_user_id, evaluation_completed_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: schedule_attachments_schedule_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX schedule_attachments_schedule_id_index ON public.schedule_attachments USING btree (schedule_id);


--
-- Name: schedule_attachments_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX schedule_attachments_user_id_index ON public.schedule_attachments USING btree (user_id);


--
-- Name: schedules_schedulable_type_schedulable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX schedules_schedulable_type_schedulable_id_index ON public.schedules USING btree (schedulable_type, schedulable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: agencies agencies_master_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agencies
    ADD CONSTRAINT agencies_master_user_id_foreign FOREIGN KEY (master_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: agency_approved_vendors agency_approved_vendors_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agency_approved_vendors
    ADD CONSTRAINT agency_approved_vendors_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id) ON DELETE CASCADE;


--
-- Name: agency_approved_vendors agency_approved_vendors_vendor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agency_approved_vendors
    ADD CONSTRAINT agency_approved_vendors_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES public.vendors(id) ON DELETE CASCADE;


--
-- Name: agency_members agency_members_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agency_members
    ADD CONSTRAINT agency_members_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id) ON DELETE CASCADE;


--
-- Name: agency_members agency_members_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.agency_members
    ADD CONSTRAINT agency_members_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: announcement_evaluators announcement_evaluators_announcement_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_evaluators
    ADD CONSTRAINT announcement_evaluators_announcement_id_foreign FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- Name: announcement_evaluators announcement_evaluators_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_evaluators
    ADD CONSTRAINT announcement_evaluators_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: announcements announcements_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id) ON DELETE CASCADE;


--
-- Name: announcements announcements_rfp_element_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_rfp_element_id_foreign FOREIGN KEY (rfp_element_id) REFERENCES public.rfp_elements(id) ON DELETE SET NULL;


--
-- Name: announcements announcements_rfp_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_rfp_id_foreign FOREIGN KEY (rfp_id) REFERENCES public.rfps(id) ON DELETE CASCADE;


--
-- Name: contracts contracts_announcement_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_announcement_id_foreign FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- Name: contracts contracts_proposal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_proposal_id_foreign FOREIGN KEY (proposal_id) REFERENCES public.proposals(id) ON DELETE RESTRICT;


--
-- Name: contracts contracts_vendor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES public.vendors(id) ON DELETE RESTRICT;


--
-- Name: evaluations evaluations_evaluator_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluations
    ADD CONSTRAINT evaluations_evaluator_user_id_foreign FOREIGN KEY (evaluator_user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: evaluations evaluations_proposal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluations
    ADD CONSTRAINT evaluations_proposal_id_foreign FOREIGN KEY (proposal_id) REFERENCES public.proposals(id) ON DELETE CASCADE;


--
-- Name: evaluator_histories evaluator_histories_announcement_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluator_histories
    ADD CONSTRAINT evaluator_histories_announcement_id_foreign FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- Name: evaluator_histories evaluator_histories_evaluator_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluator_histories
    ADD CONSTRAINT evaluator_histories_evaluator_user_id_foreign FOREIGN KEY (evaluator_user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: evaluator_histories evaluator_histories_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluator_histories
    ADD CONSTRAINT evaluator_histories_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: evaluator_histories evaluator_histories_proposal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evaluator_histories
    ADD CONSTRAINT evaluator_histories_proposal_id_foreign FOREIGN KEY (proposal_id) REFERENCES public.proposals(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: projects projects_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id) ON DELETE CASCADE;


--
-- Name: projects projects_main_agency_contact_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_main_agency_contact_user_id_foreign FOREIGN KEY (main_agency_contact_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: projects projects_sub_agency_contact_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_sub_agency_contact_user_id_foreign FOREIGN KEY (sub_agency_contact_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: proposals proposals_announcement_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proposals
    ADD CONSTRAINT proposals_announcement_id_foreign FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- Name: proposals proposals_vendor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proposals
    ADD CONSTRAINT proposals_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES public.vendors(id) ON DELETE RESTRICT;


--
-- Name: rfp_approvals rfp_approvals_approver_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfp_approvals
    ADD CONSTRAINT rfp_approvals_approver_user_id_foreign FOREIGN KEY (approver_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: rfp_approvals rfp_approvals_rfp_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfp_approvals
    ADD CONSTRAINT rfp_approvals_rfp_id_foreign FOREIGN KEY (rfp_id) REFERENCES public.rfps(id) ON DELETE CASCADE;


--
-- Name: rfp_elements rfp_elements_parent_rfp_element_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfp_elements
    ADD CONSTRAINT rfp_elements_parent_rfp_element_id_foreign FOREIGN KEY (parent_rfp_element_id) REFERENCES public.rfp_elements(id) ON DELETE SET NULL;


--
-- Name: rfp_elements rfp_elements_rfp_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfp_elements
    ADD CONSTRAINT rfp_elements_rfp_id_foreign FOREIGN KEY (rfp_id) REFERENCES public.rfps(id) ON DELETE CASCADE;


--
-- Name: rfps rfps_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfps
    ADD CONSTRAINT rfps_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id) ON DELETE CASCADE;


--
-- Name: rfps rfps_created_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfps
    ADD CONSTRAINT rfps_created_by_user_id_foreign FOREIGN KEY (created_by_user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- Name: rfps rfps_project_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rfps
    ADD CONSTRAINT rfps_project_id_foreign FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: roles roles_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id) ON DELETE CASCADE;


--
-- Name: schedule_attachments schedule_attachments_schedule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_attachments
    ADD CONSTRAINT schedule_attachments_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES public.schedules(id) ON DELETE CASCADE;


--
-- Name: schedule_attachments schedule_attachments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_attachments
    ADD CONSTRAINT schedule_attachments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: schedule_logs schedule_logs_schedule_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_logs
    ADD CONSTRAINT schedule_logs_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES public.schedules(id) ON DELETE CASCADE;


--
-- Name: schedule_logs schedule_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schedule_logs
    ADD CONSTRAINT schedule_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: user_roles user_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: user_roles user_roles_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vendor_members vendor_members_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_members
    ADD CONSTRAINT vendor_members_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: vendor_members vendor_members_vendor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendor_members
    ADD CONSTRAINT vendor_members_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES public.vendors(id) ON DELETE CASCADE;


--
-- Name: vendors vendors_master_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendors
    ADD CONSTRAINT vendors_master_user_id_foreign FOREIGN KEY (master_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

--
-- PostgreSQL database dump
--

-- Dumped from database version 14.18 (Homebrew)
-- Dumped by pg_dump version 14.18 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2025_06_26_020207_create_bidly_enum_types	1
2	2025_06_26_020217_create_users_table_for_bidly	1
3	2025_06_26_020224_create_agencies_table_for_bidly	1
4	2025_06_26_020231_create_vendors_table_for_bidly	1
5	2025_06_26_020239_create_agency_members_table_for_bidly	1
6	2025_06_26_020246_create_vendor_members_table_for_bidly	1
7	2025_06_26_020253_create_roles_table_for_bidly	1
8	2025_06_26_020259_create_user_roles_table_for_bidly	1
9	2025_06_26_020306_create_agency_approved_vendors_table_for_bidly	1
10	2025_06_26_020424_create_element_definitions_table_for_bidly	1
11	2025_06_26_020432_create_projects_table_for_bidly	1
12	2025_06_26_020440_create_rfps_table_for_bidly	1
13	2025_06_26_020448_create_rfp_elements_table_for_bidly	1
14	2025_06_26_020457_create_rfp_approvals_table_for_bidly	1
15	2025_06_26_020630_create_sessions_table	1
16	2025_06_26_020637_create_cache_table	1
17	2025_06_26_020643_create_jobs_table	1
18	2025_06_26_024815_create_personal_access_tokens_table	1
19	2025_06_26_034250_create_announcements_table_for_bidly	1
20	2025_06_26_034324_create_proposals_table_for_bidly	1
21	2025_06_26_034354_create_contracts_table_for_bidly	1
22	2025_06_26_034423_create_notifications_table_for_bidly	1
23	2025_06_26_035117_add_evaluation_criteria_to_announcements_table	1
24	2025_06_26_035352_create_evaluations_table	1
25	2025_06_26_035419_create_announcement_evaluators_table	1
26	2025_06_26_054032_create_schedules_table	1
27	2025_06_26_055941_create_schedule_logs_table	1
28	2025_06_26_060008_add_type_column_to_schedules_table	1
29	2025_06_27_062711_create_schedule_attachments_table	2
30	2025_06_27_070000_create_evaluator_histories_table	3
31	2025_06_27_080000_add_privacy_fields_to_rfps_table	4
32	2025_06_27_081000_add_scope_type_to_announcement_evaluators_table	5
33	2025_06_27_090000_add_evaluation_steps_to_announcements_table	6
34	2025_06_27_091000_add_evaluation_process_to_proposals_table	6
35	2025_06_27_092000_add_meeting_fields_to_contracts_table	6
36	2025_06_27_093000_create_notifications_table	7
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 36, true);


--
-- PostgreSQL database dump complete
--

