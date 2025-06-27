<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // Hash 파사드 추가
use Illuminate\Support\Facades\Auth; // Auth 파사드 임포트 확인
use Illuminate\Support\Facades\DB; // DB 파사드 추가

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 유효한 자격 증명으로 사용자가 로그인할 수 있는지 테스트합니다.
     *
     * @return void
     */
    public function test_user_can_login_with_valid_credentials()
    {
        // 1. 테스트 사용자 생성
        $user = User::factory()->create([
            'email' => 'test@bidly.com',
            'password' => Hash::make('password123'), // Hash::make() 사용
            'user_type' => 'admin',
        ]);

        // 2. 로그인 API 호출
        $response = $this->postJson('/api/login', [
            'email' => 'test@bidly.com',
            'password' => 'password123',
        ]);

        // 3. 응답 검증
        $response->assertStatus(200) // HTTP 상태 코드 200 (OK) 확인
                 ->assertJsonStructure([
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'user_type',
                     ],
                     'token',
                     'message',
                 ])
                 ->assertJson([
                     'user' => [
                         'email' => 'test@bidly.com',
                         'user_type' => 'admin',
                     ],
                     'message' => '로그인 성공',
                 ]);

        $this->assertNotNull($response->json('token')); // 토큰이 null이 아닌지 확인
    }

    /**
     * 유효하지 않은 자격 증명으로 사용자가 로그인할 수 없는지 테스트합니다.
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        // 1. 테스트 사용자 생성 (로그인 시도할 이메일은 존재하지만 비밀번호는 틀리게)
        $user = User::factory()->create([
            'email' => 'test_invalid@bidly.com', // 고유한 이메일 사용
            'password' => Hash::make('correctpassword'),
            'user_type' => 'vendor_member',
        ]);

        // 2. 잘못된 비밀번호로 로그인 API 호출
        $response = $this->postJson('/api/login', [
            'email' => 'test_invalid@bidly.com',
            'password' => 'wrongpassword', // 잘못된 비밀번호
        ]);

        // 3. 응답 검증
        $response->assertStatus(401) // HTTP 상태 코드 401 (Unauthorized) 확인
                 ->assertJson([
                     'message' => '인증 정보가 올바르지 않습니다.',
                 ]);
    }

    /**
     * 인증된 사용자가 자신의 정보를 조회할 수 있는지 테스트합니다.
     *
     * @return void
     */
    public function test_authenticated_user_can_get_their_details()
    {
        // 1. 테스트 사용자 생성 및 인증
        $user = User::factory()->create([
            'email' => 'auth_test@bidly.com',
            'user_type' => 'vendor_member',
        ]);

        // 2. 인증된 상태로 사용자 정보 API 호출
        $response = $this->actingAs($user)->getJson('/api/user');

        // 3. 응답 검증
        $response->assertStatus(200) // HTTP 상태 코드 200 (OK) 확인
                 ->assertJson([
                     'user' => [
                         'id' => $user->id,
                         'email' => 'auth_test@bidly.com',
                         'user_type' => 'vendor_member',
                     ],
                 ]);
    }

    /**
     * 인증되지 않은 사용자가 자신의 정보를 조회할 수 없는지 테스트합니다.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_get_details()
    {
        // 1. 인증되지 않은 상태로 API 호출
        $response = $this->getJson('/api/user');

        // 2. 응답 검증
        $response->assertStatus(401) // HTTP 상태 코드 401 (Unauthorized) 확인
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    /**
     * 인증된 사용자는 로그아웃할 수 있다.
     * 이 테스트는 API 토큰의 무효화를 명시적으로 검증합니다.
     *
     * @return void
     */
    public function test_authenticated_user_can_logout()
    {
        // 1. 테스트용 사용자 생성
        $user = User::factory()->create([
            'email' => 'logout_test@bidly.com',
            'password' => Hash::make('logoutpassword123!'),
            'user_type' => 'admin',
        ]);

        // 2. 실제 로그인 API 호출을 통해 토큰 발급
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'logout_test@bidly.com',
            'password' => 'logoutpassword123!',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');
        $this->assertNotNull($token, 'API 토큰이 발급되어야 합니다.');

        // 3. 발급받은 토큰으로 로그아웃 API 호출 (이 요청은 토큰을 DB에서 삭제)
        $logoutResponse = $this->postJson('/api/logout', [], ['Authorization' => 'Bearer ' . $token]);
        $logoutResponse->assertStatus(200)
                       ->assertJson(['message' => '로그아웃 성공']);

        // --- 디버깅용: 토큰이 DB에서 실제로 삭제되었는지 확인 ---
        // Sanctum 토큰 저장 방식에 맞춰 확인
        [$id, $plainToken] = explode('|', $token, 2);
        $hashedToken = hash('sha256', $plainToken);
        
        // 토큰이 실제로 삭제되었는지 확인
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'token' => $hashedToken,
        ]);
        
        // 해당 유저의 모든 토큰이 삭제되었는지 개수로 확인
        $tokenCount = DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->count();
        $this->assertEquals(0, $tokenCount, 'User should have no tokens after logout');
        // --- 디버깅용 코드 끝 ---

        // 4. ***추가: 테스트 환경 완전 초기화***
        //    이 부분은 Laravel의 내부 테스트 클라이언트가 세션/인증 상태를 유지하는 것을 방지합니다.
        Auth::forgetGuards(); // 모든 가드 상태를 초기화
        $this->app->forgetInstance('auth'); // Auth 서비스 인스턴스 초기화
        $this->refreshApplication(); // 애플리케이션 인스턴스 완전 새로고침

        // 5. 로그아웃된 토큰으로 다시 사용자 정보 조회 시도 (반드시 실패해야 함)
        //    완전히 새로운 요청으로 테스트
        $responseAfterLogout = $this->getJson('/api/user', ['Authorization' => 'Bearer ' . $token]);
        $responseAfterLogout->assertStatus(401)
                           ->assertJson(['message' => 'Unauthenticated.']);
    }
}
