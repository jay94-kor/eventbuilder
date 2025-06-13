'use client'

import React from 'react'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import { useTranslation } from '@/lib/i18n'
import { AlertCircle, Clock, Calendar, Settings, User, Building2 } from 'lucide-react'

interface GlobalConfig {
  global_prepare_deadline?: string
  global_delivery_date?: string
  global_internal_resource?: boolean
  global_internal_resource_person?: string
  global_feature_scope?: 'all' | 'by_zone'
  global_feature_zones?: string[]
}

interface GlobalConfigFormProps {
  config: GlobalConfig
  onChange: (config: GlobalConfig) => void
  eventZones?: { name: string; type: string; quantity: number }[]
}

export default function GlobalConfigForm({ 
  config, 
  onChange, 
  eventZones = [] 
}: GlobalConfigFormProps) {
  const t = useTranslation()

  const handleChange = (key: keyof GlobalConfig, value: any) => {
    const newConfig = { ...config, [key]: value }
    
    // 내부 리소스가 false로 변경되면 담당자 정보 제거
    if (key === 'global_internal_resource' && !value) {
      delete newConfig.global_internal_resource_person
    }
    
    // 전체 일괄이 선택되면 존 선택 제거
    if (key === 'global_feature_scope' && value === 'all') {
      delete newConfig.global_feature_zones
    }
    
    onChange(newConfig)
  }

  return (
    <Card className="border-0 shadow-xl bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 overflow-hidden">
      <CardHeader className="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
        <div className="flex items-center space-x-3">
          <div className="p-2 bg-white/20 rounded-lg">
            <Settings className="w-6 h-6" />
          </div>
          <div>
            <CardTitle className="text-xl font-bold">🌟 전역 기본 설정</CardTitle>
            <p className="text-blue-100 text-sm mt-1">
              모든 기능에 공통으로 적용될 기본값을 설정합니다. 개별 기능에서 필요시 변경 가능합니다.
            </p>
          </div>
        </div>
      </CardHeader>
      
      <CardContent className="p-6">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          
          {/* 일정 관리 그룹 */}
          <div className="space-y-4">
            <div className="flex items-center space-x-2 mb-4">
              <Calendar className="w-5 h-5 text-purple-600" />
              <h3 className="font-bold text-lg text-purple-900">📅 일정 관리</h3>
            </div>
            
            <div className="space-y-4">
              <div>
                <Label className="text-sm font-semibold text-gray-700 mb-2 block flex items-center">
                  <Clock className="w-4 h-4 mr-2 text-purple-600" />
                  기본 준비 마감시점
                </Label>
                <Input
                  type="datetime-local"
                  value={config.global_prepare_deadline || ''}
                  onChange={(e) => handleChange('global_prepare_deadline', e.target.value)}
                  className="w-full border-purple-200 focus:border-purple-400 focus:ring-purple-400/20"
                />
                <p className="text-xs text-gray-500 mt-1">각 기능의 준비 완료 기본 마감일</p>
              </div>
              
              <div>
                <Label className="text-sm font-semibold text-gray-700 mb-2 block flex items-center">
                  <Calendar className="w-4 h-4 mr-2 text-purple-600" />
                  기본 납품 완료시점
                </Label>
                <Input
                  type="datetime-local"
                  value={config.global_delivery_date || ''}
                  onChange={(e) => handleChange('global_delivery_date', e.target.value)}
                  className="w-full border-purple-200 focus:border-purple-400 focus:ring-purple-400/20"
                />
                <p className="text-xs text-gray-500 mt-1">각 기능의 납품·설치 완료 기본일</p>
              </div>
            </div>
          </div>

          {/* 리소스 관리 그룹 */}
          <div className="space-y-4">
            <div className="flex items-center space-x-2 mb-4">
              <User className="w-5 h-5 text-emerald-600" />
              <h3 className="font-bold text-lg text-emerald-900">👥 리소스 관리</h3>
            </div>
            
            <div className="space-y-4">
              <div className="flex items-center justify-between p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                <div className="flex items-center space-x-3">
                  <Building2 className="w-5 h-5 text-emerald-600" />
                  <div>
                    <Label className="text-sm font-semibold text-emerald-700">기본 내부 리소스 사용</Label>
                    <p className="text-xs text-emerald-600">내부 자원 활용 여부</p>
                  </div>
                </div>
                <Checkbox
                  checked={config.global_internal_resource || false}
                  onCheckedChange={(checked) => handleChange('global_internal_resource', checked)}
                />
              </div>
              
              {config.global_internal_resource && (
                <div>
                  <Label className="text-sm font-semibold text-gray-700 mb-2 block flex items-center">
                    <User className="w-4 h-4 mr-2 text-emerald-600" />
                    기본 내부 담당자
                  </Label>
                  <Input
                    type="text"
                    value={config.global_internal_resource_person || ''}
                    onChange={(e) => handleChange('global_internal_resource_person', e.target.value)}
                    placeholder="예) 김행사 팀장"
                    className="w-full border-emerald-200 focus:border-emerald-400 focus:ring-emerald-400/20"
                  />
                  <p className="text-xs text-gray-500 mt-1">내부 리소스 기본 담당자명</p>
                </div>
              )}
            </div>
          </div>

          {/* 적용 범위 그룹 */}
          <div className="space-y-4">
            <div className="flex items-center space-x-2 mb-4">
              <Building2 className="w-5 h-5 text-blue-600" />
              <h3 className="font-bold text-lg text-blue-900">🎯 적용 범위</h3>
            </div>
            
            <div className="space-y-4">
              <div>
                <Label className="text-sm font-semibold text-gray-700 mb-2 block">기본 적용 범위</Label>
                <div className="space-y-2">
                  <label className="flex items-center space-x-3 p-3 bg-white rounded-lg border border-blue-200 hover:bg-blue-50 cursor-pointer transition-colors">
                    <input
                      type="radio"
                      name="global_scope"
                      value="all"
                      checked={config.global_feature_scope === 'all'}
                      onChange={() => handleChange('global_feature_scope', 'all')}
                      className="text-blue-600 border-blue-300 focus:ring-blue-400/20"
                    />
                    <span className="text-sm font-medium text-gray-700">전체 일괄 적용</span>
                  </label>
                  <label className="flex items-center space-x-3 p-3 bg-white rounded-lg border border-blue-200 hover:bg-blue-50 cursor-pointer transition-colors">
                    <input
                      type="radio"
                      name="global_scope"
                      value="by_zone"
                      checked={config.global_feature_scope === 'by_zone'}
                      onChange={() => handleChange('global_feature_scope', 'by_zone')}
                      className="text-blue-600 border-blue-300 focus:ring-blue-400/20"
                    />
                    <span className="text-sm font-medium text-gray-700">존별 개별 설정</span>
                  </label>
                </div>
              </div>

              {config.global_feature_scope === 'by_zone' && eventZones.length > 0 && (
                <div>
                  <Label className="text-sm font-semibold text-gray-700 mb-2 block">기본 대상 존</Label>
                  <div className="max-h-32 overflow-y-auto space-y-1 p-2 bg-white rounded-lg border border-blue-200">
                    {eventZones.map((zone) => (
                      <label key={zone.name} className="flex items-center space-x-2 p-2 hover:bg-blue-50 rounded cursor-pointer">
                        <input
                          type="checkbox"
                          checked={config.global_feature_zones?.includes(zone.name) || false}
                          onChange={(e) => {
                            const currentZones = config.global_feature_zones || []
                            const newZones = e.target.checked
                              ? [...currentZones, zone.name]
                              : currentZones.filter(z => z !== zone.name)
                            handleChange('global_feature_zones', newZones)
                          }}
                          className="text-blue-600 border-blue-300 focus:ring-blue-400/20"
                        />
                        <span className="text-sm text-gray-700">{zone.name}</span>
                        <Badge variant="secondary" className="text-xs">
                          {zone.type}
                        </Badge>
                      </label>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* 도움말 */}
        <div className="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
          <div className="flex items-start space-x-3">
            <AlertCircle className="w-5 h-5 text-amber-600 mt-0.5" />
            <div>
              <h4 className="font-semibold text-amber-800 mb-1">💡 사용 팁</h4>
              <ul className="text-sm text-amber-700 space-y-1">
                <li>• 여기서 설정한 값들이 모든 기능의 기본값으로 적용됩니다</li>
                <li>• 개별 기능에서 필요시 다른 값으로 변경할 수 있습니다</li>
                <li>• 변경된 항목은 개별 기능 카드에서 하이라이트 표시됩니다</li>
              </ul>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  )
} 