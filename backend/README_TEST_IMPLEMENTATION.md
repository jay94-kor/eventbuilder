# Backend Test Implementation Guide

## 🧪 테스트 코드 구현 현황

### 생성된 테스트 파일
- `tests/Feature/RfpApiTest.php` - RFP 관리 API 테스트
- `tests/Feature/AnnouncementApiTest.php` - 공고 관리 API 테스트
- `tests/Feature/ProposalApiTest.php` - 제안서 관리 API 테스트
- `tests/Feature/EvaluationApiTest.php` - 평가 시스템 API 테스트
- `tests/Feature/ScheduleApiTest.php` - 스케줄 관리 API 테스트
- `tests/Unit/NotificationServiceTest.php` - 알림 서비스 유닛 테스트

### 테스트 실행을 위한 Factory 파일 생성 필요

테스트를 실행하기 전에 다음 Factory 파일들을 생성해야 합니다:

```bash
# Factory 파일 생성
php artisan make:factory AgencyFactory
php artisan make:factory VendorFactory
php artisan make:factory ProjectFactory
php artisan make:factory RfpFactory
php artisan make:factory AnnouncementFactory
php artisan make:factory ProposalFactory
php artisan make:factory ContractFactory
php artisan make:factory ScheduleFactory
php artisan make:factory EvaluationFactory
php artisan make:factory ElementDefinitionFactory
php artisan make:factory RfpElementFactory
```

### 예시 Factory 구현

```php
// database/factories/AgencyFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AgencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact_email' => $this->faker->email(),
            'contact_phone' => $this->faker->phoneNumber(),
            'specialties' => ['stage', 'lighting', 'sound'],
            'approval_status' => 'approved',
        ];
    }
}
```

### 테스트 실행 명령어

```bash
# 전체 Feature 테스트 실행
php artisan test --testsuite=Feature

# 특정 테스트 파일 실행
php artisan test tests/Feature/RfpApiTest.php

# 테스트 커버리지 확인 (Xdebug 필요)
php artisan test --coverage
```

## 📊 테스트 커버리지 목표

### 핵심 비즈니스 로직 (목표: 95%+)
- ✅ RFP 생성 및 관리
- ✅ 공고 발행 (통합/분리 발주)
- ✅ 제안서 제출 및 낙찰/유찰
- ✅ 평가 시스템 (심사위원 배정, 점수 제출)
- ✅ 계약 관리
- ✅ 스케줄 관리

### 권한 검증 (목표: 100%)
- ✅ 사용자 타입별 접근 제어
- ✅ 대행사/용역사 격리
- ✅ 관리자 권한 검증

### 예외 처리 (목표: 90%+)
- ✅ 유효성 검사 실패
- ✅ 상태 충돌 (중복 제출, 마감 후 제출 등)
- ✅ 권한 없는 접근
- ✅ 트랜잭션 롤백

## 🔧 추가 구현 권장사항

### 1. 통합 테스트
- 전체 입찰 프로세스 end-to-end 테스트
- 다중 사용자 시나리오 테스트

### 2. 성능 테스트
- 대량 데이터 처리 테스트
- 동시 접속 테스트

### 3. 보안 테스트
- SQL Injection 방지 테스트
- CSRF 보호 테스트
- 권한 우회 시도 테스트

## 📝 테스트 Best Practices

1. **Arrange-Act-Assert 패턴** 사용
2. **테스트 격리**: 각 테스트는 독립적으로 실행
3. **의미있는 테스트명**: 테스트 목적이 명확히 드러나는 이름
4. **Edge Cases 포함**: 경계 조건과 예외 상황 테스트
5. **데이터베이스 트랜잭션**: 테스트 후 데이터 정리

## 🚀 다음 단계

1. Factory 파일 생성 및 구현
2. 테스트 실행 및 디버깅
3. 커버리지 측정 및 부족한 부분 보강
4. CI/CD 파이프라인에 테스트 통합 