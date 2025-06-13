# `darkaonline/l5-swagger`를 이용한 백엔드 문서화 계획

## 1. 패키지 설치 및 기본 설정
*   **목표**: `darkaonline/l5-swagger` 패키지를 설치하고 기본 설정을 완료합니다.
*   **세부 계획**:
    1.  Composer를 사용하여 `darkaonline/l5-swagger` 패키지를 설치합니다.
    2.  `php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"` 명령어를 실행하여 패키지의 설정 파일과 뷰 파일을 게시합니다.
    3.  `config/l5-swagger.php` 파일을 열어 문서 경로, API 기본 정보 등을 설정합니다.

## 2. 기본 어노테이션 구조 작성
*   **목표**: API 문서의 전체적인 구조를 정의하는 기본 어노테이션을 작성합니다.
*   **세부 계획**:
    1.  `app/Http/Controllers/Controller.php` 파일에 `@OA\Info`, `@OA\Server`, `@OA\SecurityScheme` 등의 기본 어노테이션을 추가하여 API의 제목, 버전, 서버 URL, 인증 방식 등을 정의합니다.

## 3. 컨트롤러 및 요청별 상세 문서화 (반복)
*   **목표**: 각 API 엔드포인트에 대한 상세 명세를 PHPDoc 어노테이션을 사용하여 작성합니다.
*   **세부 계획**:
    1.  **컨트롤러 어노테이션 작성**: 컨트롤러의 각 메소드 위에 `@OA\Get`, `@OA\Post` 등의 어노테이션을 사용하여 HTTP 메소드, 경로, 태그, 요약, 설명 등을 기술합니다.
    2.  **파라미터 및 요청/응답 본문 정의**: `@OA\Parameter`, `@OA\RequestBody`, `@OA\Response` 어노테이션을 사용하여 각 API가 필요로 하는 파라미터와 요청/응답 데이터의 구조를 상세히 정의합니다.
    3.  **스키마 정의**: 재사용 가능한 데이터 구조는 `@OA\Schema` 어노테이션을 사용하여 별도로 정의하고, 다른 어노테이션에서 `$ref`를 통해 참조할 수 있습니다. 이 스키마들은 `app/OpenApi/Schemas` 디렉토리를 생성하여 관리하겠습니다.

## 4. 문서 생성 및 확인
*   **목표**: 작성된 어노테이션을 기반으로 Swagger 문서를 생성하고 확인합니다.
*   **세부 계획**:
    1.  `php artisan l5-swagger:generate` 명령어를 실행하여 어노테이션을 파싱하고 문서를 생성합니다.
    2.  설정된 URL(기본값: `/api/documentation`)로 접속하여 내장된 Swagger UI를 통해 생성된 문서를 확인합니다.

## 전체 워크플로우 다이어그램

```mermaid
graph TD
    A[1. 패키지 설치 및 설정] --> B[2. 기본 어노테이션 작성];
    B --> C{AuthController 문서화 시작};
    C --> D[3a. Controller 메소드에 어노테이션 추가];
    D --> E[3b. 요청/응답 스키마 정의];
    E --> F[3c. 문서 생성 및 확인];
    F --> G{다음 컨트롤러 문서화};
    G --> D;
    F --> H[최종 문서 검토 및 개선];
    H --> D;