'use client'

import { useState, useEffect, useMemo, useCallback } from 'react'
import { useRouter } from 'next/navigation'
import { FeatureCategory, RfpSelection, RecommendedFeature } from '@/types/rfp'
import { apiClient } from '@/lib/api'
import { useRfpStore } from '@/stores/rfpStore'
import { useTranslation } from '@/lib/i18n'
import CategorySection from '@/components/rfp/CategorySection'
import RecommendationAlert from '@/components/rfp/RecommendationAlert'
import { RfpFormProps } from '@/types/rfp'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { CheckIcon, SearchIcon, FilterIcon, ChevronDownIcon, ChevronUpIcon, ArrowLeftIcon, ArrowRightIcon } from 'lucide-react'

export default function RfpForm({ initialData }: RfpFormProps) {
  const router = useRouter()
  const [categories, setCategories] = useState<FeatureCategory[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showRecommendations, setShowRecommendations] = useState(true)
  const [dismissedRecommendations, setDismissedRecommendations] = useState<Set<number>>(new Set())
  const [searchTerm, setSearchTerm] = useState<string>('')
  const [selectedCategories, setSelectedCategories] = useState<Set<number>>(new Set())
  const [filterPremium, setFilterPremium] = useState<boolean>(false)
  const [filterBudget, setFilterBudget] = useState<boolean>(false)
  const [filterInternal, setFilterInternal] = useState<boolean>(false)
  const [showAdvancedFilters, setShowAdvancedFilters] = useState<boolean>(false)

  const t = useTranslation();

  const { rfpBasicInfo, selectedFeatures, setRfpData, toggleSelectedFeature } = useRfpStore();
  
  const isEditMode = !!initialData;

  useEffect(() => {
    if (isEditMode && initialData) {
      const basicInfoForStore = {
        title: initialData.title,
        event_date: (initialData as any).event_date || "",
        expected_attendees: ((initialData as any).expected_attendees || '').toString(),
        total_budget: ((initialData as any).total_budget || '').toString(),
        is_total_budget_undecided: (initialData as any).is_total_budget_undecided || false,
        description: (initialData as any).description || "",
      };
      
      setRfpData({
        basicInfo: basicInfoForStore,
        selectedFeatures: initialData.selections?.map((s: RfpSelection) => s.feature_id) || []
      });
    } else {
       if (!rfpBasicInfo.title) {
        router.push('/rfp/create/basic-info');
      }
    }
  }, [isEditMode, initialData, setRfpData, router, rfpBasicInfo.title]);

  const allFeatures = useMemo(() => {
    return categories.flatMap((category: FeatureCategory) => {
      if (!category || !category.features || !Array.isArray(category.features)) {
        return [];
      }
      return category.features;
    });
  }, [categories])

  const fetchFeatures = useCallback(async () => {
    try {
      setLoading(true)
      setError(null)
      const response = await apiClient.get<FeatureCategory[]>('/features')
      if (response.success) {
        const categories = response.data as FeatureCategory[];
        const sortedCategories = categories.sort((a, b) => 
          (a.order || 0) - (b.order || 0)
        );
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

  const recommendedFeatures = useMemo(() => {
    if (allFeatures.length === 0 || selectedFeatures.length === 0) return [];
    const newRecommendations = new Map<number, RecommendedFeature>();
    selectedFeatures.forEach(selectedId => {
      const feature = allFeatures.find((f) => f.id === selectedId);
      if (feature?.recommendations?.length) {
        feature.recommendations.forEach((recommendedFeature) => {
          if (!selectedFeatures.includes(recommendedFeature.id) && !dismissedRecommendations.has(recommendedFeature.id)) {
            newRecommendations.set(recommendedFeature.id, recommendedFeature as RecommendedFeature);
          }
        });
      }
    });
    return Array.from(newRecommendations.values());
  }, [selectedFeatures, allFeatures, dismissedRecommendations])

  const handleSelectRecommendation = (feature: RecommendedFeature) => {
    toggleSelectedFeature(feature.id);
  };

  const handleDismissRecommendations = () => {
    const currentRecommendationIds = recommendedFeatures.map(feature => feature.id)
    setDismissedRecommendations(prev => new Set([...prev, ...currentRecommendationIds]));
    setShowRecommendations(false)
  }

  useEffect(() => {
    if (recommendedFeatures.length > 0 && !showRecommendations) {
      setShowRecommendations(true)
    }
  }, [recommendedFeatures.length, showRecommendations])

  const handleNext = () => {
    if (selectedFeatures.length === 0) {
      alert(t('common.min_one_feature_alert'))
      return
    }
    const configureUrl = isEditMode
      ? `/rfp/${initialData?.id}/configure`
      : '/rfp/create/configure';
    router.push(configureUrl);
  }

  const handlePrevious = () => {
    if (isEditMode) {
      router.back();
    } else {
      router.push('/rfp/create/basic-info');
    }
  }

  const clearAllFilters = () => {
    setSearchTerm('');
    setSelectedCategories(new Set());
    setFilterPremium(false);
    setFilterBudget(false);
    setFilterInternal(false);
  }

  // 필터링된 카테고리 및 기능 목록
  const filteredCategories = useMemo(() => {
    if (!categories || categories.length === 0) {
      return [];
    }
    
    return categories
      .map(category => {
        if (!category || !category.features || !Array.isArray(category.features)) {
          return { ...category, features: [] };
        }
        
        const filteredFeatures = category.features.filter(feature => {
          if (!feature) return false;
          
          const matchesSearch = searchTerm === '' ||
                                (feature.name && feature.name.toLowerCase().includes(searchTerm.toLowerCase())) ||
                                (feature.description && feature.description.toLowerCase().includes(searchTerm.toLowerCase()));
          const matchesCategory = selectedCategories.size === 0 || (feature.feature_category_id && selectedCategories.has(feature.feature_category_id));
          const matchesPremium = !filterPremium || feature.is_premium;
          const matchesBudget = !filterBudget || feature.budget_allocation;
          const matchesInternal = !filterInternal || feature.internal_resource_flag;

          return matchesSearch && matchesCategory && matchesPremium && matchesBudget && matchesInternal;
        });
        
        return { ...category, features: filteredFeatures };
      })
      .filter(category => category && category.features && category.features.length > 0);
  }, [categories, searchTerm, selectedCategories, filterPremium, filterBudget, filterInternal]);

  const hasActiveFilters = searchTerm !== '' || selectedCategories.size > 0 || filterPremium || filterBudget || filterInternal;

  if (loading) return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center">
      <Card className="border-0 shadow-xl bg-white/95 backdrop-blur-sm">
        <CardContent className="p-8 text-center">
          <div className="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
            <div className="w-8 h-8 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          </div>
          <p className="text-lg font-medium text-gray-700">{t('common.loading')}</p>
          <p className="text-sm text-gray-500 mt-1">기능 목록을 불러오는 중...</p>
        </CardContent>
      </Card>
    </div>
  );
  
  if (error) return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center">
      <Card className="border-0 shadow-xl bg-white/95 backdrop-blur-sm">
        <CardContent className="p-8 text-center">
          <div className="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
            <span className="text-3xl">⚠️</span>
          </div>
          <h3 className="text-lg font-semibold text-red-700 mb-2">오류가 발생했습니다</h3>
          <p className="text-red-600 text-sm">{error}</p>
          <Button 
            onClick={fetchFeatures} 
            className="mt-4"
            variant="outline"
          >
            다시 시도
          </Button>
        </CardContent>
      </Card>
    </div>
  );

  const step2Title = isEditMode ? 'RFP 수정 - 기능 선택' : t('common.select_elements');

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* 페이지 헤더 */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-4">
            <span className="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
            {isEditMode ? 'RFP 수정' : 'RFP 생성'}
          </div>
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            {isEditMode ? '기능 선택 수정' : '행사 구성 요소 선택'}
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            {isEditMode 
              ? 'RFP의 구성 요소를 수정합니다.' 
              : '원하는 행사 구성 요소를 선택하여 맞춤형 RFP를 생성하세요.'
            }
          </p>
          <div className="flex items-center justify-center mt-4">
            <div className="bg-white/80 backdrop-blur-sm px-6 py-2 rounded-full border shadow-sm">
              <div className="flex items-center text-blue-600">
                <CheckIcon className="w-4 h-4 mr-2" />
                <span className="text-sm font-medium">{selectedFeatures.length}개 선택됨</span>
              </div>
            </div>
          </div>
        </div>

        {/* 진행 단계 표시 - 개선된 버전 */}
        <Card className="border-0 shadow-lg bg-white/95 backdrop-blur-sm mb-8">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              {/* 1단계 - 완료 */}
              <div className="flex items-center">
                <div className="flex items-center justify-center w-10 h-10 bg-green-500 rounded-full shadow-lg">
                  <CheckIcon className="w-5 h-5 text-white" />
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-green-600">완료</p>
                  <p className="text-xs text-green-500">{t('rfp_basic_info.step1_title')}</p>
                </div>
              </div>
              
              <div className="flex-1 mx-4 h-1 bg-gradient-to-r from-green-500 to-blue-500 rounded-full"></div>
              
              {/* 2단계 - 현재 */}
              <div className="flex items-center">
                <div className="flex items-center justify-center w-10 h-10 bg-blue-500 rounded-full shadow-lg animate-pulse">
                  <span className="text-white font-semibold">2</span>
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-blue-600">진행 중</p>
                  <p className="text-xs text-blue-500">{t('rfp_basic_info.step2_title')}</p>
                </div>
              </div>
              
              <div className="flex-1 mx-4 h-1 bg-gray-200 rounded-full"></div>
              
              {/* 3단계 - 대기 */}
              <div className="flex items-center">
                <div className="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                  <span className="text-gray-500 font-semibold">3</span>
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-gray-500">대기</p>
                  <p className="text-xs text-gray-400">{t('rfp_basic_info.step3_title')}</p>
                </div>
              </div>
              
              <div className="flex-1 mx-4 h-1 bg-gray-200 rounded-full"></div>
              
              {/* 4단계 - 대기 */}
              <div className="flex items-center">
                <div className="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                  <span className="text-gray-500 font-semibold">4</span>
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-gray-500">대기</p>
                  <p className="text-xs text-gray-400">{t('rfp_basic_info.step4_title')}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* 추천 시스템 */}
        {showRecommendations && recommendedFeatures.length > 0 && (
          <RecommendationAlert
            recommendations={recommendedFeatures}
            onSelectRecommendation={handleSelectRecommendation}
            onClose={handleDismissRecommendations}
          />
        )}

        {/* 검색 및 필터링 섹션 - 개선된 버전 */}
        <Card className="border-0 shadow-lg bg-white/95 backdrop-blur-sm mb-8">
          <CardHeader className="pb-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <SearchIcon className="w-5 h-5 text-blue-500 mr-2" />
                <CardTitle className="text-xl text-gray-900">기능 검색 및 필터</CardTitle>
              </div>
              {hasActiveFilters && (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={clearAllFilters}
                  className="text-gray-600 hover:text-gray-800"
                >
                  필터 초기화
                </Button>
              )}
            </div>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* 검색바 */}
            <div className="relative">
              <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <Input
                type="text"
                placeholder="기능명이나 설명으로 검색하세요..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10 h-12 border-gray-200 focus:border-blue-500 focus:ring-blue-500/20"
              />
            </div>

            {/* 고급 필터 토글 */}
            <div className="flex items-center justify-between">
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                className="text-gray-600 hover:text-gray-800"
              >
                <FilterIcon className="w-4 h-4 mr-2" />
                고급 필터
                {showAdvancedFilters ? (
                  <ChevronUpIcon className="w-4 h-4 ml-2" />
                ) : (
                  <ChevronDownIcon className="w-4 h-4 ml-2" />
                )}
              </Button>
              {hasActiveFilters && (
                <Badge variant="secondary" className="bg-blue-100 text-blue-800">
                  {[
                    searchTerm && '검색',
                    selectedCategories.size > 0 && `카테고리 ${selectedCategories.size}개`,
                    filterPremium && '프리미엄',
                    filterBudget && '예산배정',
                    filterInternal && '내부리소스'
                  ].filter(Boolean).join(', ')} 적용
                </Badge>
              )}
            </div>

            {/* 고급 필터 옵션 */}
            {showAdvancedFilters && (
              <div className="space-y-4 pt-4 border-t border-gray-100">
                {/* 카테고리 필터 */}
                <div>
                  <Label className="text-sm font-semibold text-gray-700 mb-3 block">카테고리별 필터</Label>
                  <div className="flex flex-wrap gap-2">
                    {categories.map(category => (
                      <div key={category.id} className="flex items-center">
                        <Checkbox
                          id={`category-${category.id}`}
                          checked={selectedCategories.has(category.id)}
                          onCheckedChange={(checked) => {
                            setSelectedCategories(prev => {
                              const newSet = new Set(prev);
                              if (checked) {
                                newSet.add(category.id);
                              } else {
                                newSet.delete(category.id);
                              }
                              return newSet;
                            });
                          }}
                          className="mr-2"
                        />
                        <Label 
                          htmlFor={`category-${category.id}`} 
                          className="cursor-pointer text-sm px-3 py-1 rounded-full border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors"
                        >
                          {t(`featureCategories.${category.name.toLowerCase().replace(/[\s·]/g, '_').replace(/[^\w가-힣]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '')}`)}
                        </Label>
                      </div>
                    ))}
                  </div>
                </div>

                {/* 특성별 필터 */}
                <div>
                  <Label className="text-sm font-semibold text-gray-700 mb-3 block">특성별 필터</Label>
                  <div className="flex flex-wrap gap-3">
                    <div className="flex items-center">
                      <Checkbox
                        id="filterPremium"
                        checked={filterPremium}
                        onCheckedChange={(checked) => setFilterPremium(checked as boolean)}
                        className="mr-2"
                      />
                      <Label 
                        htmlFor="filterPremium" 
                        className="cursor-pointer text-sm px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200"
                      >
                        ✨ {t('features.premium')}
                      </Label>
                    </div>
                    <div className="flex items-center">
                      <Checkbox
                        id="filterBudget"
                        checked={filterBudget}
                        onCheckedChange={(checked) => setFilterBudget(checked as boolean)}
                        className="mr-2"
                      />
                      <Label 
                        htmlFor="filterBudget" 
                        className="cursor-pointer text-sm px-3 py-1 rounded-full bg-green-100 text-green-800 border border-green-200"
                      >
                        💰 {t('features.budget_allocation')}
                      </Label>
                    </div>
                    <div className="flex items-center">
                      <Checkbox
                        id="filterInternal"
                        checked={filterInternal}
                        onCheckedChange={(checked) => setFilterInternal(checked as boolean)}
                        className="mr-2"
                      />
                      <Label 
                        htmlFor="filterInternal" 
                        className="cursor-pointer text-sm px-3 py-1 rounded-full bg-blue-100 text-blue-800 border border-blue-200"
                      >
                        👥 {t('features.internal_resource')}
                      </Label>
                    </div>
                  </div>
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        {/* 기능 목록 */}
        <div className="space-y-6">
          {filteredCategories.length > 0 ? (
            filteredCategories.map((category) => (
              <Card key={category.id} className="border-0 shadow-lg bg-white/95 backdrop-blur-sm overflow-hidden hover:shadow-xl transition-all duration-300">
                <CategorySection
                  categoryName={category.name}
                  features={category.features}
                  selectedFeatureIds={selectedFeatures}
                  onFeatureSelect={toggleSelectedFeature}
                />
              </Card>
            ))
          ) : (
            <Card className="border-0 shadow-lg bg-white/95 backdrop-blur-sm">
              <CardContent className="text-center py-16">
                <div className="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                  <SearchIcon className="w-12 h-12 text-gray-400" />
                </div>
                <h3 className="text-xl font-semibold text-gray-900 mb-2">검색 결과가 없습니다</h3>
                <p className="text-gray-600 mb-6">다른 검색어를 시도하거나 필터를 조정해보세요.</p>
                {hasActiveFilters && (
                  <Button onClick={clearAllFilters} variant="outline">
                    모든 필터 초기화
                  </Button>
                )}
              </CardContent>
            </Card>
          )}
        </div>

        {/* 하단 네비게이션 */}
        <Card className="border-0 shadow-lg bg-white/95 backdrop-blur-sm mt-8">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <Button
                onClick={handlePrevious}
                variant="outline"
                className="flex items-center px-6 py-3"
              >
                <ArrowLeftIcon className="w-4 h-4 mr-2" />
                이전 단계
              </Button>
              
              <div className="text-center">
                <p className="text-sm text-gray-600">
                  {selectedFeatures.length > 0 
                    ? `${selectedFeatures.length}개 기능이 선택되었습니다`
                    : '최소 1개 이상의 기능을 선택해주세요'
                  }
                </p>
              </div>
              
              <Button
                onClick={handleNext}
                disabled={selectedFeatures.length === 0}
                className="flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                다음 단계
                <ArrowRightIcon className="w-4 h-4 ml-2" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
