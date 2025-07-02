# Step3 개선 계획서 - 동적 스펙 및 수량 관리

## 📋 개요

**목표**: Step3에서 각 요소별로 **동적 스펙 관리**와 **수량 관리**를 효율적으로 처리할 수 있도록 개선

**핵심 아이디어**: **{스펙명 | 스펙 단위 | 스펙 입력}** 구조로 모든 요소에 적용 가능한 범용적 시스템

---

## 🎯 최종 UX 목표

### 다양한 요소 예시:

#### LED 스크린 설치:
```
┌─ LED 스크린 설치 ─────────────────────────┐
│ 📊 기본 설정 (총 5대)                      │
│ ├ 가로        │ m    │ [3.0]             │
│ ├ 세로        │ m    │ [2.0]             │
│ ├ 픽셀피치    │      │ [P3.9]            │
│ ├ 해상도      │      │ [Full HD]         │
│ └ 기본 스펙 적용: 3대                     │
│                                           │
│ 🔧 스펙 변형 (2개)                        │
│ ├ ① 소형 스크린 (1대)                     │
│ │   ├ 가로: 2.0m, 세로: 1.5m             │
│ └ ② 고해상도 스크린 (1대)                 │
│     └ 해상도: 4K                         │
└───────────────────────────────────────────┘
```

#### 카메라 촬영:
```
┌─ 카메라 촬영 ─────────────────────────────┐
│ 📊 기본 설정 (총 8대)                      │
│ ├ 화질        │      │ [4K]              │
│ ├ 렌즈타입    │      │ [표준]            │
│ ├ 삼각대      │      │ [포함]            │
│ └ 기본 스펙 적용: 6대                     │
│                                           │
│ 🔧 스펙 변형 (2개)                        │
│ ├ ① 광각 카메라 (1대)                     │
│ │   └ 렌즈타입: 광각                     │
│ └ ② 고정 카메라 (1대)                     │
│     └ 삼각대: 고정식                     │
└───────────────────────────────────────────┘
```

#### 음향 시스템:
```
┌─ 음향 시스템 ─────────────────────────────┐
│ 📊 기본 설정 (총 4대)                      │
│ ├ 출력        │ W    │ [1000]            │
│ ├ 타입        │      │ [라인어레이]       │
│ ├ 설치방식    │      │ [트러스행잉]       │
│ └ 기본 스펙 적용: 2대                     │
│                                           │
│ 🔧 스펙 변형 (2개)                        │
│ ├ ① 고출력 스피커 (1대)                   │
│ │   └ 출력: 2000W                        │
│ └ ② 서브우퍼 (1대)                        │
│     └ 타입: 서브우퍼                     │
└───────────────────────────────────────────┘
```

---

## 🏗️ 개발 단계별 계획

### **Phase 1: 동적 스펙 구조 설계**

#### 1.1 새로운 데이터 구조 정의

**프론트엔드 타입 수정** (`frontend/lib/types.ts`):

```typescript
// 🆕 동적 스펙 필드 정의
export interface SpecField {
  id: string;                           // 필드 고유 ID
  name: string;                         // 스펙명 (예: "가로", "화질", "개수")
  unit?: string;                        // 스펙 단위 (예: "m", "대", "W", null)
  value: string | number | boolean;     // 스펙 입력값
  type: 'number' | 'text' | 'select' | 'boolean';
  options?: string[];                   // select 타입일 때 선택 옵션들
  required?: boolean;                   // 필수 입력 여부
  validation?: {                        // 검증 규칙
    min?: number;
    max?: number;
    pattern?: string;
  };
}

// 🆕 스펙 변형 타입 (범용적)
export interface SpecVariant {
  id: string;                           // UUID
  name: string;                         // "소형 버전", "고출력 버전" 등
  quantity: number;                     // 이 변형의 수량
  modified_fields: string[];            // 변경된 스펙 필드 ID들
  spec_values: Record<string, any>;     // 변경된 스펙 값들
  notes?: string;                       // 변형별 특별 요구사항
}

// 🔄 기존 RfpElementFormData 완전 재설계
export interface RfpElementFormData {
  element_id: string;
  element_type: string;
  
  // 🆕 수량 관리
  total_quantity: number;               // 총 수량
  base_quantity: number;                // 기본 스펙 적용 수량
  use_variants: boolean;                // 스펙 변형 사용 여부
  
  // 🆕 동적 스펙 시스템
  spec_fields: SpecField[];             // 기본 스펙 필드들
  spec_variants: SpecVariant[];         // 스펙 변형들
  
  // 기존 필드들 (하위 호환성)
  details: Record<string, unknown>;     // 기존 details → spec_fields로 변환
  special_requirements: string;
  allocated_budget: number | null;
  // ... 기타 기존 필드들
}
```

