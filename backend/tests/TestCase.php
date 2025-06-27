<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // DB 파사드 추가 (선택 사항이지만 안전을 위해)
use Illuminate\Contracts\Console\Kernel;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * 마이그레이션 실행 여부를 추적하는 정적 변수
     */
    protected static $testMigrated = false;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * RefreshDatabase 트레잇의 refreshTestDatabase 메서드를 오버라이드하여
     * personal_access_tokens 테이블 문제를 근본적으로 해결합니다.
     */
    protected function refreshTestDatabase()
    {
        if (! static::$testMigrated) {
            // 1. 기본 마이그레이션 실행
            $this->artisan('migrate:fresh', [
                '--database' => $this->app->make('config')->get('database.default'),
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
            ]);

            // 2. personal_access_tokens 테이블이 제대로 생성되었는지 확인하고 필요시 재생성
            $this->ensurePersonalAccessTokensTable();

            $this->app[Kernel::class]->setArtisan(null);

            static::$testMigrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /**
     * personal_access_tokens 테이블이 올바른 스키마로 생성되었는지 확인하고 필요시 재생성합니다.
     */
    protected function ensurePersonalAccessTokensTable()
    {
        try {
            // 테이블이 존재하는지 확인
            if (!Schema::hasTable('personal_access_tokens')) {
                // 테이블이 없으면 우리의 마이그레이션으로 생성
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/2025_06_26_024815_create_personal_access_tokens_table.php',
                    '--force' => true,
                ]);
            } else {
                // 테이블이 있지만 스키마가 올바른지 확인
                $columns = Schema::getColumnListing('personal_access_tokens');
                
                // tokenable_id 컬럼의 타입을 확인
                $tokenableIdType = DB::select("
                    SELECT data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'personal_access_tokens' 
                    AND column_name = 'tokenable_id'
                ")[0]->data_type ?? null;

                // UUID 타입이 아니면 테이블을 재생성
                if ($tokenableIdType !== 'uuid') {
                    Schema::drop('personal_access_tokens');
                    Artisan::call('migrate', [
                        '--path' => 'database/migrations/2025_06_26_024815_create_personal_access_tokens_table.php',
                        '--force' => true,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // 오류 발생 시 테이블을 삭제하고 재생성
            if (Schema::hasTable('personal_access_tokens')) {
                Schema::drop('personal_access_tokens');
            }
            
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_06_26_024815_create_personal_access_tokens_table.php',
                '--force' => true,
            ]);
        }
    }
}