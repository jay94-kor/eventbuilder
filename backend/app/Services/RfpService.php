<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Rfp;
use App\Models\RfpElement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RfpService
{
    /**
     * RFP 생성
     */
    public function createRfp(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Project 생성
            $project = Project::create([
                'name' => $data['project_name'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'],
                'preparation_start_datetime' => $data['preparation_start_datetime'] ?? null,
                '철수_end_datetime' => $data['철수_end_datetime'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'client_contact_person' => $data['client_contact_person'] ?? null,
                'client_contact_number' => $data['client_contact_number'] ?? null,
                'is_indoor' => $data['is_indoor'],
                'location' => $data['location'],
                'budget_including_vat' => $data['budget_including_vat'] ?? null,
                'agency_id' => Auth::user()->agency_id,
            ]);

            // 2. RFP 생성
            $rfp = Rfp::create([
                'project_id' => $project->id,
                'issue_type' => $data['issue_type'],
                'rfp_description' => $data['rfp_description'] ?? null,
                'closing_at' => $data['closing_at'],
                'status' => config('rfp.status.published'),
            ]);

            // 3. RFP Elements 생성
            if (isset($data['elements']) && is_array($data['elements'])) {
                foreach ($data['elements'] as $elementData) {
                    RfpElement::create([
                        'rfp_id' => $rfp->id,
                        'element_type' => $elementData['element_type'],
                        'details' => $elementData['details'] ?? [],
                        'allocated_budget' => $elementData['allocated_budget'] ?? null,
                        'prepayment_ratio' => $elementData['prepayment_ratio'] ?? null,
                        'prepayment_due_date' => $elementData['prepayment_due_date'] ?? null,
                        'balance_ratio' => $elementData['balance_ratio'] ?? null,
                        'balance_due_date' => $elementData['balance_due_date'] ?? null,
                        'quantity' => $elementData['quantity'] ?? 1,
                        'dynamic_specs' => $elementData['dynamic_specs'] ?? [],
                    ]);
                }
            }

            return [
                'project' => $project,
                'rfp' => $rfp->load('project', 'elements'),
            ];
        });
    }

    /**
     * RFP 임시저장
     */
    public function saveDraft(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Project 생성
            $project = Project::create([
                'name' => $data['project_name'] ?? config('rfp.defaults.project_name'),
                'start_datetime' => $data['start_datetime'] ?? null,
                'end_datetime' => $data['end_datetime'] ?? null,
                'preparation_start_datetime' => $data['preparation_start_datetime'] ?? null,
                '철수_end_datetime' => $data['철수_end_datetime'] ?? null,
                'client_name' => $data['client_name'] ?? null,
                'client_contact_person' => $data['client_contact_person'] ?? null,
                'client_contact_number' => $data['client_contact_number'] ?? null,
                'is_indoor' => $data['is_indoor'] ?? true,
                'location' => $data['location'] ?? null,
                'budget_including_vat' => $data['budget_including_vat'] ?? null,
                'agency_id' => Auth::user()->agency_id,
            ]);

            // 2. RFP 생성 (임시저장)
            $rfp = Rfp::create([
                'project_id' => $project->id,
                'issue_type' => $data['issue_type'] ?? config('rfp.defaults.issue_type'),
                'rfp_description' => $data['rfp_description'] ?? null,
                'closing_at' => $data['closing_at'] ?? null,
                'status' => config('rfp.status.draft'),
            ]);

            // 3. RFP Elements 생성
            if (isset($data['elements']) && is_array($data['elements'])) {
                foreach ($data['elements'] as $elementData) {
                    RfpElement::create([
                        'rfp_id' => $rfp->id,
                        'element_type' => $elementData['element_type'],
                        'details' => $elementData['details'] ?? [],
                        'allocated_budget' => $elementData['allocated_budget'] ?? null,
                        'prepayment_ratio' => $elementData['prepayment_ratio'] ?? null,
                        'prepayment_due_date' => $elementData['prepayment_due_date'] ?? null,
                        'balance_ratio' => $elementData['balance_ratio'] ?? null,
                        'balance_due_date' => $elementData['balance_due_date'] ?? null,
                        'quantity' => $elementData['quantity'] ?? 1,
                        'dynamic_specs' => $elementData['dynamic_specs'] ?? [],
                    ]);
                }
            }

            return [
                'project' => $project,
                'rfp' => $rfp->load('project', 'elements'),
            ];
        });
    }

    /**
     * RFP 임시저장 수정
     */
    public function updateDraft(Rfp $rfp, array $data): array
    {
        return DB::transaction(function () use ($rfp, $data) {
            $project = $rfp->project;

            // 1. Project 업데이트
            $project->update([
                'name' => $data['project_name'] ?? $project->name,
                'start_datetime' => $data['start_datetime'] ?? $project->start_datetime,
                'end_datetime' => $data['end_datetime'] ?? $project->end_datetime,
                'preparation_start_datetime' => $data['preparation_start_datetime'] ?? $project->preparation_start_datetime,
                '철수_end_datetime' => $data['철수_end_datetime'] ?? $project->철수_end_datetime,
                'client_name' => $data['client_name'] ?? $project->client_name,
                'client_contact_person' => $data['client_contact_person'] ?? $project->client_contact_person,
                'client_contact_number' => $data['client_contact_number'] ?? $project->client_contact_number,
                'is_indoor' => $data['is_indoor'] ?? $project->is_indoor,
                'location' => $data['location'] ?? $project->location,
                'budget_including_vat' => $data['budget_including_vat'] ?? $project->budget_including_vat,
            ]);

            // 2. RFP 업데이트
            $rfp->update([
                'issue_type' => $data['issue_type'] ?? $rfp->issue_type,
                'rfp_description' => $data['rfp_description'] ?? $rfp->rfp_description,
                'closing_at' => $data['closing_at'] ?? $rfp->closing_at,
            ]);

            // 3. 기존 RFP Elements 삭제 후 재생성
            if (isset($data['elements']) && is_array($data['elements'])) {
                $rfp->elements()->delete();

                foreach ($data['elements'] as $elementData) {
                    RfpElement::create([
                        'rfp_id' => $rfp->id,
                        'element_type' => $elementData['element_type'],
                        'details' => $elementData['details'] ?? [],
                        'allocated_budget' => $elementData['allocated_budget'] ?? null,
                        'prepayment_ratio' => $elementData['prepayment_ratio'] ?? null,
                        'prepayment_due_date' => $elementData['prepayment_due_date'] ?? null,
                        'balance_ratio' => $elementData['balance_ratio'] ?? null,
                        'balance_due_date' => $elementData['balance_due_date'] ?? null,
                        'quantity' => $elementData['quantity'] ?? 1,
                        'dynamic_specs' => $elementData['dynamic_specs'] ?? [],
                    ]);
                }
            }

            return [
                'project' => $project,
                'rfp' => $rfp->load('project', 'elements'),
            ];
        });
    }

    /**
     * RFP 발행 (임시저장에서 발행으로 상태 변경)
     */
    public function publishDraft(Rfp $rfp, array $data): array
    {
        // 발행 전 필수 데이터 검증
        $project = $rfp->project;

        $requiredFields = [
            'project_name' => $data['project_name'] ?? $project->name,
            'start_datetime' => $data['start_datetime'] ?? $project->start_datetime,
            'end_datetime' => $data['end_datetime'] ?? $project->end_datetime,
            'is_indoor' => $data['is_indoor'] ?? $project->is_indoor,
            'location' => $data['location'] ?? $project->location,
            'issue_type' => $data['issue_type'] ?? $rfp->issue_type,
            'closing_at' => $data['closing_at'] ?? $rfp->closing_at,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new \InvalidArgumentException("필수 필드가 누락되었습니다: {$field}");
            }
        }

        return DB::transaction(function () use ($rfp, $data) {
            // 먼저 임시저장 데이터 업데이트
            $result = $this->updateDraft($rfp, $data);

            // 상태를 발행으로 변경
            $rfp->update(['status' => config('rfp.status.published')]);

            return $result;
        });
    }
}