# Event Builder API

이 프로젝트는 이벤트 빌더 웹 애플리케이션의 백엔드 API를 제공합니다. Laravel 프레임워크를 기반으로 구축되었으며, 이벤트 생성, 관리, 사용자 인증 및 기타 관련 기능을 지원합니다.

## 기술 스택

- **PHP**: ^8.2
- **Laravel Framework**: ^12.0
- **데이터베이스**: PostgreSQL (기본 설정)
- **API 문서**: L5 Swagger (^9.0)
- **관리자 패널**: Filament (^3.0)
- **인증**: Laravel Sanctum (^4.0)
- **프론트엔드 빌드**: Vite (^6.2.4), Tailwind CSS (^4.0.0)

## 환경 설정

`.env` 파일을 설정하여 애플리케이션 환경을 구성합니다. `.env.example` 파일을 복사하여 시작할 수 있습니다.

```bash
cp .env.example .env
```

`.env` 파일에서 다음 변수들을 설정합니다:

```dotenv
APP_NAME=EventBuilderAPI
APP_ENV=local
APP_KEY= # `php artisan key:generate` 명령어로 생성
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=event_builder
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# 기타 필요한 환경 변수 (예: SESSION_DRIVER, CACHE_STORE 등)
```

데이터베이스 연결 정보는 `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`를 프로젝트에 맞게 수정하세요.

## 설치 및 실행

1. **Composer 종속성 설치**:
   ```bash
   composer install
   ```

2. **애플리케이션 키 생성**:
   ```bash
   php artisan key:generate
   ```

3. **데이터베이스 마이그레이션 및 시드**:
   ```bash
   php artisan migrate --seed
   ```
   (시더가 없는 경우 `--seed`는 생략 가능)

4. **스토리지 링크 생성**:
   ```bash
   php artisan storage:link
   ```

5. **프론트엔드 자산 설치 및 빌드**:
   ```bash
   npm install
   npm run build
   ```

6. **개발 서버 실행**:
   ```bash
   php artisan serve
   ```
   또는 `composer.json`에 정의된 `dev` 스크립트를 사용하여 여러 개발 프로세스를 동시에 실행할 수 있습니다:
   ```bash
   npm run dev
   ```
   이 명령은 `php artisan serve`, `php artisan queue:listen`, `php artisan pail`, `npm run dev`를 동시에 실행합니다.

## API 문서

API 문서는 L5 Swagger를 통해 자동으로 생성됩니다. 개발 서버가 실행 중인 상태에서 다음 URL로 접근하여 API 문서를 확인할 수 있습니다:

`http://localhost:8000/api/documentation`

## 관리자 패널

Filament 관리자 패널은 다음 URL로 접근할 수 있습니다:

`http://localhost:8000/admin`

## 테스트

프로젝트 테스트는 PHPUnit을 사용합니다.

```bash
php artisan test
```

## 기여

기여에 대한 내용은 [CONTRIBUTING.md](CONTRIBUTING.md) 파일을 참조해주세요.

## 라이선스

이 프로젝트는 MIT 라이선스에 따라 배포됩니다. 자세한 내용은 [LICENSE](LICENSE) 파일을 참조하세요.