#### 1.2 ElementDefinition 확장 (동적 스펙 템플릿)

```typescript
export interface ElementDefinition {
  id: string;
  // ... 기존 필드들
  
  // 🆕 동적 스펙 템플릿 정의
  default_spec_template?: SpecField[];   // 기본 스펙 필드 템플릿
  quantity_config?: {                    // 수량 설정
    unit: string;                        // "대", "개", "세트", "명" 등
    min: number;
    max: number;
    typical: number;
    allow_variants: boolean;             // 변형 허용 여부
  };
  
  // 🆕 변형 가능 필드 설정
  variant_rules?: {
    allowed_fields: string[];            // 변형 가능한 필드 ID들
    max_variants: number;                // 최대 변형 개수
    require_name: boolean;               // 변형명 필수 여부
  };
}
```

#### 1.3 백엔드 데이터베이스 마이그레이션

**새 마이그레이션 파일**:

```sql
-- 2025_07_01_120000_add_dynamic_specs_to_rfp_elements.php

-- RFP Elements 확장
ALTER TABLE rfp_elements ADD COLUMN total_quantity INTEGER DEFAULT 1;
ALTER TABLE rfp_elements ADD COLUMN base_quantity INTEGER DEFAULT 1;
ALTER TABLE rfp_elements ADD COLUMN use_variants BOOLEAN DEFAULT FALSE;
ALTER TABLE rfp_elements ADD COLUMN spec_fields JSONB DEFAULT '[]';
ALTER TABLE rfp_elements ADD COLUMN spec_variants JSONB DEFAULT '[]';

-- ElementDefinition 확장 (동적 템플릿)
ALTER TABLE element_definitions ADD COLUMN default_spec_template JSONB DEFAULT '[]';
ALTER TABLE element_definitions ADD COLUMN quantity_config JSONB DEFAULT NULL;
ALTER TABLE element_definitions ADD COLUMN variant_rules JSONB DEFAULT NULL;

-- 인덱스 추가 (성능 최적화)
CREATE INDEX idx_rfp_elements_spec_fields ON rfp_elements USING GIN (spec_fields);
CREATE INDEX idx_element_definitions_spec_template ON element_definitions USING GIN (default_spec_template);
```

#### 1.4 백엔드 모델 수정

**RfpElement 모델**:

```php
// backend/app/Models/RfpElement.php
protected $fillable = [
    // ... 기존 필드들
    'total_quantity',
    'base_quantity',
    'use_variants', 
    'spec_fields',
    'spec_variants',
];

protected $casts = [
    // ... 기존 캐스트들
    'spec_fields' => 'array',
    'spec_variants' => 'array',
    'use_variants' => 'boolean',
];

// 🆕 스펙 값 접근자
public function getSpecValue(string $fieldName): mixed {
    $field = collect($this->spec_fields)->firstWhere('name', $fieldName);
    return $field['value'] ?? null;
}

// 🆕 변형별 스펙 값 접근자  
public function getVariantSpecValue(string $variantId, string $fieldName): mixed {
    $variant = collect($this->spec_variants)->firstWhere('id', $variantId);
    return $variant['spec_values'][$fieldName] ?? $this->getSpecValue($fieldName);
}
```

**ElementDefinition 모델**:

```php
// backend/app/Models/ElementDefinition.php
protected $fillable = [
    // ... 기존 필드들
    'default_spec_template',
    'quantity_config',
    'variant_rules',
];

protected $casts = [
    // ... 기존 캐스트들
    'default_spec_template' => 'array',
    'quantity_config' => 'array',
    'variant_rules' => 'array',
];

// 🆕 스펙 템플릿 생성 헬퍼
public function createSpecFields(): array {
    return collect($this->default_spec_template)->map(function ($template) {
        return [
            'id' => \Str::uuid(),
            'name' => $template['name'],
            'unit' => $template['unit'] ?? null,
            'value' => $template['default_value'] ?? '',
            'type' => $template['type'],
            'options' => $template['options'] ?? null,
            'required' => $template['required'] ?? false,
            'validation' => $template['validation'] ?? null,
        ];
    })->toArray();
}
```

