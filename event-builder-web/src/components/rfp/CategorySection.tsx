import { Feature } from '@/types/rfp'
import FeatureCard from '@/components/common/FeatureCard'
import { useTranslation } from '@/lib/i18n'
import { Badge } from '@/components/ui/badge'
import { CheckCircle2, Layers } from 'lucide-react'

interface CategorySectionProps {
  categoryName: string
  features: Feature[]
  selectedFeatureIds: number[]
  onFeatureSelect: (featureId: number) => void
}

// 카테고리 이름을 스네이크 케이스 번역 키로 변환하는 함수
function normalizeToSnakeCase(categoryName: string): string {
  return categoryName
    .toLowerCase()
    .replace(/[\s·]/g, '_') // 공백과 가운데점을 언더스코어로 변환
    .replace(/[^\w가-힣]/g, '_') // 기타 특수문자도 언더스코어로 변환
    .replace(/_+/g, '_') // 연속된 언더스코어를 하나로 통합
    .replace(/^_|_$/g, ''); // 앞뒤 언더스코어 제거
}

export default function CategorySection({
  categoryName,
  features,
  selectedFeatureIds,
  onFeatureSelect,
}: CategorySectionProps) {
  const t = useTranslation();
  const translatedKey = normalizeToSnakeCase(categoryName);
  const displayCategoryName = t(`featureCategories.${translatedKey}`);
  const selectedCount = features.filter(feature => selectedFeatureIds.includes(feature.id)).length;
  const totalCount = features.length;

  // 카테고리별 아이콘 매핑 (기본값 포함)
  const getCategoryIcon = (categoryName: string) => {
    const iconMap: { [key: string]: string } = {
      '행사 기획': '🎯',
      '장소·공간': '🏢',
      '음식·케이터링': '🍽️',
      '인력·스태프': '👥',
      '홍보·마케팅': '📢',
      '영상·방송': '📹',
      '음향·조명': '🎵',
      '보안·안전': '🛡️',
      '운송·물류': '🚚',
      '기타 서비스': '⚙️',
    };
    return iconMap[categoryName] || '📋';
  };

  const categoryIcon = getCategoryIcon(displayCategoryName);

  return (
    <div className="p-6">
      {/* 카테고리 헤더 - 개선된 디자인 */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center space-x-4">
          {/* 카테고리 아이콘 */}
          <div className="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl shadow-sm">
            <span className="text-2xl">{categoryIcon}</span>
          </div>
          
          {/* 카테고리 정보 */}
          <div>
            <div className="flex items-center space-x-2 mb-1">
              <h2 className="text-xl font-bold text-gray-900">
                {displayCategoryName}
              </h2>
              <Layers className="w-4 h-4 text-gray-400" />
            </div>
            <div className="flex items-center space-x-2">
              <p className="text-sm text-gray-600">
                총 {totalCount}개 기능
              </p>
              {selectedCount > 0 && (
                <>
                  <span className="text-gray-400">•</span>
                  <div className="flex items-center space-x-1">
                    <CheckCircle2 className="w-4 h-4 text-green-500" />
                    <span className="text-sm font-medium text-green-600">
                      {selectedCount}개 선택됨
                    </span>
                  </div>
                </>
              )}
            </div>
          </div>
        </div>
        
        {/* 선택 상태 인디케이터 */}
        <div className="flex items-center space-x-3">
          {/* 진행률 표시 */}
          <div className="flex items-center space-x-2">
            <div className="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
              <div 
                className="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-300"
                style={{ width: `${(selectedCount / totalCount) * 100}%` }}
              />
            </div>
            <span className="text-sm font-medium text-gray-600">
              {Math.round((selectedCount / totalCount) * 100)}%
            </span>
          </div>
          
          {/* 선택 개수 배지 */}
          {selectedCount > 0 && (
            <Badge 
              variant="secondary" 
              className="bg-green-100 text-green-800 border-green-200 px-3 py-1"
            >
              {selectedCount}/{totalCount}
            </Badge>
          )}
        </div>
      </div>

      {/* 기능 카드 그리드 - 개선된 레이아웃 */}
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        {(features || []).map((feature) => (
          <FeatureCard
            key={feature.id}
            feature={feature}
            isSelected={selectedFeatureIds.includes(feature.id)}
            onSelect={onFeatureSelect}
            displayMode="detailed"
          />
        ))}
      </div>

      {/* 빈 상태 */}
      {features.length === 0 && (
        <div className="text-center py-8">
          <div className="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <span className="text-2xl text-gray-400">📋</span>
          </div>
          <p className="text-gray-500 text-sm">이 카테고리에는 아직 기능이 없습니다.</p>
        </div>
      )}
    </div>
  )
}