'use client'

import React, { useState, useEffect, useMemo } from 'react'
import { RecommendedFeature } from '@/types/rfp'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { useTranslation } from '@/lib/i18n'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { Lightbulb, ChevronDown, ChevronUp, X, Sparkles, Plus, Check, Star } from 'lucide-react'

interface RecommendationAlertProps {
  recommendations: RecommendedFeature[]
  onSelectRecommendation: (feature: RecommendedFeature) => void
  onClose: () => void
}

export default function RecommendationAlert({
  recommendations,
  onSelectRecommendation,
  onClose,
}: RecommendationAlertProps) {
  const t = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [selectedR1, setSelectedR1] = useState<Set<number>>(new Set());
  const [selectedR2, setSelectedR2] = useState<Set<number>>(new Set());

  const sortedRecommendations = useMemo(() => {
    return [...recommendations].sort((a, b) => {
      const levelA = a.pivot?.level === 'R1' ? 1 : 2;
      const levelB = b.pivot?.level === 'R1' ? 1 : 2;
      return levelA - levelB;
    });
  }, [recommendations]);

  const r1Recommendations = sortedRecommendations.filter(
    (rec) => rec.pivot?.level === 'R1'
  );
  const r2Recommendations = sortedRecommendations.filter(
    (rec) => rec.pivot?.level === 'R2'
  );

  useEffect(() => {
    // R1 추천은 기본적으로 선택된 상태로 시작
    setSelectedR1(new Set(r1Recommendations.map((rec) => rec.id)));
    setSelectedR2(new Set());
  }, [recommendations]);

  const handleToggle = (feature: RecommendedFeature, checked: boolean, type: 'R1' | 'R2') => {
    if (type === 'R1') {
      setSelectedR1(prev => {
        const newSet = new Set(prev);
        if (checked) {
          newSet.add(feature.id);
        } else {
          newSet.delete(feature.id);
        }
        return newSet;
      });
    } else {
      setSelectedR2(prev => {
        const newSet = new Set(prev);
        if (checked) {
          newSet.add(feature.id);
        } else {
          newSet.delete(feature.id);
        }
        return newSet;
      });
    }
    onSelectRecommendation(feature);
  };

  const handleApplyAll = () => {
    // R1의 선택된 항목들을 모두 적용
    r1Recommendations.forEach(feature => {
      if (selectedR1.has(feature.id)) {
        onSelectRecommendation(feature);
      }
    });
    onClose();
  };

  if (recommendations.length === 0) {
    return null;
  }

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50">
      <Card className="bg-white/95 backdrop-blur-md border-0 border-t shadow-2xl rounded-t-xl rounded-b-none">
        {/* 토스트 헤더 */}
        <div 
          className="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50/50 transition-colors border-b border-gray-100"
          onClick={() => setIsExpanded(!isExpanded)}
        >
          <div className="flex items-center space-x-3">
            <div className="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-amber-100 to-orange-100 rounded-lg shadow-sm">
              <Lightbulb className="w-4 h-4 text-amber-600" />
            </div>
            <div>
              <div className="flex items-center space-x-2">
                <h3 className="font-bold text-base text-gray-900">💡 추천 기능</h3>
                {r1Recommendations.length > 0 && (
                  <Badge variant="destructive" className="flex items-center space-x-1">
                    <Star className="w-3 h-3" />
                    <span>필수 {r1Recommendations.length}개</span>
                  </Badge>
                )}
                {r2Recommendations.length > 0 && (
                  <Badge variant="secondary" className="bg-blue-100 text-blue-800">
                    추가 {r2Recommendations.length}개
                  </Badge>
                )}
              </div>
              <p className="text-xs text-gray-600 mt-0.5">
                선택하신 기능과 함께 사용하면 더욱 완성도 높은 행사가 됩니다
              </p>
            </div>
          </div>

          {/* 간단한 미리보기 카드들 (접힌 상태) */}
          {!isExpanded && r1Recommendations.length > 0 && (
            <div className="flex items-center space-x-3 mx-6">
              {r1Recommendations.slice(0, 4).map((feature) => (
                                  <div
                    key={feature.id}
                    className={`
                      flex items-center space-x-2 px-2 py-1.5 rounded-lg border-2 transition-all duration-200 cursor-pointer
                      ${selectedR1.has(feature.id)
                        ? 'border-red-200 bg-red-50 shadow-sm'
                        : 'border-gray-200 hover:border-red-300 hover:bg-red-50/50'
                      }
                    `}
                  onClick={(e) => {
                    e.stopPropagation();
                    handleToggle(feature, !selectedR1.has(feature.id), 'R1');
                  }}
                >
                  <span className="text-base">{feature.icon}</span>
                  <span className="font-medium text-xs text-gray-900 whitespace-nowrap">
                    {feature.name}
                  </span>
                  <div className={`
                    w-3.5 h-3.5 rounded border flex items-center justify-center transition-colors
                    ${selectedR1.has(feature.id)
                      ? 'bg-red-500 border-red-500'
                      : 'border-gray-300'
                    }
                  `}>
                    {selectedR1.has(feature.id) && (
                      <Check className="w-2.5 h-2.5 text-white" />
                    )}
                  </div>
                </div>
              ))}
              {r1Recommendations.length > 4 && (
                <div className="text-sm text-gray-500 px-2">
                  +{r1Recommendations.length - 4}개 더
                </div>
              )}
            </div>
          )}
          
          <div className="flex items-center space-x-2">
            {!isExpanded && r1Recommendations.length > 0 && (
                          <Button
              size="sm"
              onClick={(e) => {
                e.stopPropagation();
                handleApplyAll();
              }}
              className="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs"
            >
                              <Plus className="w-3 h-3 mr-1" />
              모두 추가
              </Button>
            )}
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setIsExpanded(!isExpanded)}
              className="text-gray-600 hover:text-gray-800 px-2 py-1 text-xs"
            >
              {isExpanded ? (
                <>
                  <ChevronDown className="w-3 h-3 mr-1" />
                  접기
                </>
              ) : (
                <>
                  <ChevronUp className="w-3 h-3 mr-1" />
                  자세히
                </>
              )}
            </Button>
            <Button
              variant="ghost"
              size="sm"
              onClick={(e) => {
                e.stopPropagation();
                onClose();
              }}
              className="text-gray-400 hover:text-gray-600 p-1"
            >
              <X className="w-3 h-3" />
            </Button>
          </div>
        </div>

        {/* 상세 내용 (펼친 상태) */}
        {isExpanded && (
          <CardContent className="p-6">
            <div className="space-y-6">
              {/* R1 추천 기능 */}
              {r1Recommendations.length > 0 && (
                <div>
                  <div className="flex items-center space-x-2 mb-4">
                    <Star className="w-5 h-5 text-red-500" />
                    <h4 className="font-bold text-lg text-gray-900">필수 추천 기능</h4>
                    <Badge variant="destructive">중요</Badge>
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    {r1Recommendations.map((feature) => (
                      <div
                        key={feature.id}
                        className={`
                          p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md
                          ${selectedR1.has(feature.id)
                            ? 'border-red-200 bg-red-50 shadow-sm ring-2 ring-red-100'
                            : 'border-gray-200 hover:border-red-300 hover:bg-red-50/50'
                          }
                        `}
                        onClick={() => handleToggle(feature, !selectedR1.has(feature.id), 'R1')}
                      >
                        <div className="flex items-start justify-between mb-3">
                          <div className="flex items-center space-x-2">
                            <span className="text-2xl">{feature.icon}</span>
                            {feature.is_premium && (
                              <Badge variant="secondary" className="bg-yellow-100 text-yellow-800 border-yellow-200 text-xs">
                                <Sparkles className="w-3 h-3 mr-1" />
                                프리미엄
                              </Badge>
                            )}
                          </div>
                          <div className={`
                            w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                            ${selectedR1.has(feature.id)
                              ? 'bg-red-500 border-red-500'
                              : 'border-gray-300'
                            }
                          `}>
                            {selectedR1.has(feature.id) && (
                              <Check className="w-3 h-3 text-white" />
                            )}
                          </div>
                        </div>
                        <h5 className="font-semibold text-gray-900 mb-2 line-clamp-1">
                          {feature.name}
                        </h5>
                        {feature.description && (
                          <p className="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                            {feature.description}
                          </p>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* R2 추천 기능 */}
              {r2Recommendations.length > 0 && (
                <div>
                  <div className="flex items-center space-x-2 mb-4">
                    <Plus className="w-5 h-5 text-blue-500" />
                    <h4 className="font-bold text-lg text-gray-900">추가 제안 기능</h4>
                    <Badge variant="secondary" className="bg-blue-100 text-blue-800">
                      선택사항
                    </Badge>
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    {r2Recommendations.map((feature) => (
                      <div
                        key={feature.id}
                        className={`
                          p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md
                          ${selectedR2.has(feature.id)
                            ? 'border-blue-200 bg-blue-50 shadow-sm ring-2 ring-blue-100'
                            : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50/50'
                          }
                        `}
                        onClick={() => handleToggle(feature, !selectedR2.has(feature.id), 'R2')}
                      >
                        <div className="flex items-start justify-between mb-3">
                          <div className="flex items-center space-x-2">
                            <span className="text-2xl">{feature.icon}</span>
                            {feature.is_premium && (
                              <Badge variant="secondary" className="bg-yellow-100 text-yellow-800 border-yellow-200 text-xs">
                                <Sparkles className="w-3 h-3 mr-1" />
                                프리미엄
                              </Badge>
                            )}
                          </div>
                          <div className={`
                            w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                            ${selectedR2.has(feature.id)
                              ? 'bg-blue-500 border-blue-500'
                              : 'border-gray-300'
                            }
                          `}>
                            {selectedR2.has(feature.id) && (
                              <Check className="w-3 h-3 text-white" />
                            )}
                          </div>
                        </div>
                        <h5 className="font-semibold text-gray-900 mb-2 line-clamp-1">
                          {feature.name}
                        </h5>
                        {feature.description && (
                          <p className="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                            {feature.description}
                          </p>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>

            {/* 하단 액션 */}
            <div className="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
              <div className="flex items-center space-x-4">
                <span className="text-sm text-gray-600">
                  💡 팁: 각 카드를 클릭해서 선택/해제할 수 있습니다
                </span>
              </div>
              <div className="flex items-center space-x-3">
                <span className="text-sm font-medium text-gray-700">
                  총 {selectedR1.size + selectedR2.size}개 선택됨
                </span>
                <Button
                  onClick={onClose}
                  className="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white"
                >
                  선택 완료
                </Button>
              </div>
            </div>
          </CardContent>
        )}
      </Card>
    </div>
  );
}