---

### **Phase 2: 어드민 동적 스펙 템플릿 관리**

#### 2.1 어드민 스펙 템플릿 편집기

**UI 구성**:
```
┌─ LED 스크린 설치 - 스펙 템플릿 편집 ─────┐
│ 기본 정보                                  │
│ ├ 이름: LED 스크린 설치                   │
│ ├ 설명: ...                              │
│ └ 복잡도: 중급                           │
│                                           │
│ 🆕 수량 설정                               │
│ ├ 단위: [대]                             │
│ ├ 최소: [1] 권장: [3] 최대: [20]         │
│ ├ ☑ 변형 허용  최대 변형: [5]개          │
│ └ ☑ 변형명 필수                          │
│                                           │
│ 🆕 스펙 필드 템플릿                        │
│ ┌─────────────────────────────────────────┐│
│ │ ① 가로                                  ││
│ │ ├ 타입: [숫자] 단위: [m]                ││
│ │ ├ 기본값: [3.0] ☑ 필수                 ││
│ │ ├ 최소: [0.5] 최대: [10.0]             ││
│ │ └ ☑ 변형 가능                          ││
│ │ [🗑️ 삭제]                              ││
│ └─────────────────────────────────────────┘│
│ ┌─────────────────────────────────────────┐│
│ │ ② 세로                                  ││
│ │ ├ 타입: [숫자] 단위: [m]                ││
│ │ └ ... (동일한 설정)                     ││
│ └─────────────────────────────────────────┘│
│ ┌─────────────────────────────────────────┐│
│ │ ③ 해상도                                ││
│ │ ├ 타입: [선택] 단위: []                 ││
│ │ ├ 옵션: Full HD, 4K, 8K                ││
│ │ └ ☑ 변형 가능                          ││
│ └─────────────────────────────────────────┘│
│                                           │
│ [➕ 스펙 필드 추가]                        │
└───────────────────────────────────────────┘
```

#### 2.2 다양한 요소별 템플릿 예시

**카메라 촬영 템플릿**:
```json
{
  "default_spec_template": [
    {
      "name": "화질",
      "type": "select",
      "options": ["HD", "Full HD", "4K", "8K"],
      "default_value": "4K",
      "required": true
    },
    {
      "name": "렌즈타입", 
      "type": "select",
      "options": ["표준", "광각", "망원", "줌"],
      "default_value": "표준"
    },
    {
      "name": "삼각대",
      "type": "select", 
      "options": ["기본", "고정식", "불포함"],
      "default_value": "기본"
    }
  ],
  "quantity_config": {
    "unit": "대",
    "min": 1,
    "max": 20, 
    "typical": 3,
    "allow_variants": true
  }
}
```

**음향 시스템 템플릿**:
```json
{
  "default_spec_template": [
    {
      "name": "출력",
      "type": "number",
      "unit": "W",
      "default_value": 1000,
      "validation": {"min": 100, "max": 5000},
      "required": true
    },
    {
      "name": "타입",
      "type": "select",
      "options": ["라인어레이", "포인트소스", "서브우퍼", "모니터"],
      "default_value": "라인어레이"
    },
    {
      "name": "설치방식",
      "type": "select",
      "options": ["트러스행잉", "스탠드", "바닥설치", "천장매립"],
      "default_value": "트러스행잉"
    }
  ]
}
```

---

### **Phase 3: Step3 동적 UI 시스템**

#### 3.1 새로운 컴포넌트 구조

```
Step3Form.tsx
├── DynamicSpecManager.tsx         // 동적 스펙 관리 메인
├── SpecFieldEditor.tsx           // 개별 스펙 필드 편집
├── QuantityController.tsx        // 수량 관리
├── VariantManager.tsx            // 변형 관리
├── VariantEditor.tsx             // 개별 변형 편집
└── SpecFieldTypes/               // 스펙 타입별 컴포넌트
    ├── NumberField.tsx
    ├── TextField.tsx  
    ├── SelectField.tsx
    └── BooleanField.tsx
```

