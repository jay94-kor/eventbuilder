<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Agency;
use App\Models\Vendor;
use App\Models\AgencyMember;
use App\Models\VendorMember;

/**
 * @OA\Tag(
 *     name="User Management",
 *     description="사용자 및 조직 관리 API"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/agency-members",
     *     summary="대행사 멤버 목록 조회",
     *     description="현재 사용자 소속 대행사의 멤버 목록을 조회합니다. 심사위원 배정 시 활용됩니다.",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="include_expertise",
     *         in="query",
     *         description="전문성 정보 포함 여부",
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="대행사 멤버 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="대행사 멤버 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(
     *                 property="members",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                     @OA\Property(property="name", type="string", example="김직원"),
     *                     @OA\Property(property="email", type="string", example="employee@agency.com"),
     *                     @OA\Property(property="phone_number", type="string", example="010-1234-5678"),
     *                     @OA\Property(
     *                         property="expertise",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="element_type", type="string", example="stage"),
     *                             @OA\Property(property="evaluation_count", type="integer", example=5),
     *                             @OA\Property(property="expertise_level", type="string", example="경험자")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음"),
     *     @OA\Response(response=404, description="소속 대행사를 찾을 수 없음")
     * )
     */
    public function getAgencyMembers(Request $request)
    {
        $user = Auth::user();

        // 대행사 멤버 또는 관리자만 접근 가능
        if ($user->user_type !== 'agency_member' && $user->user_type !== 'admin') {
            return response()->json([
                'message' => '대행사 멤버 목록은 대행사 소속 사용자만 조회할 수 있습니다.'
            ], 403);
        }

        // 현재 사용자의 대행사 ID 조회
        $agencyId = null;
        if ($user->user_type === 'agency_member') {
            $agencyMember = AgencyMember::where('user_id', $user->id)->first();
            if (!$agencyMember) {
                return response()->json(['message' => '소속 대행사를 찾을 수 없습니다.'], 404);
            }
            $agencyId = $agencyMember->agency_id;
        }

        // 대행사 멤버 목록 조회
        $membersQuery = User::whereHas('agency_members', function ($query) use ($agencyId) {
            if ($agencyId) {
                $query->where('agency_id', $agencyId);
            }
        })->where('user_type', 'agency_member');

        $includeExpertise = $request->query('include_expertise', false);

        if ($includeExpertise) {
            $membersQuery->with('evaluator_histories');
        }

        $members = $membersQuery->get();

        $formattedMembers = $members->map(function ($member) use ($includeExpertise) {
            $memberData = [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone_number' => $member->phone_number,
            ];

            if ($includeExpertise) {
                $expertise = $member->evaluator_histories()
                    ->where('evaluation_completed', true)
                    ->select('element_type')
                    ->selectRaw('COUNT(*) as evaluation_count')
                    ->selectRaw('AVG(evaluation_score) as avg_score')
                    ->groupBy('element_type')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'element_type' => $item->element_type,
                            'evaluation_count' => $item->evaluation_count,
                            'avg_score' => round($item->avg_score, 1),
                            'expertise_level' => $this->getExpertiseLevel($item->evaluation_count, $item->avg_score)
                        ];
                    });

                $memberData['expertise'] = $expertise;
            }

            return $memberData;
        });

        return response()->json([
            'message' => '대행사 멤버 목록을 성공적으로 불러왔습니다.',
            'members' => $formattedMembers,
            'total_count' => $formattedMembers->count()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/agencies",
     *     summary="대행사 목록 조회",
     *     description="시스템에 등록된 대행사 목록을 조회합니다. (관리자만)",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="대행사 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="대행사 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(
     *                 property="agencies",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                     @OA\Property(property="name", type="string", example="ABC 이벤트"),
     *                     @OA\Property(property="business_registration_number", type="string", example="123-45-67890"),
     *                     @OA\Property(property="subscription_status", type="string", example="active"),
     *                     @OA\Property(
     *                         property="master_user",
     *                         @OA\Property(property="name", type="string", example="김대표"),
     *                         @OA\Property(property="email", type="string", example="master@agency.com")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음")
     * )
     */
    public function getAgencies()
    {
        $user = Auth::user();

        // 관리자만 접근 가능
        if ($user->user_type !== 'admin') {
            return response()->json([
                'message' => '대행사 목록은 관리자만 조회할 수 있습니다.'
            ], 403);
        }

        $agencies = Agency::with('masterUser:id,name,email')
            ->select('id', 'name', 'business_registration_number', 'subscription_status', 'master_user_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedAgencies = $agencies->map(function ($agency) {
            return [
                'id' => $agency->id,
                'name' => $agency->name,
                'business_registration_number' => $agency->business_registration_number,
                'subscription_status' => $agency->subscription_status,
                'master_user' => $agency->masterUser ? [
                    'name' => $agency->masterUser->name,
                    'email' => $agency->masterUser->email,
                ] : null,
                'created_at' => $agency->created_at
            ];
        });

        return response()->json([
            'message' => '대행사 목록을 성공적으로 불러왔습니다.',
            'agencies' => $formattedAgencies,
            'total_count' => $formattedAgencies->count()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/vendors",
     *     summary="용역사 목록 조회",
     *     description="시스템에 등록된 용역사 목록을 조회합니다. (관리자만)",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="용역사 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="용역사 목록을 성공적으로 불러왔습니다."),
     *             @OA\Property(
     *                 property="vendors",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01234567-89ab-cdef-0123-456789abcdef"),
     *                     @OA\Property(property="name", type="string", example="XYZ 무대"),
     *                     @OA\Property(property="business_registration_number", type="string", example="987-65-43210"),
     *                     @OA\Property(property="account_status", type="string", example="active"),
     *                     @OA\Property(
     *                         property="master_user",
     *                         @OA\Property(property="name", type="string", example="박대표"),
     *                         @OA\Property(property="email", type="string", example="master@vendor.com")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="권한 없음")
     * )
     */
    public function getVendors()
    {
        $user = Auth::user();

        // 관리자만 접근 가능
        if ($user->user_type !== 'admin') {
            return response()->json([
                'message' => '용역사 목록은 관리자만 조회할 수 있습니다.'
            ], 403);
        }

        $vendors = Vendor::with('masterUser:id,name,email')
            ->select('id', 'name', 'business_registration_number', 'account_status', 'master_user_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedVendors = $vendors->map(function ($vendor) {
            return [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'business_registration_number' => $vendor->business_registration_number,
                'account_status' => $vendor->account_status,
                'master_user' => $vendor->masterUser ? [
                    'name' => $vendor->masterUser->name,
                    'email' => $vendor->masterUser->email,
                ] : null,
                'created_at' => $vendor->created_at
            ];
        });

        return response()->json([
            'message' => '용역사 목록을 성공적으로 불러왔습니다.',
            'vendors' => $formattedVendors,
            'total_count' => $formattedVendors->count()
        ]);
    }

    /**
     * 전문성 레벨 결정
     */
    private function getExpertiseLevel(int $evaluationCount, float $avgScore): string
    {
        if ($evaluationCount >= 20 && $avgScore >= 85) {
            return '전문가';
        } elseif ($evaluationCount >= 10 && $avgScore >= 80) {
            return '숙련자';
        } elseif ($evaluationCount >= 5 && $avgScore >= 75) {
            return '경험자';
        } else {
            return '초보자';
        }
    }
} 