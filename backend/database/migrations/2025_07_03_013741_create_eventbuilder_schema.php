<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ========== 1. ENUM 타입 생성 ==========
        DB::statement("CREATE TYPE user_type_enum AS ENUM ('agency_member', 'vendor_member', 'admin')");
        DB::statement("CREATE TYPE account_status_enum AS ENUM ('pending', 'approved', 'rejected', 'suspended')");
        DB::statement("CREATE TYPE subscription_status_enum AS ENUM ('active', 'inactive', 'suspended')");
        DB::statement("CREATE TYPE vendor_status_enum AS ENUM ('active', 'inactive', 'banned')");
        DB::statement("CREATE TYPE rfp_status_enum AS ENUM ('draft', 'published', 'closed', 'cancelled')");
        DB::statement("CREATE TYPE approval_status_enum AS ENUM ('pending', 'approved', 'rejected')");
        DB::statement("CREATE TYPE issue_type_enum AS ENUM ('integrated', 'separated_by_element', 'separated_by_group')");
        DB::statement("CREATE TYPE announcement_status_enum AS ENUM ('active', 'closed', 'cancelled')");
        DB::statement("CREATE TYPE proposal_status_enum AS ENUM ('submitted', 'under_review', 'awarded', 'rejected', 'reserve')");
        DB::statement("CREATE TYPE contract_status_enum AS ENUM ('pending', 'active', 'completed', 'cancelled')");
        DB::statement("CREATE TYPE payment_status_enum AS ENUM ('pending', 'partial', 'completed', 'overdue')");
        DB::statement("CREATE TYPE schedule_type_enum AS ENUM ('meeting', 'site_visit', 'setup', 'event', 'breakdown', 'delivery')");
        DB::statement("CREATE TYPE schedule_status_enum AS ENUM ('pending', 'in_progress', 'completed', 'cancelled')");
        DB::statement("CREATE TYPE notification_type_enum AS ENUM ('rfp_published', 'proposal_submitted', 'contract_signed', 'payment_reminder', 'schedule_reminder')");

        // ========== 2. 사용자 및 조직 관련 테이블 ==========
        
        // 사용자 테이블
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone_number', 20)->nullable();
            $table->enum('user_type', ['agency_member', 'vendor_member', 'admin'])->default('agency_member');
            $table->enum('account_status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->string('position')->nullable();
            $table->string('job_title')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['user_type']);
            $table->index(['account_status']);
            $table->index(['email_verified_at']);
            $table->index(['approved_at']);
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // 대행사 테이블
        Schema::create('agencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('business_registration_number', 12)->unique();
            $table->text('address');
            $table->uuid('master_user_id');
            $table->enum('subscription_status', ['active', 'inactive', 'suspended'])->default('active');
            $table->date('subscription_end_date')->nullable();
            $table->timestamps();

            $table->foreign('master_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['subscription_status']);
        });

        // 용역사 테이블
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('business_registration_number', 12)->unique();
            $table->text('address');
            $table->text('description')->nullable();
            $table->json('specialties')->nullable();
            $table->uuid('master_user_id');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->timestamps();

            $table->foreign('master_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status']);
        });

        // 대행사 멤버 테이블
        Schema::create('agency_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('agency_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->unique(['user_id', 'agency_id']);
        });

        // 용역사 멤버 테이블
        Schema::create('vendor_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('vendor_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unique(['user_id', 'vendor_id']);
        });

        // 역할 테이블
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 사용자 역할 테이블
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        // 대행사 승인 용역사 테이블
        Schema::create('agency_approved_vendors', function (Blueprint $table) {
            $table->uuid('agency_id');
            $table->uuid('vendor_id');
            $table->timestamps();

            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->primary(['agency_id', 'vendor_id']);
        });

        // ========== 3. 카테고리 및 요소 정의 ==========
        
        // 카테고리 테이블
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->string('group')->nullable();
            $table->integer('popularity_score')->default(0);
            $table->timestamps();
            
            $table->index(['group']);
            $table->index(['popularity_score']);
        });

        // 요소 정의 테이블
        Schema::create('element_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('element_type')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('input_schema')->nullable();
            $table->json('default_details_template')->nullable();
            $table->json('recommended_elements')->nullable();
            $table->uuid('category_id')->nullable();
            $table->string('complexity_level')->default('medium');
            $table->json('event_types')->nullable();
            $table->integer('popularity_score')->default(0);
            
            // 동적 스펙 템플릿 관리 필드들
            $table->json('default_spec_template')->nullable();
            $table->json('quantity_config')->nullable();
            $table->json('variant_rules')->nullable();
            
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->index(['category_id']);
            $table->index(['complexity_level']);
            $table->index(['popularity_score']);
        });

        // 카테고리 추천 테이블
        Schema::create('category_recommendations', function (Blueprint $table) {
            $table->uuid('source_category_id');
            $table->uuid('recommended_category_id');
            $table->integer('recommendation_score')->default(1);
            $table->timestamps();

            $table->foreign('source_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('recommended_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->primary(['source_category_id', 'recommended_category_id'], 'category_recommendations_primary');
            $table->index(['recommendation_score']);
        });

        // 요소 추천 테이블
        Schema::create('element_recommendations', function (Blueprint $table) {
            $table->uuid('source_element_id');
            $table->uuid('recommended_element_id');
            $table->integer('recommendation_score')->default(1);
            $table->timestamps();

            $table->foreign('source_element_id')->references('id')->on('element_definitions')->onDelete('cascade');
            $table->foreign('recommended_element_id')->references('id')->on('element_definitions')->onDelete('cascade');
            $table->primary(['source_element_id', 'recommended_element_id'], 'element_recommendations_primary');
            $table->index(['recommendation_score']);
        });

        // 스마트 추천 규칙 테이블
        Schema::create('smart_recommendation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('rule_name');
            $table->json('conditions');
            $table->json('recommendations');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1);
            $table->timestamps();
            
            $table->index(['is_active', 'priority']);
        });

        // ========== 4. 프로젝트 및 RFP 관련 ==========
        
        // 프로젝트 테이블
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('project_name');
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->timestamp('preparation_start_datetime')->nullable();
            $table->timestamp('breakdown_end_datetime')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_contact_person')->nullable();
            $table->string('client_contact_number', 20)->nullable();
            $table->uuid('main_agency_contact_user_id');
            $table->uuid('sub_agency_contact_user_id')->nullable();
            $table->boolean('is_indoor')->default(true);
            $table->string('location');
            $table->decimal('budget_including_vat', 15, 2)->nullable();
            $table->uuid('agency_id');
            $table->timestamps();

            $table->foreign('main_agency_contact_user_id')->references('id')->on('users');
            $table->foreign('sub_agency_contact_user_id')->references('id')->on('users');
            $table->foreign('agency_id')->references('id')->on('agencies');
            $table->index(['agency_id']);
            $table->index(['start_datetime']);
        });

        // RFP 테이블
        Schema::create('rfps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->enum('status', ['draft', 'published', 'closed', 'cancelled'])->default('draft');
            $table->uuid('created_by_user_id');
            $table->uuid('agency_id');
            $table->enum('issue_type', ['integrated', 'separated_by_element', 'separated_by_group']);
            $table->text('rfp_description')->nullable();
            $table->timestamp('closing_at');
            $table->timestamp('published_at')->nullable();
            $table->json('selected_categories')->nullable();
            $table->json('selected_element_definitions')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('agency_id')->references('id')->on('agencies');
            $table->index(['status']);
            $table->index(['agency_id']);
            $table->index(['closing_at']);
            $table->index(['published_at']);
            $table->index(['agency_id', 'status']);
        });

        // RFP 요소 테이블
        Schema::create('rfp_elements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rfp_id');
            $table->uuid('element_definition_id')->nullable();
            $table->string('element_type');
            $table->json('specifications')->nullable();
            $table->text('special_requirements')->nullable();
            $table->decimal('allocated_budget', 15, 2)->nullable();
            $table->decimal('prepayment_ratio', 3, 2)->nullable();
            $table->date('prepayment_due_date')->nullable();
            $table->decimal('balance_ratio', 3, 2)->nullable();
            $table->date('balance_due_date')->nullable();
            $table->uuid('parent_rfp_element_id')->nullable();
            
            // 동적 스펙 관리 필드들
            $table->integer('total_quantity')->default(1);
            $table->integer('base_quantity')->default(1);
            $table->boolean('use_variants')->default(false);
            $table->json('spec_fields')->nullable();
            $table->json('spec_variants')->nullable();
            
            $table->timestamps();

            $table->foreign('rfp_id')->references('id')->on('rfps')->onDelete('cascade');
            $table->foreign('element_definition_id')->references('id')->on('element_definitions')->onDelete('set null');
            $table->foreign('parent_rfp_element_id')->references('id')->on('rfp_elements')->onDelete('cascade');
            $table->index(['rfp_id']);
            $table->index(['element_type']);
            $table->index(['rfp_id', 'element_type']);
        });

        // RFP 승인 테이블
        Schema::create('rfp_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rfp_id');
            $table->uuid('requested_by_user_id');
            $table->uuid('approved_by_user_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_notes')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('rfp_id')->references('id')->on('rfps')->onDelete('cascade');
            $table->foreign('requested_by_user_id')->references('id')->on('users');
            $table->foreign('approved_by_user_id')->references('id')->on('users');
            $table->index(['status']);
        });

        // ========== 5. 공고 및 제안서 관련 ==========
        
        // 공고 테이블
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rfp_id');
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');
            $table->timestamp('published_at');
            $table->timestamp('closing_at');
            $table->json('evaluation_criteria')->nullable();
            $table->timestamps();

            $table->foreign('rfp_id')->references('id')->on('rfps')->onDelete('cascade');
            $table->index(['status']);
            $table->index(['closing_at']);
        });

        // 제안서 테이블
        Schema::create('proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('announcement_id');
            $table->uuid('vendor_id');
            $table->uuid('submitted_by_user_id');
            $table->enum('status', ['submitted', 'under_review', 'awarded', 'rejected', 'reserve'])->default('submitted');
            $table->decimal('total_price', 15, 2);
            $table->json('proposal_details');
            $table->text('cover_letter')->nullable();
            $table->integer('reserve_rank')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('submitted_by_user_id')->references('id')->on('users');
            $table->index(['status']);
            $table->index(['vendor_id']);
            $table->index(['submitted_at']);
            $table->index(['vendor_id', 'status']);
            $table->unique(['announcement_id', 'vendor_id']);
        });

        // 평가 테이블
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('proposal_id');
            $table->uuid('evaluator_user_id');
            $table->json('scores');
            $table->text('comments')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();

            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('cascade');
            $table->foreign('evaluator_user_id')->references('id')->on('users');
            $table->unique(['proposal_id', 'evaluator_user_id']);
        });

        // 공고 평가자 테이블
        Schema::create('announcement_evaluators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('announcement_id');
            $table->uuid('user_id');
            $table->string('role')->default('evaluator');
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['announcement_id', 'user_id']);
        });

        // ========== 6. 계약 및 스케줄 관련 ==========
        
        // 계약 테이블
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('proposal_id');
            $table->uuid('agency_id');
            $table->uuid('vendor_id');
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->decimal('contract_amount', 15, 2);
            $table->json('contract_terms');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('signed_at')->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'completed', 'overdue'])->default('pending');
            $table->timestamps();

            $table->foreign('proposal_id')->references('id')->on('proposals');
            $table->foreign('agency_id')->references('id')->on('agencies');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['signed_at']);
            $table->index(['agency_id', 'status']);
        });

        // 스케줄 테이블
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['meeting', 'site_visit', 'setup', 'event', 'breakdown', 'delivery']);
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->string('location')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->json('attendees')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->index(['status']);
            $table->index(['start_time']);
            $table->index(['contract_id', 'type']);
            $table->index(['contract_id', 'status']);
        });

        // 스케줄 로그 테이블
        Schema::create('schedule_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schedule_id');
            $table->uuid('user_id');
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['schedule_id']);
        });

        // 스케줄 첨부파일 테이블
        Schema::create('schedule_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schedule_id');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->uuid('uploaded_by');
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users');
        });

        // ========== 7. 알림 및 시스템 테이블 ==========
        
        // 알림 테이블
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('type', ['rfp_published', 'proposal_submitted', 'contract_signed', 'payment_reminder', 'schedule_reminder']);
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_read']);
        });

        // ========== 8. Laravel 시스템 테이블 ==========
        
        // 세션 테이블
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 캐시 테이블
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // 작업 큐 테이블
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Personal Access Tokens 테이블 (Sanctum)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 외래키 제약조건 순서를 고려하여 역순으로 삭제
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('schedule_attachments');
        Schema::dropIfExists('schedule_logs');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('announcement_evaluators');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('rfp_approvals');
        Schema::dropIfExists('rfp_elements');
        Schema::dropIfExists('rfps');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('smart_recommendation_rules');
        Schema::dropIfExists('element_recommendations');
        Schema::dropIfExists('category_recommendations');
        Schema::dropIfExists('element_definitions');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('agency_approved_vendors');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('vendor_members');
        Schema::dropIfExists('agency_members');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('agencies');
        Schema::dropIfExists('users');

        // ENUM 타입 삭제
        DB::statement("DROP TYPE IF EXISTS notification_type_enum");
        DB::statement("DROP TYPE IF EXISTS schedule_status_enum");
        DB::statement("DROP TYPE IF EXISTS schedule_type_enum");
        DB::statement("DROP TYPE IF EXISTS payment_status_enum");
        DB::statement("DROP TYPE IF EXISTS contract_status_enum");
        DB::statement("DROP TYPE IF EXISTS proposal_status_enum");
        DB::statement("DROP TYPE IF EXISTS announcement_status_enum");
        DB::statement("DROP TYPE IF EXISTS issue_type_enum");
        DB::statement("DROP TYPE IF EXISTS approval_status_enum");
        DB::statement("DROP TYPE IF EXISTS rfp_status_enum");
        DB::statement("DROP TYPE IF EXISTS vendor_status_enum");
        DB::statement("DROP TYPE IF EXISTS subscription_status_enum");
        DB::statement("DROP TYPE IF EXISTS account_status_enum");
        DB::statement("DROP TYPE IF EXISTS user_type_enum");
    }
};