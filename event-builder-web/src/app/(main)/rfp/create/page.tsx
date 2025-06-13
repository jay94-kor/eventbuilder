'use client'

import { useState, useEffect, useCallback } from 'react'
import { useRouter } from 'next/navigation'
import { FeatureCategory, Feature, RfpSelection, RecommendedFeature } from '@/types/rfp'
import { apiClient } from '@/lib/api'
import { useRfpStore } from '@/stores/rfpStore'
import { useTranslation } from '@/lib/i18n'
import CategorySection from '@/components/rfp/CategorySection'
import RecommendationAlert from '@/components/rfp/RecommendationAlert'
import DynamicFeatureForm from '@/components/rfp/DynamicFeatureForm'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'

import { ArrowRightIcon, CheckIcon, ClockIcon, UsersIcon, CalendarIcon, DollarSignIcon, SearchIcon, FilterIcon, Sparkles, Eye, Edit3, Save } from 'lucide-react'

type WizardStep = 'concept' | 'build' | 'review'

export default function CreateRfpPage() {
  const router = useRouter()
  const t = useTranslation()
  
  // 위저드 상태
  const [currentStep, setCurrentStep] = useState<WizardStep>('concept')
  
  // 데이터 상태
  const [categories, setCategories] = useState<FeatureCategory[]>([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  
  // 기본 정보 상태 (인라인으로 관리)
  const [rfpTitle, setRfpTitle] = useState('')
  const [eventDate, setEventDate] = useState('')
  const [expectedAttendees, setExpectedAttendees] = useState('')
  const [totalBudget, setTotalBudget] = useState('')
  const [isTotalBudgetUndecided, setIsTotalBudgetUndecided] = useState(false)
  const [description, setDescription] = useState('')
  
  // 필터링 상태
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedCategories, setSelectedCategories] = useState<Set<number>>(new Set())
  const [showAdvancedFilters, setShowAdvancedFilters] = useState(false)
  
  // 추천 상태
  const [showRecommendations, setShowRecommendations] = useState(true)
  const [dismissedRecommendations, setDismissedRecommendations] = useState<Set<number>>(new Set())
  
  const { selectedFeatures, featureDetails, toggleSelectedFeature, updateFeatureDetailField, resetRfpStore } = useRfpStore()

  // 모든 기능 평면화
  const allFeatures = categories.flatMap(category => category.features || [])
  
  // 선택된 기능 객체들
  const selectedFeatureObjects = selectedFeatures
    .map(id => allFeatures.find(feature => feature.id === id))
    .filter(Boolean) as Feature[]

  // 추천 기능 계산
  const recommendedFeatures = (() => {
    if (allFeatures.length === 0 || selectedFeatures.length === 0) return [];
    const recommendations = new Map<number, RecommendedFeature>();
    selectedFeatures.forEach(selectedId => {
      const feature = allFeatures.find((f) => f.id === selectedId);
      if (feature?.recommendations?.length) {
        feature.recommendations.forEach((recommendedFeature) => {
          if (!selectedFeatures.includes(recommendedFeature.id) && !dismissedRecommendations.has(recommendedFeature.id)) {
            recommendations.set(recommendedFeature.id, recommendedFeature as RecommendedFeature);
          }
        });
      }
    });
    return Array.from(recommendations.values());
  })()

  // 데이터 가져오기
  const fetchFeatures = useCallback(async () => {
    try {
      setLoading(true)
      setError(null)
      const response = await apiClient.get<FeatureCategory[]>('/features')
      if (response.success) {
        const categories = response.data as FeatureCategory[];
        const sortedCategories = categories.sort((a, b) => (a.order || 0) - (b.order || 0));
        setCategories(sortedCategories)
      } else {
        setError(response.message || t('common.fetch_features_failed'))
      }
    } catch {
      setError(t('common.network_error'))
    } finally {
      setLoading(false)
    }
  }, [t])

  useEffect(() => {
    fetchFeatures();
  }, [fetchFeatures]);

  // 필터링된 카테고리
  const filteredCategories = categories
    .map(category => {
      if (!category?.features) return { ...category, features: [] };
      
      const filteredFeatures = category.features.filter(feature => {
        if (!feature) return false;
        const matchesSearch = searchTerm === '' ||
                              (feature.name && feature.name.toLowerCase().includes(searchTerm.toLowerCase())) ||
                              (feature.description && feature.description.toLowerCase().includes(searchTerm.toLowerCase()));
        const matchesCategory = selectedCategories.size === 0 || selectedCategories.has(feature.feature_category_id);
        return matchesSearch && matchesCategory;
      });
      
      return { ...category, features: filteredFeatures };
    })
    .filter(category => category?.features?.length > 0);

  // 스텝 진행 검증
  const canProceedFromConcept = rfpTitle.trim().length > 0
  const canProceedFromBuild = selectedFeatures.length > 0
  
  // 추천 기능 핸들러
  const handleSelectRecommendation = (feature: RecommendedFeature) => {
    toggleSelectedFeature(feature.id);
  };

  const handleDismissRecommendations = () => {
    const currentRecommendationIds = recommendedFeatures.map(feature => feature.id)
    setDismissedRecommendations(prev => new Set([...prev, ...currentRecommendationIds]));
    setShowRecommendations(false)
  }

  // 기능 상세 정보 변경
  const handleFeatureFormChange = (featureId: number, data: Record<string, unknown>) => {
    updateFeatureDetailField(featureId, 'form_data', data)
  }

  // RFP 저장
  const handleSaveRfp = async () => {
    try {
      setSaving(true)
      setError(null)

      const selections = selectedFeatures.map(featureId => ({
        feature_id: featureId,
        details: featureDetails[featureId]?.form_data || {}
      }))

      const rfpData = {
        title: rfpTitle,
        event_date: eventDate || undefined,
        expected_attendees: expectedAttendees ? parseInt(expectedAttendees) : undefined,
        total_budget: isTotalBudgetUndecided ? null : (totalBudget ? parseInt(totalBudget) : undefined),
        is_total_budget_undecided: isTotalBudgetUndecided,
        description: description || undefined,
        selections
      }

      const response = await apiClient.post('/rfps', rfpData)

      if (response.success) {
        resetRfpStore()
        router.push('/dashboard')
      } else {
        setError(response.message || 'RFP 생성에 실패했습니다.')
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'RFP 저장 오류가 발생했습니다.')
    } finally {
      setSaving(false)
    }
  }

  // 로딩 상태
  if (loading) return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center">
      <Card className="border-0 shadow-xl bg-white/95 backdrop-blur-sm">
        <CardContent className="p-8 text-center">
          <div className="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
            <div className="w-8 h-8 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          </div>
          <p className="text-lg font-medium text-gray-700">기능 목록을 불러오는 중...</p>
        </CardContent>
      </Card>
    </div>
  );

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 via-blue-50 to-indigo-100 relative overflow-hidden">
      {/* 배경 장식 요소 */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-purple-400/20 to-pink-400/20 rounded-full blur-3xl"></div>
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-indigo-400/20 rounded-full blur-3xl"></div>
        <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-emerald-400/10 to-cyan-400/10 rounded-full blur-3xl"></div>
      </div>
      
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        {/* 헤더 */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 via-pink-500 to-indigo-600 rounded-3xl mb-6 shadow-2xl">
            <span className="text-3xl">✨</span>
          </div>
          <h1 className="text-5xl font-extrabold bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 bg-clip-text text-transparent mb-4">
            RFP 생성 위저드
          </h1>
          <p className="text-xl text-gray-700 max-w-2xl mx-auto">
            3단계로 간단하게 <span className="font-semibold text-purple-600">전문적인 RFP</span>를 만들어보세요
          </p>
        </div>

        {/* 진행 상태 표시 */}
        <Card className="border-0 shadow-2xl bg-white/95 backdrop-blur-md mb-12 overflow-hidden">
          <div className="absolute inset-0 bg-gradient-to-r from-purple-500/5 via-pink-500/5 to-indigo-500/5"></div>
          <CardContent className="p-8 relative">
            <div className="flex items-center justify-between">
              {/* 1단계 - 컨셉 */}
              <div className="flex items-center group">
                <div className={`
                  relative flex items-center justify-center w-16 h-16 rounded-2xl shadow-xl transition-all duration-500 transform hover:scale-105
                  ${currentStep === 'concept' 
                    ? 'bg-gradient-to-br from-purple-500 to-pink-500 animate-pulse shadow-purple-500/25' 
                    : canProceedFromConcept 
                      ? 'bg-gradient-to-br from-emerald-500 to-green-500 shadow-emerald-500/25' 
                      : 'bg-gradient-to-br from-gray-300 to-gray-400'
                  }
                `}>
                  {canProceedFromConcept && currentStep !== 'concept' ? (
                    <CheckIcon className="w-8 h-8 text-white" />
                  ) : (
                    <span className={`font-bold text-lg ${
                      currentStep === 'concept' || canProceedFromConcept ? 'text-white' : 'text-gray-500'
                    }`}>1</span>
                  )}
                  {currentStep === 'concept' && (
                    <div className="absolute -inset-1 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl blur opacity-30 animate-pulse"></div>
                  )}
                </div>
                <div className="ml-4">
                  <p className={`text-lg font-bold transition-all duration-300 ${
                    currentStep === 'concept' ? 'text-purple-600' : 
                    canProceedFromConcept ? 'text-emerald-600' : 'text-gray-500'
                  }`}>
                    {currentStep === 'concept' ? '🎯 진행 중' : canProceedFromConcept ? '✅ 완료' : '⏳ 대기'}
                  </p>
                  <p className="text-sm text-gray-700 font-medium">컨셉 정의</p>
                </div>
              </div>
              
              <div className={`flex-1 mx-6 h-2 rounded-full transition-all duration-500 ${
                canProceedFromConcept ? 'bg-gradient-to-r from-emerald-400 via-purple-400 to-pink-400' : 'bg-gray-300'
              }`}></div>
              
              {/* 2단계 - 구성 */}
              <div className="flex items-center group">
                <div className={`
                  relative flex items-center justify-center w-16 h-16 rounded-2xl shadow-xl transition-all duration-500 transform hover:scale-105
                  ${currentStep === 'build' 
                    ? 'bg-gradient-to-br from-blue-500 to-indigo-500 animate-pulse shadow-blue-500/25' 
                    : canProceedFromBuild 
                      ? 'bg-gradient-to-br from-emerald-500 to-green-500 shadow-emerald-500/25' 
                      : 'bg-gradient-to-br from-gray-300 to-gray-400'
                  }
                `}>
                  {canProceedFromBuild && currentStep !== 'build' ? (
                    <CheckIcon className="w-8 h-8 text-white" />
                  ) : (
                    <span className={`font-bold text-lg ${
                      currentStep === 'build' || canProceedFromBuild ? 'text-white' : 'text-gray-500'
                    }`}>2</span>
                  )}
                  {currentStep === 'build' && (
                    <div className="absolute -inset-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl blur opacity-30 animate-pulse"></div>
                  )}
                </div>
                <div className="ml-4">
                  <p className={`text-lg font-bold transition-all duration-300 ${
                    currentStep === 'build' ? 'text-blue-600' : 
                    canProceedFromBuild ? 'text-emerald-600' : 'text-gray-500'
                  }`}>
                    {currentStep === 'build' ? '🔧 진행 중' : canProceedFromBuild ? '✅ 완료' : '⏳ 대기'}
                  </p>
                  <p className="text-sm text-gray-700 font-medium">구성 선택</p>
                </div>
              </div>
              
              <div className={`flex-1 mx-6 h-2 rounded-full transition-all duration-500 ${
                canProceedFromBuild ? 'bg-gradient-to-r from-emerald-400 via-blue-400 to-indigo-400' : 'bg-gray-300'
              }`}></div>
              
              {/* 3단계 - 완성 */}
              <div className="flex items-center group">
                <div className={`
                  relative flex items-center justify-center w-16 h-16 rounded-2xl shadow-xl transition-all duration-500 transform hover:scale-105
                  ${currentStep === 'review' 
                    ? 'bg-gradient-to-br from-emerald-500 to-cyan-500 animate-pulse shadow-emerald-500/25' 
                    : 'bg-gradient-to-br from-gray-300 to-gray-400'
                  }
                `}>
                  <span className={`font-bold text-lg ${currentStep === 'review' ? 'text-white' : 'text-gray-500'}`}>3</span>
                  {currentStep === 'review' && (
                    <div className="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-2xl blur opacity-30 animate-pulse"></div>
                  )}
                </div>
                <div className="ml-4">
                  <p className={`text-lg font-bold transition-all duration-300 ${currentStep === 'review' ? 'text-emerald-600' : 'text-gray-500'}`}>
                    {currentStep === 'review' ? '🎯 진행 중' : '⏳ 대기'}
                  </p>
                  <p className="text-sm text-gray-700 font-medium">검토 및 완성</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* 메인 콘텐츠 */}
        <div className="space-y-8">
          {/* 1단계: 컨셉 정의 */}
          {currentStep === 'concept' && (
            <Card className="border-0 shadow-2xl bg-white/95 backdrop-blur-md overflow-hidden relative">
              <div className="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-pink-500/10 to-indigo-500/10"></div>
              <CardHeader className="text-center relative z-10 pb-8">
                <div className="relative inline-block mb-6">
                  <div className="w-24 h-24 bg-gradient-to-br from-purple-500 via-pink-500 to-indigo-600 rounded-3xl mx-auto flex items-center justify-center shadow-2xl shadow-purple-500/25 transform hover:scale-105 transition-all duration-300">
                    <span className="text-white text-4xl">💡</span>
                  </div>
                  <div className="absolute -inset-3 bg-gradient-to-r from-purple-400 via-pink-400 to-indigo-400 rounded-3xl opacity-20 blur-xl animate-pulse"></div>
                </div>
                <CardTitle className="text-3xl font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 bg-clip-text text-transparent mb-3">
                  어떤 행사를 만들고 싶나요?
                </CardTitle>
                <CardDescription className="text-lg text-gray-600 max-w-lg mx-auto">
                  아이디어만 있어도 괜찮아요. <span className="font-semibold text-purple-600">함께 구체화</span>해 나가겠습니다.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-8 relative z-10">
                {/* 제목 */}
                <div className="space-y-4 bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-2xl border border-purple-200/50">
                  <Label htmlFor="title" className="text-xl font-bold flex items-center text-gray-800">
                    <div className="w-6 h-6 bg-gradient-to-r from-red-500 to-pink-500 rounded-full mr-3 flex items-center justify-center">
                      <span className="w-2 h-2 bg-white rounded-full"></span>
                    </div>
                    행사 이름
                  </Label>
                  <Input
                    id="title"
                    value={rfpTitle}
                    onChange={(e) => setRfpTitle(e.target.value)}
                    placeholder="예: 2024 회사 송년회, AI 컨퍼런스, 제품 론칭 이벤트..."
                    className="h-16 text-lg border-purple-200 focus:border-purple-400 focus:ring-purple-400/20 bg-white/80 shadow-md"
                  />
                </div>

                {/* 기본 정보 (선택사항) */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div className="space-y-3 bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-200/50 hover:shadow-lg transition-all duration-300">
                    <Label htmlFor="date" className="flex items-center text-gray-800 font-semibold">
                      <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl mr-3 flex items-center justify-center">
                        <CalendarIcon className="w-4 h-4 text-white" />
                      </div>
                      언제쯤 개최하나요?
                    </Label>
                    <Input
                      id="date"
                      type="date"
                      value={eventDate}
                      onChange={(e) => setEventDate(e.target.value)}
                      className="h-14 border-blue-200 focus:border-blue-400 focus:ring-blue-400/20 bg-white/80 shadow-md"
                    />
                  </div>

                  <div className="space-y-3 bg-gradient-to-br from-emerald-50 to-green-50 p-6 rounded-2xl border border-emerald-200/50 hover:shadow-lg transition-all duration-300">
                    <Label htmlFor="attendees" className="flex items-center text-gray-800 font-semibold">
                      <div className="w-8 h-8 bg-gradient-to-br from-emerald-500 to-green-500 rounded-xl mr-3 flex items-center justify-center">
                        <UsersIcon className="w-4 h-4 text-white" />
                      </div>
                      예상 참석자 수
                    </Label>
                    <Input
                      id="attendees"
                      type="number"
                      value={expectedAttendees}
                      onChange={(e) => setExpectedAttendees(e.target.value)}
                      placeholder="예: 100"
                      className="h-14 border-emerald-200 focus:border-emerald-400 focus:ring-emerald-400/20 bg-white/80 shadow-md"
                    />
                  </div>

                  <div className="space-y-3 bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-2xl border border-amber-200/50 hover:shadow-lg transition-all duration-300">
                    <Label className="flex items-center text-gray-800 font-semibold">
                      <div className="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl mr-3 flex items-center justify-center">
                        <DollarSignIcon className="w-4 h-4 text-white" />
                      </div>
                      총 예산
                    </Label>
                    <div className="space-y-3">
                      <Input
                        type="number"
                        value={totalBudget}
                        onChange={(e) => setTotalBudget(e.target.value)}
                        placeholder="예: 5000000"
                        disabled={isTotalBudgetUndecided}
                        className="h-14 border-amber-200 focus:border-amber-400 focus:ring-amber-400/20 bg-white/80 shadow-md disabled:bg-gray-100"
                      />
                      <div className="flex items-center space-x-3">
                        <Checkbox
                          id="budget-undecided"
                          checked={isTotalBudgetUndecided}
                          onCheckedChange={(checked) => {
                            setIsTotalBudgetUndecided(checked as boolean)
                            if (checked) setTotalBudget('')
                          }}
                          className="border-amber-300 data-[state=checked]:bg-amber-500"
                        />
                        <Label htmlFor="budget-undecided" className="text-sm text-gray-600 font-medium">
                          아직 미정이에요
                        </Label>
                      </div>
                    </div>
                  </div>
                </div>

                {/* 설명 */}
                <div className="space-y-4 bg-gradient-to-r from-slate-50 to-gray-50 p-6 rounded-2xl border border-gray-200/50">
                  <Label htmlFor="description" className="text-lg font-semibold text-gray-800 flex items-center">
                    <span className="text-2xl mr-3">✨</span>
                    어떤 분위기의 행사인가요? 
                    <span className="text-sm font-normal text-gray-500 ml-2">(선택)</span>
                  </Label>
                  <Textarea
                    id="description"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    placeholder="예: 격식있는 비즈니스 미팅, 캐주얼한 팀 빌딩, 화려한 론칭 이벤트..."
                    rows={4}
                    className="resize-none border-gray-200 focus:border-gray-400 focus:ring-gray-400/20 bg-white/80 shadow-md text-base"
                  />
                </div>

                <div className="text-center pt-8">
                  <Button
                    onClick={() => setCurrentStep('build')}
                    disabled={!canProceedFromConcept}
                    className="group px-10 py-4 text-xl font-semibold bg-gradient-to-r from-purple-500 via-pink-500 to-indigo-600 hover:from-purple-600 hover:via-pink-600 hover:to-indigo-700 shadow-2xl shadow-purple-500/25 hover:shadow-purple-500/40 transform hover:scale-105 transition-all duration-300 rounded-2xl"
                  >
                    다음: 구성 요소 선택하기
                    <ArrowRightIcon className="w-6 h-6 ml-3 group-hover:translate-x-1 transition-transform duration-300" />
                  </Button>
                  {!canProceedFromConcept && (
                    <p className="text-sm text-gray-500 mt-3">행사 이름을 입력해주세요</p>
                  )}
                </div>
              </CardContent>
            </Card>
          )}

          {/* 2단계: 구성 선택 */}
          {currentStep === 'build' && (
            <>
              {/* 실시간 미리보기 */}
              <Card className="border-0 shadow-2xl bg-gradient-to-r from-white via-blue-50/50 to-indigo-50/50 backdrop-blur-md overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-indigo-500/5 to-purple-500/5"></div>
                <CardContent className="p-8 relative">
                  <div className="flex items-center justify-between">
                    <div className="flex-1">
                      <div className="flex items-center mb-3">
                        <div className="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                        <span className="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">실시간 미리보기</span>
                      </div>
                      <h2 className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-3">
                        {rfpTitle}
                      </h2>
                      <div className="flex items-center flex-wrap gap-4 text-sm">
                        {eventDate && (
                          <div className="flex items-center bg-blue-100 px-3 py-2 rounded-xl">
                            <CalendarIcon className="w-4 h-4 mr-2 text-blue-600" />
                            <span className="font-medium text-blue-700">
                              {new Date(eventDate).toLocaleDateString('ko-KR')}
                            </span>
                          </div>
                        )}
                        {expectedAttendees && (
                          <div className="flex items-center bg-emerald-100 px-3 py-2 rounded-xl">
                            <UsersIcon className="w-4 h-4 mr-2 text-emerald-600" />
                            <span className="font-medium text-emerald-700">{expectedAttendees}명</span>
                          </div>
                        )}
                        <div className="flex items-center bg-purple-100 px-3 py-2 rounded-xl">
                          <CheckIcon className="w-4 h-4 mr-2 text-purple-600" />
                          <span className="font-medium text-purple-700">{selectedFeatures.length}개 구성요소</span>
                        </div>
                      </div>
                    </div>
                    <div className="flex items-center space-x-3 ml-6">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setCurrentStep('concept')}
                        className="border-blue-200 text-blue-600 hover:bg-blue-50 hover:border-blue-300 transition-all duration-300"
                      >
                        <Edit3 className="w-4 h-4 mr-2" />
                        기본정보 수정
                      </Button>
                      {selectedFeatures.length > 0 && (
                        <Button
                          size="sm"
                          onClick={() => setCurrentStep('review')}
                          className="bg-gradient-to-r from-emerald-500 to-green-500 hover:from-emerald-600 hover:to-green-600 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transform hover:scale-105 transition-all duration-300"
                        >
                          <Eye className="w-4 h-4 mr-2" />
                          미리보기
                        </Button>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* 검색 및 필터 */}
              <Card className="border-0 shadow-2xl bg-white/95 backdrop-blur-md overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-r from-indigo-500/5 via-purple-500/5 to-pink-500/5"></div>
                <CardContent className="p-8 relative">
                  <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center">
                      <div className="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl mr-4 flex items-center justify-center">
                        <span className="text-white text-xl">🔧</span>
                      </div>
                      <div>
                        <h3 className="text-xl font-bold text-gray-800">필요한 구성 요소를 선택해주세요</h3>
                        <p className="text-sm text-gray-600">원하는 기능을 찾아 클릭해보세요</p>
                      </div>
                    </div>
                    {selectedFeatures.length > 0 && (
                      <Badge className="bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-4 py-2 text-base font-semibold shadow-lg">
                        {selectedFeatures.length}개 선택됨
                      </Badge>
                    )}
                  </div>
                  
                  <div className="relative">
                    <div className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-indigo-500">
                      <SearchIcon className="w-5 h-5" />
                    </div>
                    <Input
                      placeholder="원하는 기능을 검색해보세요... (예: 음향, 조명, 케이터링)"
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="pl-12 h-14 text-base border-indigo-200 focus:border-indigo-400 focus:ring-indigo-400/20 bg-white/80 shadow-md rounded-xl"
                    />
                  </div>
                </CardContent>
              </Card>

              {/* 카테고리별 기능 선택 */}
              <div className="space-y-6">
                {filteredCategories.map((category) => (
                  <Card key={category.id} className="border-0 shadow-lg bg-white/95 backdrop-blur-sm overflow-hidden">
                    <CategorySection
                      categoryName={category.name}
                      features={category.features}
                      selectedFeatureIds={selectedFeatures}
                      onFeatureSelect={toggleSelectedFeature}
                    />
                  </Card>
                ))}
              </div>

              {/* 하단 네비게이션 */}
              <Card className="border-0 shadow-2xl bg-gradient-to-r from-white via-gray-50 to-white backdrop-blur-md overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-r from-gray-500/5 via-slate-500/5 to-gray-500/5"></div>
                <CardContent className="p-8 relative">
                  <div className="flex items-center justify-between">
                    <Button
                      variant="outline"
                      onClick={() => setCurrentStep('concept')}
                      className="border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-gray-400 px-6 py-3 text-base transition-all duration-300"
                    >
                      ← 이전: 기본 정보
                    </Button>
                    
                    <div className="text-center bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-3 rounded-2xl border border-gray-200">
                      <p className="text-base font-semibold text-gray-700">
                        {selectedFeatures.length > 0 
                          ? (
                            <>
                              <span className="text-indigo-600">{selectedFeatures.length}개</span> 구성요소가 선택되었습니다
                            </>
                          )
                          : '최소 1개 이상의 구성요소를 선택해주세요'
                        }
                      </p>
                      {selectedFeatures.length > 0 && (
                        <p className="text-sm text-gray-500 mt-1">다음 단계로 진행할 수 있습니다</p>
                      )}
                    </div>
                    
                    <Button
                      onClick={() => setCurrentStep('review')}
                      disabled={!canProceedFromBuild}
                      className="group bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 shadow-xl shadow-indigo-500/25 hover:shadow-indigo-500/40 transform hover:scale-105 transition-all duration-300 px-8 py-3 text-base font-semibold rounded-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                      다음: 최종 검토
                      <ArrowRightIcon className="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300" />
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </>
          )}

          {/* 3단계: 검토 및 완성 */}
          {currentStep === 'review' && (
            <Card className="border-0 shadow-2xl bg-white/95 backdrop-blur-md overflow-hidden relative">
              <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-cyan-500/10 to-blue-500/10"></div>
              <CardHeader className="text-center relative z-10 pb-8">
                <div className="relative inline-block mb-6">
                  <div className="w-28 h-28 bg-gradient-to-br from-emerald-500 via-cyan-500 to-blue-600 rounded-3xl mx-auto flex items-center justify-center shadow-2xl shadow-emerald-500/25 transform hover:scale-105 transition-all duration-300">
                    <span className="text-white text-5xl">🎯</span>
                  </div>
                  <div className="absolute -inset-4 bg-gradient-to-r from-emerald-400 via-cyan-400 to-blue-400 rounded-3xl opacity-20 blur-xl animate-pulse"></div>
                </div>
                <CardTitle className="text-4xl font-bold bg-gradient-to-r from-emerald-600 via-cyan-600 to-blue-600 bg-clip-text text-transparent mb-4">
                  거의 완성되었습니다!
                </CardTitle>
                <CardDescription className="text-xl text-gray-600 max-w-2xl mx-auto">
                  마지막으로 <span className="font-semibold text-emerald-600">세부 사항을 확인</span>하고 RFP를 완성해주세요.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {/* RFP 요약 */}
                <div className="bg-gray-50 rounded-lg p-6">
                  <h3 className="font-bold text-lg mb-4">{rfpTitle}</h3>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                      <span className="text-gray-600">날짜</span>
                      <p className="font-medium">{eventDate || '미정'}</p>
                    </div>
                    <div>
                      <span className="text-gray-600">참석자</span>
                      <p className="font-medium">{expectedAttendees || '미정'}명</p>
                    </div>
                    <div>
                      <span className="text-gray-600">예산</span>
                      <p className="font-medium">
                        {isTotalBudgetUndecided ? '미정' : totalBudget ? `${parseInt(totalBudget).toLocaleString()}원` : '미정'}
                      </p>
                    </div>
                    <div>
                      <span className="text-gray-600">구성요소</span>
                      <p className="font-medium">{selectedFeatures.length}개</p>
                    </div>
                  </div>
                </div>

                                 {/* 선택된 기능들의 상세 설정 */}
                 {selectedFeatureObjects.length > 0 && (
                   <div className="space-y-4">
                     <h4 className="font-semibold text-lg">구성 요소별 상세 설정</h4>
                     <div className="space-y-4">
                       {selectedFeatureObjects.map((feature) => (
                         <Card key={feature.id} className="border border-gray-200">
                           <CardHeader className="bg-gray-50">
                             <div className="flex items-center space-x-3">
                               <span className="text-2xl">{feature.icon}</span>
                               <div>
                                 <CardTitle className="text-lg">{feature.name}</CardTitle>
                                 {feature.description && (
                                   <CardDescription className="text-sm">
                                     {feature.description}
                                   </CardDescription>
                                 )}
                               </div>
                             </div>
                           </CardHeader>
                           <CardContent className="p-0">
                             <DynamicFeatureForm
                               feature={feature}
                               formData={featureDetails[feature.id]?.form_data || {}}
                               onChange={(data) => handleFeatureFormChange(feature.id, data)}
                               errors={{}}
                             />
                           </CardContent>
                         </Card>
                       ))}
                     </div>
                   </div>
                 )}

                {/* 액션 버튼 */}
                <div className="flex items-center justify-between pt-8 border-t border-gray-200">
                  <Button
                    variant="outline"
                    onClick={() => setCurrentStep('build')}
                    className="border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-gray-400 px-6 py-3 text-base transition-all duration-300"
                  >
                    ← 이전: 구성 수정
                  </Button>
                  
                  <div className="text-center">
                    <Button
                      onClick={handleSaveRfp}
                      disabled={saving}
                      className="group px-12 py-4 text-xl font-bold bg-gradient-to-r from-emerald-500 via-cyan-500 to-blue-500 hover:from-emerald-600 hover:via-cyan-600 hover:to-blue-600 shadow-2xl shadow-emerald-500/30 hover:shadow-emerald-500/50 transform hover:scale-105 transition-all duration-300 rounded-2xl disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
                    >
                      {saving ? (
                        <>
                          <div className="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin mr-3"></div>
                          저장하는 중...
                        </>
                      ) : (
                        <>
                          <Save className="w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
                          🎉 RFP 완성하기
                        </>
                      )}
                    </Button>
                    <p className="text-sm text-gray-500 mt-3">완성된 RFP는 대시보드에서 확인할 수 있습니다</p>
                  </div>
                  
                  <div></div> {/* 공간 균형을 위한 빈 div */}
                </div>
              </CardContent>
            </Card>
          )}
        </div>

        {/* 추천 시스템 */}
        {currentStep === 'build' && showRecommendations && recommendedFeatures.length > 0 && (
          <RecommendationAlert
            recommendations={recommendedFeatures}
            onSelectRecommendation={handleSelectRecommendation}
            onClose={handleDismissRecommendations}
          />
        )}

        {/* 에러 표시 */}
        {error && (
          <Card className="border-red-200 bg-red-50">
            <CardContent className="p-4">
              <p className="text-red-700">{error}</p>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}