#### 3.2 동적 스펙 필드 렌더링

```typescript
// SpecFieldEditor.tsx
const SpecFieldEditor: React.FC<{
  field: SpecField;
  onChange: (field: SpecField) => void;
}> = ({ field, onChange }) => {
  
  const renderFieldInput = () => {
    switch (field.type) {
      case 'number':
        return (
          <div className="flex items-center gap-2">
            <Input
              type="number"
              value={field.value as number}
              onChange={(e) => onChange({
                ...field,
                value: parseFloat(e.target.value) || 0
              })}
              min={field.validation?.min}
              max={field.validation?.max}
            />
            {field.unit && (
              <span className="text-sm text-gray-500">{field.unit}</span>
            )}
          </div>
        );
      
      case 'select':
        return (
          <Select
            value={field.value as string}
            onValueChange={(value) => onChange({...field, value})}
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {field.options?.map(option => (
                <SelectItem key={option} value={option}>
                  {option}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        );
      
      case 'boolean':
        return (
          <Checkbox
            checked={field.value as boolean}
            onCheckedChange={(checked) => onChange({...field, value: checked})}
          />
        );
      
      default:
        return (
          <Input
            type="text"
            value={field.value as string}
            onChange={(e) => onChange({...field, value: e.target.value})}
          />
        );
    }
  };

  return (
    <div className="grid grid-cols-12 gap-3 items-center p-3 border rounded">
      <div className="col-span-3">
        <Label className="font-medium">{field.name}</Label>
        {field.required && <span className="text-red-500 ml-1">*</span>}
      </div>
      <div className="col-span-6">
        {renderFieldInput()}
      </div>
      <div className="col-span-2 text-sm text-gray-500">
        {field.unit || '-'}
      </div>
      <div className="col-span-1">
        {/* 추가 액션 버튼들 */}
      </div>
    </div>
  );
};
```

#### 3.3 수량 검증 및 변형 관리

```typescript
const validateElement = (element: RfpElementFormData): string[] => {
  const errors: string[] = [];
  
  // 수량 검증
  const variantTotal = element.spec_variants.reduce((sum, variant) => sum + variant.quantity, 0);
  const calculatedTotal = element.base_quantity + variantTotal;
  
  if (calculatedTotal !== element.total_quantity) {
    errors.push(`총 수량(${element.total_quantity})과 실제 수량(${calculatedTotal})이 일치하지 않습니다.`);
  }
  
  // 스펙 필드 검증
  element.spec_fields.forEach(field => {
    if (field.required && (!field.value || field.value === '')) {
      errors.push(`${field.name}은(는) 필수 입력 항목입니다.`);
    }
    
    if (field.type === 'number' && field.validation) {
      const numValue = Number(field.value);
      if (field.validation.min && numValue < field.validation.min) {
        errors.push(`${field.name}은(는) ${field.validation.min} 이상이어야 합니다.`);
      }
      if (field.validation.max && numValue > field.validation.max) {
        errors.push(`${field.name}은(는) ${field.validation.max} 이하여야 합니다.`);
      }
    }
  });
  
  return errors;
};
```

---

### **Phase 4: API 및 데이터 변환**

#### 4.1 기존 데이터 마이그레이션

```php
// RfpController.php - 기존 details를 spec_fields로 변환
private function migrateDetailsToSpecFields($elementData, $elementDefinition) {
    // 기존 details가 있는 경우
    if (isset($elementData['details']) && !isset($elementData['spec_fields'])) {
        $specFields = [];
        $template = $elementDefinition->default_spec_template ?? [];
        
        foreach ($template as $fieldTemplate) {
            $fieldName = $fieldTemplate['name'];
            $specFields[] = [
                'id' => \Str::uuid(),
                'name' => $fieldName,
                'unit' => $fieldTemplate['unit'] ?? null,
                'value' => $elementData['details'][$fieldName] ?? $fieldTemplate['default_value'] ?? '',
                'type' => $fieldTemplate['type'],
                'options' => $fieldTemplate['options'] ?? null,
                'required' => $fieldTemplate['required'] ?? false,
            ];
        }
        
        $elementData['spec_fields'] = $specFields;
        $elementData['total_quantity'] = $elementData['total_quantity'] ?? 1;
        $elementData['base_quantity'] = $elementData['base_quantity'] ?? 1;
        $elementData['use_variants'] = false;
        $elementData['spec_variants'] = [];
    }
    
    return $elementData;
}
```

