<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Vendor;
use App\Models\User;
use App\Models\ElementDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * 모든 대행사와 멤버 정보를 조회
     */
    public function getAgencies(): JsonResponse
    {
        try {
            $agencies = Agency::with([
                'masterUser:id,name,email,position,job_title',
                'members.user:id,name,email,position,job_title,created_at'
            ])
            ->select([
                'id', 'name', 'business_registration_number', 'address', 
                'master_user_id', 'subscription_status', 'subscription_end_date',
                'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

            // 각 대행사별 멤버 수 추가
            $agencies->each(function ($agency) {
                $agency->member_count = $agency->members->count();
                
                // 멤버 정보에 가입일 추가
                $agency->members->each(function ($member) {
                    $member->joined_at = $member->created_at;
                });
            });

            return response()->json([
                'success' => true,
                'data' => $agencies
            ]);

        } catch (\Exception $e) {
            \Log::error('대행사 목록 조회 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '대행사 목록을 불러오는데 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 모든 용역사와 멤버 정보를 조회
     */
    public function getVendors(): JsonResponse
    {
        try {
            $vendors = Vendor::with([
                'masterUser:id,name,email,position,job_title',
                'members.user:id,name,email,position,job_title,created_at'
            ])
            ->select([
                'id', 'name', 'business_registration_number', 'address', 
                'description', 'specialties', 'master_user_id', 'status',
                'ban_reason', 'banned_at', 'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

            // 각 용역사별 멤버 수 추가
            $vendors->each(function ($vendor) {
                $vendor->member_count = $vendor->members->count();
                
                // 멤버 정보에 가입일 추가
                $vendor->members->each(function ($member) {
                    $member->joined_at = $member->created_at;
                });
            });

            return response()->json([
                'success' => true,
                'data' => $vendors
            ]);

        } catch (\Exception $e) {
            \Log::error('용역사 목록 조회 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '용역사 목록을 불러오는데 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 대행사 정보 업데이트 (구독 만료일 포함)
     */
    public function updateAgency(Request $request, string $agencyId): JsonResponse
    {
        try {
            $request->validate([
                'subscription_end_date' => 'nullable|date',
                'subscription_status' => 'nullable|in:active,inactive,suspended',
                'name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500'
            ]);

            $agency = Agency::findOrFail($agencyId);

            $updateData = [];
            
            if ($request->has('subscription_end_date')) {
                $updateData['subscription_end_date'] = $request->subscription_end_date 
                    ? Carbon::parse($request->subscription_end_date) 
                    : null;
            }
            
            if ($request->has('subscription_status')) {
                $updateData['subscription_status'] = $request->subscription_status;
            }
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('address')) {
                $updateData['address'] = $request->address;
            }

            $agency->update($updateData);

            // 업데이트된 정보와 함께 다시 로드
            $agency->load([
                'masterUser:id,name,email,position,job_title',
                'members.user:id,name,email,position,job_title,created_at'
            ]);

            return response()->json([
                'success' => true,
                'message' => '대행사 정보가 성공적으로 업데이트되었습니다.',
                'data' => $agency
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('대행사 업데이트 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '대행사 정보 업데이트에 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 용역사 정보 업데이트
     */
    public function updateVendor(Request $request, string $vendorId): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'nullable|in:active,inactive,banned',
                'ban_reason' => 'nullable|string|max:500',
                'name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'description' => 'nullable|string|max:1000'
            ]);

            $vendor = Vendor::findOrFail($vendorId);

            $updateData = [];
            
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
                
                // 상태가 banned로 변경되면 banned_at 설정
                if ($request->status === 'banned') {
                    $updateData['banned_at'] = now();
                } else {
                    $updateData['banned_at'] = null;
                    $updateData['ban_reason'] = null; // 밴 해제시 사유도 초기화
                }
            }
            
            if ($request->has('ban_reason')) {
                $updateData['ban_reason'] = $request->ban_reason;
            }
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('address')) {
                $updateData['address'] = $request->address;
            }
            
            if ($request->has('description')) {
                $updateData['description'] = $request->description;
            }

            $vendor->update($updateData);

            // 업데이트된 정보와 함께 다시 로드
            $vendor->load([
                'masterUser:id,name,email,position,job_title',
                'members.user:id,name,email,position,job_title,created_at'
            ]);

            return response()->json([
                'success' => true,
                'message' => '용역사 정보가 성공적으로 업데이트되었습니다.',
                'data' => $vendor
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('용역사 업데이트 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '용역사 정보 업데이트에 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 사용자 계정 상태 업데이트 (승인/거절/정지)
     */
    public function updateUserStatus(Request $request, string $userId): JsonResponse
    {
        try {
            $request->validate([
                'account_status' => 'required|in:pending,approved,rejected,suspended',
                'admin_notes' => 'nullable|string|max:1000'
            ]);

            $user = User::findOrFail($userId);

            $updateData = [
                'account_status' => $request->account_status,
                'admin_notes' => $request->admin_notes
            ];

            // 승인된 경우 승인 정보 추가
            if ($request->account_status === 'approved') {
                $updateData['approved_by'] = auth()->id();
                $updateData['approved_at'] = now();
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => '사용자 상태가 성공적으로 업데이트되었습니다.',
                'data' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('사용자 상태 업데이트 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '사용자 상태 업데이트에 실패했습니다.'
            ], 500);
        }
    }

    // 🆕 ===== 동적 스펙 템플릿 관리 =====

    /**
     * 모든 ElementDefinition과 동적 스펙 템플릿 조회
     */
    public function getElementTemplates(): JsonResponse
    {
        try {
            $elements = ElementDefinition::with('category:id,name,color')
                ->select([
                    'id', 'display_name', 'element_type', 'description', 
                    'complexity', 'category_id', 'default_spec_template',
                    'quantity_config', 'variant_rules', 'created_at'
                ])
                ->orderBy('category_id')
                ->orderBy('display_name')
                ->get();

            // 템플릿 유무와 기본 정보 표시
            $elements->each(function ($element) {
                $element->has_template = !empty($element->default_spec_template);
                $element->spec_field_count = $element->has_template 
                    ? count($element->default_spec_template) 
                    : 0;
                
                // quantity_config 기본값 설정
                if (!$element->quantity_config) {
                    $element->quantity_config = [
                        'unit' => '개',
                        'min' => 1,
                        'max' => 10,
                        'typical' => 1,
                        'allow_variants' => false
                    ];
                }
            });

            return response()->json([
                'success' => true,
                'data' => $elements
            ]);

        } catch (\Exception $e) {
            \Log::error('ElementDefinition 템플릿 조회 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '요소 템플릿 목록을 불러오는데 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 특정 ElementDefinition의 동적 스펙 템플릿 상세 조회
     */
    public function getElementTemplate(string $elementId): JsonResponse
    {
        try {
            $element = ElementDefinition::with('category:id,name,color')
                ->findOrFail($elementId);

            // 기본값 설정
            if (!$element->default_spec_template) {
                $element->default_spec_template = [];
            }
            
            if (!$element->quantity_config) {
                $element->quantity_config = [
                    'unit' => '개',
                    'min' => 1,
                    'max' => 10,
                    'typical' => 1,
                    'allow_variants' => false
                ];
            }
            
            if (!$element->variant_rules) {
                $element->variant_rules = [
                    'allowed_fields' => [],
                    'max_variants' => 3,
                    'require_name' => true
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $element
            ]);

        } catch (\Exception $e) {
            \Log::error('ElementDefinition 템플릿 상세 조회 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '요소 템플릿을 찾을 수 없습니다.'
            ], 404);
        }
    }

    /**
     * ElementDefinition의 동적 스펙 템플릿 업데이트
     */
    public function updateElementTemplate(Request $request, string $elementId): JsonResponse
    {
        try {
            $request->validate([
                'default_spec_template' => 'required|array',
                'default_spec_template.*.name' => 'required|string|max:50',
                'default_spec_template.*.type' => 'required|in:number,text,select,boolean',
                'default_spec_template.*.unit' => 'nullable|string|max:10',
                'default_spec_template.*.default_value' => 'nullable',
                'default_spec_template.*.required' => 'nullable|boolean',
                'default_spec_template.*.options' => 'nullable|array',
                'default_spec_template.*.validation' => 'nullable|array',
                'default_spec_template.*.validation.min' => 'nullable|numeric',
                'default_spec_template.*.validation.max' => 'nullable|numeric',
                
                'quantity_config' => 'required|array',
                'quantity_config.unit' => 'required|string|max:10',
                'quantity_config.min' => 'required|integer|min:1',
                'quantity_config.max' => 'required|integer|min:1',
                'quantity_config.typical' => 'required|integer|min:1',
                'quantity_config.allow_variants' => 'required|boolean',
                
                'variant_rules' => 'nullable|array',
                'variant_rules.allowed_fields' => 'nullable|array',
                'variant_rules.max_variants' => 'nullable|integer|min:1|max:10',
                'variant_rules.require_name' => 'nullable|boolean'
            ]);

            $element = ElementDefinition::findOrFail($elementId);

            // 스펙 필드들 검증
            foreach ($request->default_spec_template as $index => $field) {
                // select 타입의 경우 options 필수
                if ($field['type'] === 'select' && empty($field['options'])) {
                    throw ValidationException::withMessages([
                        "default_spec_template.{$index}.options" => 'select 타입은 options가 필수입니다.'
                    ]);
                }
                
                // number 타입의 경우 validation 검증
                if ($field['type'] === 'number' && isset($field['validation'])) {
                    if (isset($field['validation']['min'], $field['validation']['max']) && 
                        $field['validation']['min'] > $field['validation']['max']) {
                        throw ValidationException::withMessages([
                            "default_spec_template.{$index}.validation" => '최소값이 최대값보다 클 수 없습니다.'
                        ]);
                    }
                }
            }

            // quantity_config 검증
            if ($request->quantity_config['min'] > $request->quantity_config['max']) {
                throw ValidationException::withMessages([
                    'quantity_config.min' => '최소 수량이 최대 수량보다 클 수 없습니다.'
                ]);
            }
            
            if ($request->quantity_config['typical'] < $request->quantity_config['min'] || 
                $request->quantity_config['typical'] > $request->quantity_config['max']) {
                throw ValidationException::withMessages([
                    'quantity_config.typical' => '권장 수량은 최소-최대 범위 내에 있어야 합니다.'
                ]);
            }

            $element->update([
                'default_spec_template' => $request->default_spec_template,
                'quantity_config' => $request->quantity_config,
                'variant_rules' => $request->variant_rules ?? [
                    'allowed_fields' => [],
                    'max_variants' => 3,
                    'require_name' => true
                ]
            ]);

            // 업데이트된 정보와 함께 다시 로드
            $element->load('category:id,name,color');

            return response()->json([
                'success' => true,
                'message' => '동적 스펙 템플릿이 성공적으로 업데이트되었습니다.',
                'data' => $element
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('ElementDefinition 템플릿 업데이트 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '템플릿 업데이트에 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 동적 스펙 템플릿 초기화 (기본 템플릿 생성)
     */
    public function resetElementTemplate(string $elementId): JsonResponse
    {
        try {
            $element = ElementDefinition::findOrFail($elementId);

            // 요소 타입별 기본 템플릿 정의
            $defaultTemplates = [
                'led_screen' => [
                    'spec_template' => [
                        ['name' => '가로', 'type' => 'number', 'unit' => 'm', 'default_value' => 3.0, 'required' => true],
                        ['name' => '세로', 'type' => 'number', 'unit' => 'm', 'default_value' => 2.0, 'required' => true],
                        ['name' => '해상도', 'type' => 'select', 'options' => ['HD', 'Full HD', '4K'], 'default_value' => 'Full HD']
                    ],
                    'quantity_config' => ['unit' => '대', 'min' => 1, 'max' => 20, 'typical' => 3, 'allow_variants' => true]
                ],
                'beam_projector' => [
                    'spec_template' => [
                        ['name' => '밝기', 'type' => 'number', 'unit' => '루멘', 'default_value' => 5000, 'required' => true],
                        ['name' => '해상도', 'type' => 'select', 'options' => ['XGA', 'Full HD', '4K'], 'default_value' => 'Full HD']
                    ],
                    'quantity_config' => ['unit' => '대', 'min' => 1, 'max' => 10, 'typical' => 2, 'allow_variants' => true]
                ]
            ];

            $template = $defaultTemplates[$element->element_type] ?? [
                'spec_template' => [
                    ['name' => '수량', 'type' => 'number', 'unit' => '개', 'default_value' => 1, 'required' => true]
                ],
                'quantity_config' => ['unit' => '개', 'min' => 1, 'max' => 10, 'typical' => 1, 'allow_variants' => false]
            ];

            $element->update([
                'default_spec_template' => $template['spec_template'],
                'quantity_config' => $template['quantity_config'],
                'variant_rules' => [
                    'allowed_fields' => [],
                    'max_variants' => 3,
                    'require_name' => true
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => '기본 템플릿으로 초기화되었습니다.',
                'data' => $element->fresh(['category'])
            ]);

        } catch (\Exception $e) {
            \Log::error('ElementDefinition 템플릿 초기화 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '템플릿 초기화에 실패했습니다.'
            ], 500);
        }
    }
}
