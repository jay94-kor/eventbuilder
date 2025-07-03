<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElementDefinition;
use App\Services\ElementTemplateService;
use App\Http\Requests\Admin\UpdateElementTemplateRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ElementTemplateController extends Controller
{
    protected $elementTemplateService;

    public function __construct(ElementTemplateService $elementTemplateService)
    {
        $this->elementTemplateService = $elementTemplateService;
    }

    /**
     * 모든 ElementDefinition과 동적 스펙 템플릿 조회
     */
    public function index(): JsonResponse
    {
        try {
            $elements = $this->elementTemplateService->getAllElementTemplates();

            return ApiResponse::success('요소 템플릿 목록을 성공적으로 조회했습니다.', [
                'elements' => $elements,
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('요소 템플릿 목록을 불러오는데 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 특정 ElementDefinition의 동적 스펙 템플릿 상세 조회
     */
    public function show(string $elementId): JsonResponse
    {
        try {
            $element = $this->elementTemplateService->getElementTemplate($elementId);

            return ApiResponse::success('요소 템플릿을 성공적으로 조회했습니다.', [
                'element' => $element,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('요소 템플릿을 찾을 수 없습니다.');
        } catch (\Exception $e) {
            return ApiResponse::serverError('요소 템플릿 조회에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * ElementDefinition의 동적 스펙 템플릿 업데이트
     */
    public function update(UpdateElementTemplateRequest $request, string $elementId): JsonResponse
    {
        try {
            $element = ElementDefinition::findOrFail($elementId);
            
            $updatedElement = $this->elementTemplateService->updateElementTemplate(
                $element, 
                $request->validated()
            );

            return ApiResponse::success('동적 스펙 템플릿이 성공적으로 업데이트되었습니다.', [
                'element' => $updatedElement,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('요소를 찾을 수 없습니다.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError('입력 데이터가 올바르지 않습니다.', $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::serverError('템플릿 업데이트에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 동적 스펙 템플릿 초기화 (기본 템플릿 생성)
     */
    public function reset(string $elementId): JsonResponse
    {
        try {
            $element = ElementDefinition::findOrFail($elementId);
            
            $resetElement = $this->elementTemplateService->resetElementTemplate($element);

            return ApiResponse::success('기본 템플릿으로 초기화되었습니다.', [
                'element' => $resetElement,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('요소를 찾을 수 없습니다.');
        } catch (\Exception $e) {
            return ApiResponse::serverError('템플릿 초기화에 실패했습니다.', $e->getMessage());
        }
    }

    /**
     * 템플릿 통계 조회
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->elementTemplateService->getTemplateStats();

            return ApiResponse::success('템플릿 통계를 조회했습니다.', $stats);

        } catch (\Exception $e) {
            return ApiResponse::serverError('템플릿 통계 조회에 실패했습니다.', $e->getMessage());
        }
    }
}