#### 4.2 다양한 요소 타입별 기본 템플릿

```php
// 시더 파일에서 기본 템플릿 생성
$elementTemplates = [
    'LED 스크린 설치' => [
        'default_spec_template' => [
            ['name' => '가로', 'type' => 'number', 'unit' => 'm', 'default_value' => 3.0, 'required' => true],
            ['name' => '세로', 'type' => 'number', 'unit' => 'm', 'default_value' => 2.0, 'required' => true],
            ['name' => '픽셀피치', 'type' => 'text', 'default_value' => 'P3.9'],
            ['name' => '해상도', 'type' => 'select', 'options' => ['HD', 'Full HD', '4K', '8K'], 'default_value' => 'Full HD'],
        ],
        'quantity_config' => ['unit' => '대', 'min' => 1, 'max' => 20, 'typical' => 3, 'allow_variants' => true],
    ],
    
    '카메라 촬영' => [
        'default_spec_template' => [
            ['name' => '화질', 'type' => 'select', 'options' => ['HD', 'Full HD', '4K', '8K'], 'default_value' => '4K', 'required' => true],
            ['name' => '렌즈타입', 'type' => 'select', 'options' => ['표준', '광각', '망원', '줌'], 'default_value' => '표준'],
            ['name' => '삼각대', 'type' => 'select', 'options' => ['기본', '고정식', '불포함'], 'default_value' => '기본'],
        ],
        'quantity_config' => ['unit' => '대', 'min' => 1, 'max' => 15, 'typical' => 3, 'allow_variants' => true],
    ],
    
    '음향 시스템' => [
        'default_spec_template' => [
            ['name' => '출력', 'type' => 'number', 'unit' => 'W', 'default_value' => 1000, 'required' => true],
            ['name' => '타입', 'type' => 'select', 'options' => ['라인어레이', '포인트소스', '서브우퍼', '모니터'], 'default_value' => '라인어레이'],
            ['name' => '설치방식', 'type' => 'select', 'options' => ['트러스행잉', '스탠드', '바닥설치', '천장매립'], 'default_value' => '트러스행잉'],
        ],
        'quantity_config' => ['unit' => '대', 'min' => 1, 'max' => 10, 'typical' => 2, 'allow_variants' => true],
    ],
];
```

---

## 🔄 마이그레이션 전략

### 기존 데이터 처리

1. **자동 변환**: 기존 `details` → 동적 `spec_fields`
2. **템플릿 매칭**: ElementDefinition의 템플릿과 기존 데이터 매칭
3. **점진적 전환**: 기존 API 응답도 병행 지원
4. **데이터 검증**: 변환된 데이터 무결성 검사

### 호환성 유지

1. **API 버전**: 기존 `details` 기반 API와 새 `spec_fields` API 병행
2. **프론트엔드**: 구버전 데이터 자동 마이그레이션
3. **백엔드**: 양방향 데이터 변환 지원

---

## 📅 개발 일정 (수정)

| Phase | 작업 내용 | 소요 시간 |
|-------|-----------|-----------|
| Phase 1 | 동적 스펙 구조 설계 및 백엔드 | 3-4일 |
| Phase 2 | 어드민 동적 템플릿 관리 | 2-3일 |
| Phase 3 | Step3 동적 UI 시스템 | 4-5일 |
| Phase 4 | API 및 데이터 변환 | 2-3일 |
| Phase 5 | Step4 연동 및 테스트 | 2-3일 |
| **총합** | **전체 개발 및 테스트** | **13-18일** |

---

## 🎯 성공 지표

1. **범용성**: LED, 카메라, 음향 등 모든 요소 타입 지원
2. **사용성**: 5대 LED(3대 동일, 2대 변형) 설정이 2분 이내 완료
3. **확장성**: 새로운 요소 타입 추가 시 30분 이내 템플릿 생성 가능
4. **정확성**: 동적 스펙 검증 오류 100% 방지

---

이 **동적 스펙 시스템**으로 LED뿐만 아니라 카메라, 음향, 조명, 무대 등 모든 요소를 효율적으로 관리할 수 있습니다! 어떤 Phase부터 시작하시겠습니까? 🚀 