import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Package, AlertCircle } from 'lucide-react';
import { ElementDefinition, SpecVariant } from '@/lib/types';

interface QuantityControllerProps {
  elementDefinition: ElementDefinition;
  totalQuantity: number;
  baseQuantity: number;
  useVariants: boolean;
  variants: SpecVariant[];
  onChange: (data: {
    total_quantity: number;
    base_quantity: number;
    use_variants: boolean;
  }) => void;
}

export default function QuantityController({
  elementDefinition,
  totalQuantity,
  baseQuantity,
  useVariants,
  variants,
  onChange
}: QuantityControllerProps) {
  const quantityConfig = elementDefinition.quantity_config;
  const unit = quantityConfig?.unit || '개';
  const minQuantity = quantityConfig?.min || 1;
  const maxQuantity = quantityConfig?.max || 10;
  const allowVariants = quantityConfig?.allow_variants || false;

  // 변형들의 총 수량 계산
  const variantTotalQuantity = variants.reduce((sum, variant) => sum + variant.quantity, 0);
  const calculatedTotal = baseQuantity + variantTotalQuantity;
  
  // 수량 검증
  const isQuantityValid = calculatedTotal === totalQuantity;
  const isInRange = totalQuantity >= minQuantity && totalQuantity <= maxQuantity;

  const handleTotalQuantityChange = (value: number) => {
    const newTotal = Math.max(minQuantity, Math.min(maxQuantity, value));
    const newBase = useVariants ? Math.max(0, newTotal - variantTotalQuantity) : newTotal;
    
    onChange({
      total_quantity: newTotal,
      base_quantity: newBase,
      use_variants: useVariants
    });
  };

  const handleBaseQuantityChange = (value: number) => {
    const newBase = Math.max(0, value);
    const newTotal = newBase + variantTotalQuantity;
    
    onChange({
      total_quantity: newTotal,
      base_quantity: newBase,
      use_variants: useVariants
    });
  };

  const handleVariantsToggle = (enabled: boolean) => {
    if (!enabled) {
      // 변형 사용 해제 시 기본 수량을 총 수량과 동일하게
      onChange({
        total_quantity: totalQuantity,
        base_quantity: totalQuantity,
        use_variants: false
      });
    } else {
      // 변형 사용 활성화 시 기본 수량 유지
      onChange({
        total_quantity: totalQuantity,
        base_quantity: baseQuantity,
        use_variants: true
      });
    }
  };

  return (
    <Card className="bg-slate-50 border-slate-200">
      <CardContent className="p-4">
        <div className="flex items-center gap-2 mb-4">
          <Package className="w-4 h-4 text-slate-600" />
          <h4 className="font-medium text-slate-900">수량 설정</h4>
          <Badge variant="outline" className="text-xs">
            {minQuantity}-{maxQuantity}{unit}
          </Badge>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {/* 총 수량 */}
          <div className="space-y-2">
            <Label htmlFor="total-quantity" className="text-sm font-medium">
              총 수량
            </Label>
            <div className="relative">
              <Input
                id="total-quantity"
                type="number"
                min={minQuantity}
                max={maxQuantity}
                value={totalQuantity}
                onChange={(e) => handleTotalQuantityChange(parseInt(e.target.value) || minQuantity)}
                className={`pr-12 ${!isInRange ? 'border-red-300 focus:border-red-500' : ''}`}
              />
              <span className="absolute right-3 top-1/2 transform -translate-y-1/2 text-sm text-gray-500">
                {unit}
              </span>
            </div>
            {!isInRange && (
              <p className="text-xs text-red-600 flex items-center gap-1">
                <AlertCircle className="w-3 h-3" />
                {minQuantity}-{maxQuantity}{unit} 범위 내에서 입력하세요
              </p>
            )}
          </div>

          {/* 기본 스펙 수량 */}
          <div className="space-y-2">
            <Label htmlFor="base-quantity" className="text-sm font-medium">
              기본 스펙 적용
            </Label>
            <div className="relative">
              <Input
                id="base-quantity"
                type="number"
                min={0}
                max={totalQuantity}
                value={baseQuantity}
                onChange={(e) => handleBaseQuantityChange(parseInt(e.target.value) || 0)}
                disabled={!useVariants}
                className={`pr-12 ${useVariants ? '' : 'bg-gray-100'}`}
              />
              <span className="absolute right-3 top-1/2 transform -translate-y-1/2 text-sm text-gray-500">
                {unit}
              </span>
            </div>
            {!useVariants && (
              <p className="text-xs text-gray-600">
                변형 사용 시 조정 가능
              </p>
            )}
          </div>

          {/* 변형 사용 여부 */}
          {allowVariants && (
            <div className="space-y-2">
              <Label className="text-sm font-medium">스펙 변형</Label>
              <div className="flex items-center space-x-2 py-2">
                <Switch
                  checked={useVariants}
                  onCheckedChange={handleVariantsToggle}
                  id="use-variants"
                />
                <Label htmlFor="use-variants" className="text-sm cursor-pointer">
                  변형 허용
                </Label>
              </div>
              {useVariants && variants.length > 0 && (
                <p className="text-xs text-blue-600">
                  변형 {variants.length}개 (총 {variantTotalQuantity}{unit})
                </p>
              )}
            </div>
          )}
        </div>

        {/* 수량 검증 알림 */}
        {useVariants && !isQuantityValid && (
          <div className="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <div className="flex items-center gap-2 text-amber-800">
              <AlertCircle className="w-4 h-4" />
              <span className="text-sm font-medium">수량 불일치</span>
            </div>
            <p className="text-xs text-amber-700 mt-1">
              기본 스펙 ({baseQuantity}{unit}) + 변형 ({variantTotalQuantity}{unit}) = {calculatedTotal}{unit} ≠ 총 수량 ({totalQuantity}{unit})
            </p>
          </div>
        )}

        {/* 요약 정보 */}
        <div className="mt-4 pt-4 border-t border-slate-200">
          <div className="grid grid-cols-3 gap-4 text-center">
            <div>
              <p className="text-lg font-semibold text-slate-900">{totalQuantity}</p>
              <p className="text-xs text-slate-600">총 수량</p>
            </div>
            <div>
              <p className="text-lg font-semibold text-blue-600">{baseQuantity}</p>
              <p className="text-xs text-slate-600">기본 스펙</p>
            </div>
            <div>
              <p className="text-lg font-semibold text-green-600">{variantTotalQuantity}</p>
              <p className="text-xs text-slate-600">변형</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
} 