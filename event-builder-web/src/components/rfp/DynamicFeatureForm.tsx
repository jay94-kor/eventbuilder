'use client'

import React, { useState, useEffect, useMemo } from 'react'
import { Feature, FeatureField } from '@/types/rfp'
import { Input } from '@/components/ui/input'; // Shadcn UI Input 컴포넌트 임포트
import { cn } from '@/lib/design-system';
import { useTranslation } from '@/lib/i18n';

interface DynamicFeatureFormProps {
  feature: Feature
  formData: Record<string, any> // eslint-disable-line @typescript-eslint/no-explicit-any
  onChange: (data: Record<string, any>) => void // eslint-disable-line @typescript-eslint/no-explicit-any
  errors?: Record<string, string>
  eventZones?: { name: string; type: string; quantity: number }[] // EventBasic의 zones 데이터
}

export default function DynamicFeatureForm({ 
  feature,
  formData,
  onChange,
  errors = {},
  eventZones = []
}: DynamicFeatureFormProps) {
  const { t } = useTranslation();
  const [fieldVisibility, setFieldVisibility] = useState<Record<string, boolean>>({})

  // 필드 가시성 계산 - useMemo로 최적화
  const calculatedVisibility = useMemo(() => {
    if (!feature.config?.fields) return {}

    const visibility: Record<string, boolean> = {}
    
    feature.config.fields.forEach(field => {
      if (field.field_level === 'independent' || field.field_level === 'parent') {
        // 독립 필드와 상위 필드는 항상 표시
        visibility[field.key] = true
      } else if (field.field_level === 'child' && field.parent_field && field.show_when_value) {
        // 하위 필드는 상위 필드 값에 따라 조건부 표시
        const parentValue = formData[field.parent_field]
        visibility[field.key] = parentValue === field.show_when_value
      } else {
        visibility[field.key] = false
      }
    })

    return visibility
  }, [feature.config?.fields, formData])

  // 가시성이 변경될 때만 상태 업데이트
  useEffect(() => {
    setFieldVisibility(calculatedVisibility)
  }, [calculatedVisibility])

  const handleFieldChange = (fieldKey: string, value: any) => { // eslint-disable-line @typescript-eslint/no-explicit-any
    const newFormData = { ...formData, [fieldKey]: value }
    
    // 상위 필드가 변경된 경우, 관련된 모든 하위 필드 값들을 제거
    const changedField = feature.config?.fields.find(f => f.key === fieldKey)
    if (changedField?.field_level === 'parent') {
      // 이 상위 필드에 속한 모든 하위 필드들 찾기
      const childFields = feature.config?.fields.filter(f => 
        f.field_level === 'child' && f.parent_field === fieldKey
      ) || []
      
      // 새로운 값과 매치되지 않는 하위 필드들의 값 제거
      childFields.forEach(childField => {
        if (childField.show_when_value !== value && newFormData.hasOwnProperty(childField.key)) {
          delete newFormData[childField.key]
        }
      })
    }
    
    onChange(newFormData)
  }

  // 필드들을 논리적 그룹으로 분류
  const groupFields = (fields: FeatureField[]) => {
    const visibleFields = fields.filter(field => fieldVisibility[field.key])
    
    const groups = {
      basic: [] as FeatureField[],      // 기본 정보 (이름, 수용인원 등)
      schedule: [] as FeatureField[],   // 일정 관리 (날짜, 시간 관련)
      operation: [] as FeatureField[],  // 운영 설정 (리소스, 범위 등)
      others: [] as FeatureField[]      // 기타
    }
    
    visibleFields.forEach(field => {
      const fieldName = field.name.toLowerCase()
      const fieldKey = field.key.toLowerCase()
      
      if (fieldName.includes('이름') || fieldName.includes('수용') || fieldName.includes('인원') || 
          fieldKey.includes('name') || fieldKey.includes('capacity')) {
        groups.basic.push(field)
      } else if (fieldName.includes('일') || fieldName.includes('시점') || fieldName.includes('날짜') || 
                 fieldName.includes('시간') || fieldKey.includes('date') || fieldKey.includes('time') || 
                 fieldKey.includes('deadline')) {
        groups.schedule.push(field)
      } else if (fieldName.includes('리소스') || fieldName.includes('범위') || fieldName.includes('적용') ||
                 fieldKey.includes('resource') || fieldKey.includes('scope')) {
        groups.operation.push(field)
      } else {
        groups.others.push(field)
      }
    })
    
    return groups
  }

  // 필드 타입별 아이콘 반환
  const getFieldTypeIcon = (type: string) => {
    switch (type) {
      case 'text': return '📝'
      case 'number': return '🔢'
      case 'date': return '📅'
      case 'time': return '⏰'
      case 'datetime': return '📅'
      case 'textarea': return '📄'
      case 'select': return '📋'
      case 'radio': return '🔘'
      case 'checkbox': return '☑️'
      default: return '✏️'
    }
  }

  // 필드 타입별 색상 테마 반환
  const getFieldTypeTheme = (type: string) => {
    switch (type) {
      case 'text': 
        return {
          bg: 'from-blue-50 to-indigo-50',
          border: 'border-blue-200/60',
          focus: 'focus:border-blue-400 focus:ring-blue-400/20',
          label: 'text-blue-700',
          icon: 'from-blue-500 to-indigo-500'
        }
      case 'number':
        return {
          bg: 'from-emerald-50 to-green-50',
          border: 'border-emerald-200/60',
          focus: 'focus:border-emerald-400 focus:ring-emerald-400/20',
          label: 'text-emerald-700',
          icon: 'from-emerald-500 to-green-500'
        }
      case 'date':
      case 'time':
      case 'datetime':
        return {
          bg: 'from-purple-50 to-pink-50',
          border: 'border-purple-200/60',
          focus: 'focus:border-purple-400 focus:ring-purple-400/20',
          label: 'text-purple-700',
          icon: 'from-purple-500 to-pink-500'
        }
      case 'textarea':
        return {
          bg: 'from-amber-50 to-orange-50',
          border: 'border-amber-200/60',
          focus: 'focus:border-amber-400 focus:ring-amber-400/20',
          label: 'text-amber-700',
          icon: 'from-amber-500 to-orange-500'
        }
      case 'select':
        return {
          bg: 'from-cyan-50 to-blue-50',
          border: 'border-cyan-200/60',
          focus: 'focus:border-cyan-400 focus:ring-cyan-400/20',
          label: 'text-cyan-700',
          icon: 'from-cyan-500 to-blue-500'
        }
      case 'radio':
      case 'checkbox':
        return {
          bg: 'from-rose-50 to-pink-50',
          border: 'border-rose-200/60',
          focus: 'focus:border-rose-400 focus:ring-rose-400/20',
          label: 'text-rose-700',
          icon: 'from-rose-500 to-pink-500'
        }
      default:
        return {
          bg: 'from-gray-50 to-slate-50',
          border: 'border-gray-200/60',
          focus: 'focus:border-gray-400 focus:ring-gray-400/20',
          label: 'text-gray-700',
          icon: 'from-gray-500 to-slate-500'
        }
    }
  }

  const renderField = (field: FeatureField, isInGroup = false, isCompact = false) => {
    if (!fieldVisibility[field.key]) return null;

    const value = formData[field.key];
    const hasError = errors[field.key];
    const isUndecided = formData[`${field.key}_undecided`] || false;
    const theme = getFieldTypeTheme(field.type)
    const fieldIcon = getFieldTypeIcon(field.type)

    const handleValueChange = (newValue: any) => { // eslint-disable-line @typescript-eslint/no-explicit-any
      handleFieldChange(field.key, newValue);
    };

    const handleUndecidedToggle = (checked: boolean) => {
      handleFieldChange(`${field.key}_undecided`, checked);
      if (checked) {
        handleFieldChange(field.key, null); // 미정 선택 시 값 초기화
      }
    };

    // 필드 레벨에 따른 스타일 적용 (그룹 내에서는 간소화)
    const getFieldLevelStyle = () => {
      if (isInGroup) return '' // 그룹 내에서는 개별 필드 스타일 제거
      
      switch (field.field_level) {
        case 'parent':
          return 'border-l-4 border-indigo-400 pl-6 bg-gradient-to-r from-indigo-50/80 to-purple-50/80 shadow-md';
        case 'child':
          return 'border-l-4 border-orange-300 pl-8 ml-6 bg-gradient-to-r from-orange-50/80 to-amber-50/80 shadow-sm';
        default:
          return 'border-l-4 border-gray-300 pl-6 bg-gradient-to-r from-gray-50/80 to-slate-50/80 shadow-sm';
      }
    };

    // 필드 레벨 아이콘
    const getFieldLevelIcon = () => {
      if (isInGroup) return '' // 그룹 내에서는 레벨 아이콘 제거
      
      switch (field.field_level) {
        case 'parent':
          return '🌟';
        case 'child':
          return '📎';
        default:
          return '🔹';
      }
    };

    const baseInputClasses = cn(
      isCompact ? "w-full px-3 py-2 border-2 rounded-lg font-medium" : "w-full px-4 py-3 border-2 rounded-xl font-medium",
      "transition-all duration-300 shadow-sm hover:shadow-md bg-white/90 backdrop-blur-sm",
      theme.border,
      theme.focus,
      hasError ? 'border-red-300 focus:border-red-400 focus:ring-red-400/20 bg-red-50/50' : '',
      isUndecided ? 'bg-gray-100 text-gray-500 cursor-not-allowed border-gray-300' : 'hover:border-opacity-80'
    );

    const baseSelectClasses = cn(
      baseInputClasses,
      "cursor-pointer appearance-none bg-no-repeat bg-right pr-10",
      "bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iOCIgdmlld0JveD0iMCAwIDEyIDgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTEwIDNMNiA3IDIgMyIgc3Ryb2tlPSIjNjI2MjYyIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjwvc3ZnPg==')]"
    );

    const containerClass = isInGroup 
      ? "space-y-3" 
      : `mb-6 p-6 rounded-2xl transition-all duration-300 hover:shadow-lg ${getFieldLevelStyle()}`

    return (
      <div key={field.key} className={containerClass}>
        {/* 필드 라벨 */}
        <div className={cn("flex items-center", isCompact ? "mb-2" : "mb-4")}>
          {!isInGroup && (
            <div className={cn(
              "bg-gradient-to-br rounded-xl mr-4 flex items-center justify-center shadow-lg transform hover:scale-105 transition-all duration-300",
              isCompact ? "w-8 h-8" : "w-10 h-10",
              theme.icon
            )}>
              <span className={cn("text-white", isCompact ? "text-sm" : "text-lg")}>{fieldIcon}</span>
            </div>
          )}
          <div className="flex-1">
            <label className={cn("block font-bold flex items-center", isCompact ? "text-sm" : "text-lg", theme.label)}>
              <span className="mr-2">{getFieldLevelIcon()}</span>
              {field.name}
              {field.required && <span className={cn("text-red-500 ml-2", isCompact ? "text-sm" : "text-xl")}>*</span>}
              {field.unit && field.show_unit && (
                <span className={cn("font-normal text-gray-500 ml-2 bg-gray-100 px-2 py-1 rounded-full", isCompact ? "text-xs" : "text-sm")}>
                  {field.unit}
                </span>
              )}
            </label>
            {field.placeholder && !isCompact && (
              <p className="text-sm text-gray-600 mt-1">{field.placeholder}</p>
            )}
          </div>
        </div>

        {/* allow_undecided 체크박스 */}
        {field.allow_undecided && (
          <div className={cn("flex items-center bg-yellow-50 border border-yellow-200 rounded-xl", isCompact ? "mb-2 p-2" : "mb-4 p-3")}>
            <input
              type="checkbox"
              checked={isUndecided}
              onChange={(e) => handleUndecidedToggle(e.target.checked)}
              className={cn("text-yellow-500 border-yellow-300 rounded focus:ring-yellow-400/20 mr-3", isCompact ? "w-4 h-4" : "w-5 h-5")}
            />
            <span className={cn("text-yellow-700 font-medium flex items-center", isCompact ? "text-sm" : "")}>
              <span className="mr-2">⏳</span>
              {t('common.date_undetermined')}
            </span>
          </div>
        )}

        {/* 필드 타입별 렌더링 */}
        <div className={cn("bg-gradient-to-br border", isCompact ? "p-3 rounded-lg" : "p-4 rounded-xl", theme.bg, theme.border)}>
          {field.type === 'text' && (
            <Input
              type="text"
              className={baseInputClasses}
              value={isUndecided ? t('common.date_undetermined') : value || ''}
              placeholder={field.placeholder}
              onChange={(e) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            />
          )}

          {field.type === 'number' && (
            <Input
              type="number"
              className={baseInputClasses}
              value={isUndecided ? '' : value || ''}
              placeholder={field.placeholder}
              onChange={(e) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            />
          )}

          {field.type === 'textarea' && (
            <textarea
              className={cn(baseInputClasses, isCompact ? "min-h-[80px]" : "min-h-[120px]", "resize-none")}
              value={isUndecided ? t('common.date_undetermined') : value || ''}
              placeholder={field.placeholder}
              onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => handleValueChange(e.target.value)}
              rows={isCompact ? 3 : 4}
              disabled={isUndecided}
            />
          )}

          {field.type === 'select' && field.options && (
            <select
              className={baseSelectClasses}
              value={isUndecided ? '' : value || ''}
              onChange={(e: React.ChangeEvent<HTMLSelectElement>) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            >
              <option value="">{field.placeholder || t('common.select_placeholder')}</option>
              {field.options.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          )}

          {field.type === 'radio' && field.options && (
            <div className={cn("space-y-2", isCompact ? "space-y-1" : "space-y-3")}>
              {field.options.map((option) => (
                <label key={option.value} className={cn("flex items-center bg-white/70 rounded-xl hover:bg-white hover:shadow-md transition-all duration-300 cursor-pointer", isCompact ? "space-x-2 p-2" : "space-x-3 p-3")}>
                  <input
                    type="radio"
                    name={field.key}
                    value={option.value}
                    checked={value === option.value}
                    onChange={(e) => handleValueChange(e.target.value)}
                    className={cn("text-indigo-500 border-indigo-300 focus:ring-indigo-400/20", isCompact ? "w-4 h-4" : "w-5 h-5")}
                    disabled={isUndecided}
                  />
                  <span className={cn("text-gray-700 font-medium", isCompact ? "text-sm" : "")}>{option.label}</span>
                </label>
              ))}
            </div>
          )}

          {field.type === 'checkbox' && field.options && (
            <div className={cn("space-y-2", isCompact ? "space-y-1" : "space-y-3")}>
              {field.options.map((option) => {
                const isChecked = Array.isArray(value) ? value.includes(option.value) : false;
                return (
                  <label key={option.value} className={cn("flex items-center bg-white/70 rounded-xl hover:bg-white hover:shadow-md transition-all duration-300 cursor-pointer", isCompact ? "space-x-2 p-2" : "space-x-3 p-3")}>
                    <input
                      type="checkbox"
                      checked={isChecked}
                      onChange={(e) => {
                        const currentValue = Array.isArray(value) ? value : [];
                        if (e.target.checked) {
                          handleValueChange([...currentValue, option.value]);
                        } else {
                          handleValueChange(currentValue.filter(v => v !== option.value));
                        }
                      }}
                      className={cn("text-indigo-500 border-indigo-300 rounded focus:ring-indigo-400/20", isCompact ? "w-4 h-4" : "w-5 h-5")}
                      disabled={isUndecided}
                    />
                    <span className={cn("text-gray-700 font-medium", isCompact ? "text-sm" : "")}>{option.label}</span>
                  </label>
                );
              })}
            </div>
          )}

          {field.type === 'date' && (
            <Input
              type="date"
              className={baseInputClasses}
              value={isUndecided ? '' : value || ''}
              onChange={(e) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            />
          )}

          {field.type === 'time' && (
            <Input
              type="time"
              className={baseInputClasses}
              value={isUndecided ? '' : value || ''}
              onChange={(e) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            />
          )}

          {field.type === 'datetime' && (
            <Input
              type="datetime-local"
              className={baseInputClasses}
              value={isUndecided ? '' : value || ''}
              onChange={(e) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            />
          )}

          {/* feature_scope 및 feature_zones 필드 렌더링 */}
          {field.key === 'feature_scope' && (
            <select
              className={baseSelectClasses}
              value={isUndecided ? '' : value || ''}
              onChange={(e) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            >
              <option value="">{t('dynamic_form.scope_select_placeholder')}</option>
              <option value="overall">{t('dynamic_form.scope_overall')}</option>
              <option value="by_zone">{t('dynamic_form.scope_by_zone')}</option>
            </select>
          )}

          {field.key === 'feature_zones' && formData['feature_scope'] === 'by_zone' && (
            <select
              multiple
              className={cn(baseInputClasses, "min-h-[120px]")}
              value={isUndecided ? [] : (Array.isArray(value) ? value : [])}
              onChange={(e) => {
                const selectedOptions = Array.from(e.target.selectedOptions).map(option => option.value);
                handleValueChange(selectedOptions);
              }}
              disabled={isUndecided}
            >
              {eventZones?.map((zone) => (
                <option key={zone.name} value={zone.name} className="p-2">
                  {zone.name} ({zone.type})
                </option>
              ))}
            </select>
          )}

          {/* internal_resource 및 internal_resource_person 필드 렌더링 */}
          {field.key === 'internal_resource' && (
            <label className={cn("flex items-center bg-white/70 rounded-xl hover:bg-white hover:shadow-md transition-all duration-300 cursor-pointer", isCompact ? "space-x-2 p-3" : "space-x-3 p-4")}>
              <input
                type="checkbox"
                checked={isUndecided ? false : value || false}
                onChange={(e) => handleValueChange(e.target.checked)}
                className={cn("text-indigo-500 border-indigo-300 rounded focus:ring-indigo-400/20", isCompact ? "w-5 h-5" : "w-6 h-6")}
                disabled={isUndecided}
              />
              <span className={cn("text-gray-700 font-medium flex items-center", isCompact ? "text-sm" : "")}>
                <span className="mr-2">🏢</span>
                {t('dynamic_form.internal_resource_label')}
              </span>
            </label>
          )}

          {field.key === 'internal_resource_person' && formData['internal_resource'] && (
            <Input
              type="text"
              className={baseInputClasses}
              value={isUndecided ? t('common.date_undetermined') : value || ''}
              placeholder={field.placeholder || t('dynamic_form.internal_resource_person_placeholder')}
              onChange={(e) => handleValueChange(e.target.value)}
              disabled={isUndecided}
            />
          )}
        </div>

        {/* 에러 메시지 */}
        {hasError && (
          <div className="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
            <p className="text-sm text-red-700 font-medium flex items-center">
              <span className="mr-2">⚠️</span>
              {errors[field.key]}
            </p>
          </div>
        )}

        {/* 힌트 텍스트 (하위 필드용) */}
        {field.field_level === 'child' && field.parent_field && !isInGroup && (
          <div className="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-xl">
            <p className="text-sm text-blue-700 flex items-center">
              <span className="mr-2">💡</span>
              {t('dynamic_form.child_field_hint', { parentField: String(field.parent_field), showWhenValue: String(field.show_when_value) })}
            </p>
          </div>
        )}
      </div>
    );
  };

  // 그룹 렌더링 함수
  const renderGroup = (title: string, icon: string, fields: FeatureField[], theme: string) => {
    if (fields.length === 0) return null;
    
    return (
      <div className={`bg-gradient-to-br ${theme} p-6 rounded-2xl border shadow-lg`}>
        <div className="flex items-center mb-6">
          <div className="w-12 h-12 bg-white/80 rounded-xl mr-4 flex items-center justify-center shadow-md">
            <span className="text-2xl">{icon}</span>
          </div>
          <h4 className="text-xl font-bold text-gray-800">{title}</h4>
        </div>
        
        <div className={cn(
          "grid gap-6",
          fields.length === 1 ? "grid-cols-1" :
          fields.length === 2 ? "grid-cols-1 md:grid-cols-2" :
          "grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
        )}>
          {fields.map(field => (
            <div key={field.key}>
              {renderField(field, true, true)}
            </div>
          ))}
        </div>
      </div>
    );
  };

  if (!feature.config?.fields || feature.config.fields.length === 0) {
    return (
      <div className="text-center py-12 bg-gradient-to-br from-gray-50 to-slate-50 rounded-2xl border border-gray-200">
        <div className="w-16 h-16 bg-gradient-to-br from-gray-300 to-slate-400 rounded-full mx-auto mb-4 flex items-center justify-center">
          <span className="text-white text-2xl">📝</span>
        </div>
        <p className="text-gray-600 text-lg font-medium">{t('dynamic_form.no_input_fields_message')}</p>
      </div>
    )
  }

  const groups = groupFields(feature.config.fields)
  const hasGroups = groups.basic.length > 0 || groups.schedule.length > 0 || groups.operation.length > 0

  return (
    <div className="space-y-8">
      {/* 기능 헤더 */}
      <div className="bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 p-8 rounded-2xl border border-indigo-200/50 shadow-lg">
        <div className="flex items-center mb-4">
          <div className="w-16 h-16 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl mr-6 flex items-center justify-center shadow-lg">
            <span className="text-white text-3xl">{feature.icon}</span>
          </div>
          <div>
            <h3 className="text-2xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
              {feature.name}
            </h3>
            {feature.description && (
              <p className="text-gray-600 text-lg mt-1">{feature.description}</p>
            )}
          </div>
        </div>
      </div>

      {/* 필드 구조 안내 (그룹화된 경우만 표시) */}
      {hasGroups && (
        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-200/50">
          <div className="flex flex-wrap items-center gap-6 text-sm">
            <span className="flex items-center bg-white px-4 py-2 rounded-xl shadow-sm">
              <span className="mr-2 text-lg">🏢</span>
              <span className="font-semibold text-blue-700">{t('dynamic_form.group_basic_info')}</span>
            </span>
            <span className="flex items-center bg-white px-4 py-2 rounded-xl shadow-sm">
              <span className="mr-2 text-lg">📅</span>
              <span className="font-semibold text-purple-700">{t('dynamic_form.group_schedule_management')}</span>
            </span>
            <span className="flex items-center bg-white px-4 py-2 rounded-xl shadow-sm">
              <span className="mr-2 text-lg">⚙️</span>
              <span className="font-semibold text-emerald-700">{t('dynamic_form.group_operation_settings')}</span>
            </span>
          </div>
        </div>
      )}

      {/* 그룹화된 필드 렌더링 */}
      {hasGroups ? (
        <div className="space-y-8">
          {renderGroup(
            t('dynamic_form.group_basic_info'),
            "🏢",
            groups.basic,
            "from-blue-50/80 to-indigo-50/80 border-blue-200/50"
          )}
          
          {renderGroup(
            t('dynamic_form.group_schedule_management'),
            "📅",
            groups.schedule,
            "from-purple-50/80 to-pink-50/80 border-purple-200/50"
          )}
          
          {renderGroup(
            t('dynamic_form.group_operation_settings'),
            "⚙️",
            groups.operation,
            "from-emerald-50/80 to-green-50/80 border-emerald-200/50"
          )}
          
          {groups.others.length > 0 && renderGroup(
            t('dynamic_form.group_other_settings'),
            "📋",
            groups.others,
            "from-gray-50/80 to-slate-50/80 border-gray-200/50"
          )}
        </div>
      ) : (
        // 그룹화되지 않은 필드들은 기존 방식으로 렌더링
        <div className="space-y-6">
          {feature.config.fields.map(field => renderField(field, false, false))}
        </div>
      )}

      {/* 현재 입력된 데이터 표시 (개발용) */}
      {process.env.NODE_ENV === 'development' && Object.keys(formData).length > 0 && (
        <details className="mt-8 p-6 bg-gradient-to-r from-gray-50 to-slate-50 rounded-2xl border border-gray-200">
          <summary className="cursor-pointer text-base font-bold text-gray-700 hover:text-gray-900 transition-colors duration-300">
            {t('dynamic_form.current_input_data_label')}
          </summary>
          <pre className="mt-4 text-sm text-gray-600 overflow-auto bg-white p-4 rounded-xl border">
            {JSON.stringify(formData, null, 2)}
          </pre>
        </details>
      )}
    </div>
  )
} 