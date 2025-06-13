import { Feature } from '@/types/rfp';
import { useTranslation } from '@/lib/i18n';
import { Badge } from '@/components/ui/badge';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { CheckCircle2, Sparkles, DollarSign, Users, Settings, Zap } from 'lucide-react';

interface FeatureCardProps {
  feature: Partial<Feature>;
  isSelected: boolean;
  onSelect: (featureId: number) => void;
  displayMode?: 'simple' | 'detailed';
  disabled?: boolean;
}

export default function FeatureCard({
  feature,
  isSelected,
  onSelect,
  displayMode = 'detailed',
  disabled = false,
}: FeatureCardProps) {
  const t = useTranslation();
  const hasConfigFields = feature.config?.fields?.length > 0;

  if (displayMode === 'simple') {
    return (
      <div
        className={`
          p-4 border-2 rounded-xl transition-all duration-300 group cursor-pointer
          ${disabled 
            ? 'opacity-50 cursor-not-allowed border-gray-200 bg-gray-50'
            : 'hover:shadow-lg hover:-translate-y-0.5'
          }
          ${isSelected 
            ? 'border-blue-500 bg-blue-50 shadow-md ring-2 ring-blue-200 transform scale-[1.02]'
            : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50/50'
          }
          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
        `}
        onClick={() => !disabled && onSelect(feature.id!)}
        onKeyDown={(e) => {
          if (!disabled && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            onSelect(feature.id!);
          }
        }}
        tabIndex={disabled ? -1 : 0}
        role="button"
        aria-pressed={isSelected}
        aria-label={`${feature.name} ${isSelected ? '선택됨' : '선택하지 않음'} ${disabled ? '비활성화됨' : ''}`}
      >
        <div className="flex items-center gap-3">
          <div className="text-2xl flex-shrink-0 transform group-hover:scale-110 transition-transform duration-200">
            {feature.icon || '⚙️'}
          </div>
          <div className="flex-1 min-w-0">
            <h3 className={`font-semibold text-sm sm:text-base truncate transition-colors duration-200 ${
              isSelected ? 'text-blue-700' : 'text-gray-900 group-hover:text-blue-600'
            }`}>
              {feature.name}
            </h3>
            {feature.description && (
              <p className={`text-xs sm:text-sm mt-1 line-clamp-2 transition-colors duration-200 ${
                isSelected ? 'text-blue-600' : 'text-gray-600 group-hover:text-blue-500'
              }`}>
                {feature.description}
              </p>
            )}
          </div>
          {isSelected && (
            <div className="flex-shrink-0">
              <CheckCircle2 className="w-5 h-5 text-blue-500 animate-in fade-in-50 duration-200" />
            </div>
          )}
        </div>
      </div>
    );
  }

  return (
    <TooltipProvider>
      <Tooltip>
        <TooltipTrigger asChild>
          <div
            className={`
              relative flex flex-col items-center justify-center text-center p-4 
              border-2 rounded-xl cursor-pointer transition-all duration-300 ease-out
              aspect-square min-h-[120px] group focus-visible:ring-2 focus-visible:ring-blue-500/50 focus-visible:ring-offset-2 focus-visible:outline-none
              ${isSelected
                ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 shadow-lg ring-2 ring-blue-200 transform scale-105'
                : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-gradient-to-br hover:from-blue-50/50 hover:to-indigo-50/50 hover:shadow-md hover:transform hover:scale-102'
              }
            `}
            onClick={() => onSelect(feature.id!)}
          >
            {isSelected && (
              <div className="absolute -top-2 -right-2 z-10 animate-in zoom-in-50 duration-300">
                <div className="flex items-center justify-center w-6 h-6 bg-blue-500 rounded-full shadow-lg">
                  <CheckCircle2 className="w-4 h-4 text-white" />
                </div>
              </div>
            )}
            {hasConfigFields && (
              <div className="absolute top-2 left-2 animate-pulse">
                <div className="w-2 h-2 bg-orange-400 rounded-full shadow-sm"></div>
              </div>
            )}
            <div className={`
              flex items-center justify-center w-14 h-14 rounded-2xl mb-3 transition-all duration-300 ease-out transform
              ${isSelected 
                ? 'bg-blue-100 text-blue-600 shadow-sm scale-110' 
                : 'bg-gray-100 text-gray-600 group-hover:bg-blue-100 group-hover:text-blue-600 group-hover:scale-105'
              }
            `}>
              <span className="text-2xl">
                {feature.icon || '⚙️'}
              </span>
            </div>
            <h3 className={`
              font-semibold text-sm leading-tight transition-all duration-300 ease-out line-clamp-2 mb-2
              ${isSelected 
                ? 'text-blue-700' 
                : 'text-gray-900 group-hover:text-blue-600'
              }
            `}>
              {feature.name}
            </h3>
            <div className="absolute bottom-2 left-2 flex flex-wrap gap-1">
              {feature.is_premium && (
                <div className="flex items-center justify-center w-5 h-5 bg-yellow-100 rounded-full ring-1 ring-yellow-200 shadow-sm">
                  <Sparkles className="w-3 h-3 text-yellow-600" />
                </div>
              )}
              {feature.budget_allocation && (
                <div className="flex items-center justify-center w-5 h-5 bg-green-100 rounded-full ring-1 ring-green-200 shadow-sm">
                  <DollarSign className="w-3 h-3 text-green-600" />
                </div>
              )}
              {feature.internal_resource_flag && (
                <div className="flex items-center justify-center w-5 h-5 bg-blue-100 rounded-full ring-1 ring-blue-200 shadow-sm">
                  <Users className="w-3 h-3 text-blue-600" />
                </div>
              )}
            </div>
            <div className={`
              absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity duration-300
              ${!isSelected ? 'group-hover:opacity-100' : ''}
            `} />
          </div>
        </TooltipTrigger>
        <TooltipContent 
          side="top" 
          className="max-w-xs bg-white/95 backdrop-blur-md border border-gray-200 shadow-lg rounded-lg p-3"
        >
          <div className="space-y-3">
            <div className="flex items-center space-x-2">
              <span className="text-lg">{feature.icon || '⚙️'}</span>
              <p className="font-semibold text-gray-900">{feature.name}</p>
            </div>
            {feature.description && (
              <p className="text-sm text-gray-600 leading-relaxed">{feature.description}</p>
            )}
            <div className="flex flex-wrap gap-2">
              {feature.is_premium && (
                <Badge variant="secondary" className="text-xs bg-yellow-100 text-yellow-800 border-yellow-200 px-2 py-1">
                  <Sparkles className="w-3 h-3 mr-1" />
                  {t('features.premium')}
                </Badge>
              )}
              {feature.budget_allocation && (
                <Badge variant="secondary" className="text-xs bg-green-100 text-green-800 border-green-200 px-2 py-1">
                  <DollarSign className="w-3 h-3 mr-1" />
                  {t('features.budget_allocation')}
                </Badge>
              )}
              {feature.internal_resource_flag && (
                <Badge variant="secondary" className="text-xs bg-blue-100 text-blue-800 border-blue-200 px-2 py-1">
                  <Users className="w-3 h-3 mr-1" />
                  {t('features.internal_resource')}
                </Badge>
              )}
              {hasConfigFields && (
                <Badge variant="outline" className="text-xs border-orange-200 text-orange-700 px-2 py-1">
                  <Settings className="w-3 h-3 mr-1" />
                  {t('features.additional_info_required')}
                </Badge>
              )}
            </div>
          </div>
        </TooltipContent>
      </Tooltip>
    </TooltipProvider>
  );
}