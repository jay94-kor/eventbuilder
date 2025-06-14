'use client'

import { useState, useEffect, useCallback } from 'react'
import { useRouter } from 'next/navigation'
import { FeatureCategory, Feature } from '@/types/rfp'
import { apiClient } from '@/lib/api'
import { useRfpStore } from '@/stores/rfpStore'
import DynamicFeatureForm from '@/components/rfp/DynamicFeatureForm'
import GlobalConfigForm from '@/components/rfp/GlobalConfigForm'
import { useTranslation } from '@/lib/i18n';

interface GlobalConfig {
  global_prepare_deadline?: string
  global_delivery_date?: string
  global_internal_resource?: boolean
  global_internal_resource_person?: string
  global_feature_scope?: 'all' | 'by_zone'
  global_feature_zones?: string[]
}

export default function ConfigureRfpPage() {
  const { t } = useTranslation();
  const router = useRouter()
  const [categories, setCategories] = useState<FeatureCategory[]>([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [rfpTitle, setRfpTitle] = useState('')
  const [eventDate, setEventDate] = useState('')
  const [globalConfig, setGlobalConfig] = useState<GlobalConfig>({
    global_prepare_deadline: '',
    global_delivery_date: '',
    global_internal_resource: false,
    global_internal_resource_person: '',
    global_feature_scope: 'all',
    global_feature_zones: []
  })

  // Zustand 스토어에서 상태와 액션 가져오기
  const { selectedFeatures, featureDetails, updateFeatureDetailField, resetRfpStore } = useRfpStore()

  // 모든 features를 평면화한 배열
  const allFeatures = categories.flatMap(category => category.features)

  // 선택된 features 객체들 찾기
  const selectedFeatureObjects = selectedFeatures
    .map(id => allFeatures.find(feature => feature.id === id))
    .filter(Boolean) as Feature[]

  // 백엔드에서 features 데이터 가져오기 - useCallback으로 메모이제이션
  const fetchFeatures = useCallback(async () => {
    try {
      setLoading(true)
      setError(null)
      const response = await apiClient.get<FeatureCategory[]>('/features')
      if (response.success) {
        setCategories(response.data as FeatureCategory[])
      } else {
        setError(response.message || t('common.fetch_features_failed'))
      }
    } catch (err) {
      setError(t('common.network_error'))
      console.error('Error fetching features:', err)
    } finally {
      setLoading(false)
    }
  }, [t])

  // 페이지 로드 시 데이터 가져오기
  useEffect(() => {
    let isMounted = true;
    
    if (isMounted) {
      fetchFeatures()
    }

    return () => {
      isMounted = false;
    };
  }, [fetchFeatures])

  // 선택된 features가 없으면 1단계로 리다이렉트
  useEffect(() => {
    if (!loading && selectedFeatures.length === 0) {
      router.push('/rfp/create')
    }
  }, [selectedFeatures, loading, router])

  // 이전 단계로 이동
  const handlePrevious = () => {
    router.push('/rfp/create') // 2단계 기능 선택 페이지로 이동
  }

  // RFP 저장 및 완료
  const handleSaveRfp = async () => {
    if (!rfpTitle.trim()) {
      alert(t('rfp_configure.title_required_alert'))
      return
    }

    try {
      setSaving(true)
      setError(null)

      // 선택된 features와 상세 정보를 API 형식으로 변환
      const selections = selectedFeatures.map(featureId => ({
        feature_id: featureId,
        details: featureDetails[featureId] || {}
      }))

      const rfpData = {
        title: rfpTitle,
        event_date: eventDate || undefined,
        selections
      }

      console.log('Saving RFP with data:', rfpData)

      const response = await apiClient.post('/rfps', rfpData)

      if (response.success) {
        alert(t('rfp_configure.creation_success_alert'))
        resetRfpStore() // 스토어 초기화
        router.push('/dashboard') // 대시보드로 이동
      } else {
        setError(response.message || t('rfp_configure.creation_failed_alert'))
      }
    } catch (err: unknown) {
      console.error('Error saving RFP:', err)
      setError(err instanceof Error ? err.message : t('rfp_configure.save_error_alert'))
    } finally {
      setSaving(false)
    }
  }

  // Feature 상세 정보 변경 핸들러
  const handleFeatureFormChange = (featureId: number, data: Record<string, unknown>) => {
    // 기존 세부사항을 새 데이터로 완전히 교체
    updateFeatureDetailField(featureId, 'form_data', data)
  }

  if (loading) {
    return (
      <div role="status" aria-live="polite" className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          <div className="text-center">{t('common.loading')}</div>
        </div>
      </div>
    )
  }

  if (error && !saving) {
    return (
      <div role="alert" aria-live="assertive" className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          <div className="text-center text-red-600">{t('common.error_label')}{error}</div>
        </div>
      </div>
    )
  }

  return (
    <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div className="px-4 py-6 sm:px-0">
        {/* 페이지 헤더 */}
        <div className="mb-8">
          <h1 className="text-heading-xl mb-2">
            {t('rfp_configure.title')}
          </h1>
          <p className="text-muted-foreground">
            {t('rfp_configure.subtitle')}
          </p>
        </div>

        {/* 진행 단계 표시 */}
        <div className="mb-8">
          <div className="flex items-center">
            <div className="flex items-center text-green-600">
              <div className="flex items-center justify-center w-8 h-8 bg-green-600 rounded-full text-white text-sm font-medium" aria-hidden="true">
                ✓
              </div>
              <span className="ml-2 text-label">{t('rfp_basic_info.step1_title')}</span>
            </div>
            <div className="flex-1 mx-4 h-px bg-green-300" aria-hidden="true"></div>
            <div className="flex items-center text-green-600">
              <div className="flex items-center justify-center w-8 h-8 bg-green-600 rounded-full text-white text-sm font-medium" aria-hidden="true">
                ✓
              </div>
              <span className="ml-2 text-label">{t('rfp_basic_info.step2_title')}</span>
            </div>
            <div className="flex-1 mx-4 h-px bg-green-300" aria-hidden="true"></div>
            <div className="flex items-center text-blue-600">
              <div className="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-full text-white text-sm font-medium" aria-hidden="true">
                3
              </div>
              <span className="ml-2 text-label">{t('rfp_basic_info.step3_title')}</span>
            </div>
            <div className="flex-1 mx-4 h-px bg-border" aria-hidden="true"></div>
            <div className="flex items-center text-muted-foreground">
              <div className="flex items-center justify-center w-8 h-8 bg-muted rounded-full text-muted-foreground text-sm font-medium" aria-hidden="true">
                4
              </div>
              <span className="ml-2 text-label">{t('rfp_basic_info.step4_title')}</span>
            </div>
          </div>
        </div>

        {/* 오류 메시지 */}
        {error && (
          <div role="alert" aria-live="assertive" className="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div className="text-sm text-red-600">{error}</div>
          </div>
        )}

        {/* RFP 기본 정보 */}
        <div className="bg-white rounded-2xl shadow-xl p-6 mb-8 border-l-4 border-blue-500">
          <div className="flex items-center mb-4">
            <div className="p-2 bg-blue-100 rounded-lg mr-3">
              <span className="text-2xl" role="img" aria-label="클립보드 아이콘">📋</span>
            </div>
            <h2 className="text-xl font-bold text-gray-900">{t('rfp_configure.basic_info_section_title')}</h2>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                {t('rfp_configure.rfp_title_label')} <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                value={rfpTitle}
                onChange={(e) => setRfpTitle(e.target.value)}
                className="block w-full px-4 py-3 border-2 border-blue-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-blue-500/20 transition-all"
                placeholder={t('rfp_configure.rfp_title_placeholder')}
                required
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                {t('rfp_configure.event_date_label')}
              </label>
              <input
                type="date"
                value={eventDate}
                onChange={(e) => setEventDate(e.target.value)}
                className="block w-full px-4 py-3 border-2 border-blue-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-blue-500/20 transition-all"
              />
            </div>
          </div>
        </div>

        {/* 전역 기본 설정 */}
        <div className="mb-8">
          <GlobalConfigForm
            config={globalConfig}
            onChange={setGlobalConfig}
            eventZones={[]} // TODO: 이벤트 존 데이터 연결
          />
        </div>

        {/* 선택된 features 요약 */}
        <div className="bg-white rounded-2xl shadow-xl p-6 mb-8 border-l-4 border-green-500">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center">
              <div className="p-2 bg-green-100 rounded-lg mr-3">
                <span className="text-2xl" role="img" aria-label="체크 표시 아이콘">✅</span>
              </div>
              <h2 className="text-xl font-bold text-gray-900">
                선택된 기능 ({selectedFeatures.length}개)
              </h2>
            </div>
            <div className="text-sm text-gray-500">
              각 기능의 개별 설정을 확인하세요
            </div>
          </div>
          <div className="flex flex-wrap gap-3">
            {selectedFeatureObjects.map((feature) => (
              <span
                key={feature.id}
                className="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-800 border border-blue-200 shadow-sm"
              >
                <span className="mr-2" role="img" aria-label={`${feature.name} 아이콘`}>{feature.icon}</span>
                {feature.name}
              </span>
            ))}
          </div>
        </div>

        {/* 개별 기능 상세 설정 - 그리드 레이아웃 */}
        <div className="mb-8">
          <div className="flex items-center mb-6">
            <div className="p-2 bg-purple-100 rounded-lg mr-3">
              <span className="text-2xl" role="img" aria-label="톱니바퀴 아이콘">⚙️</span>
            </div>
            <h2 className="text-xl font-bold text-gray-900">개별 기능 상세 설정</h2>
            <div className="ml-auto text-sm text-gray-500">
              전역 설정과 다른 부분만 개별 설정하세요
            </div>
          </div>
          
          <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            {selectedFeatureObjects.map((feature) => (
              <div key={feature.id} className="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-all duration-300">
                                 <DynamicFeatureForm
                   feature={feature}
                   formData={featureDetails[feature.id]?.form_data || {}}
                   onChange={(data) => handleFeatureFormChange(feature.id, data)}
                   errors={{}}
                 />
              </div>
            ))}
          </div>
        </div>

        {/* 이전/완료 버튼 */}
        <div className="mt-8 flex justify-between">
          <button
            onClick={handlePrevious}
            disabled={saving}
            className="px-6 py-2 border border text-muted-foreground rounded-md hover:bg-muted font-medium disabled:opacity-50"
          >
            {t('rfp_configure.previous_step_button')}
          </button>
          <button
            onClick={handleSaveRfp}
            disabled={saving || !rfpTitle.trim()}
            className="px-6 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {saving ? t('rfp_configure.saving_button') : t('rfp_configure.complete_creation_button')}
          </button>
        </div>
      </div>
    </div>
  )
} 