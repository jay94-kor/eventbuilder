'use client'

import { useState, useEffect, useCallback } from 'react'
import { useRouter } from 'next/navigation'
import { useRfpStore } from '@/stores/rfpStore'
import { useTranslation } from '@/lib/i18n'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

export default function BasicInfoPage() {
  const t = useTranslation()
  const router = useRouter()
  const { rfpBasicInfo, updateRfpBasicInfo } = useRfpStore()

  const [rfpTitle, setRfpTitle] = useState(rfpBasicInfo.title || '')
  const [eventDate, setEventDate] = useState(rfpBasicInfo.event_date || '')
  const [expectedAttendees, setExpectedAttendees] = useState(rfpBasicInfo.expected_attendees || '')
  const [totalBudget, setTotalBudget] = useState(rfpBasicInfo.total_budget || '')
  const [isTotalBudgetUndecided, setIsTotalBudgetUndecided] = useState(rfpBasicInfo.is_total_budget_undecided || false)
  const [rfpDescription, setRfpDescription] = useState(rfpBasicInfo.description || '')

  // 스토어 업데이트를 위한 메모이제이션된 함수
  const updateBasicInfo = useCallback(() => {
    updateRfpBasicInfo({
      title: rfpTitle,
      event_date: eventDate,
      expected_attendees: expectedAttendees,
      total_budget: totalBudget,
      is_total_budget_undecided: isTotalBudgetUndecided,
      description: rfpDescription,
    })
  }, [rfpTitle, eventDate, expectedAttendees, totalBudget, isTotalBudgetUndecided, rfpDescription, updateRfpBasicInfo])

  useEffect(() => {
    updateBasicInfo()
  }, [updateBasicInfo])

  const handleNext = () => {
    if (!rfpTitle.trim()) {
      alert(t('rfp_basic_info.title_required_alert'))
      return
    }
    // 스토어에 최신 정보 저장 (useEffect에 의해 이미 저장되지만, 명시적으로 호출)
    updateRfpBasicInfo({
      title: rfpTitle,
      event_date: eventDate,
      expected_attendees: expectedAttendees,
      total_budget: totalBudget,
      is_total_budget_undecided: isTotalBudgetUndecided,
      description: rfpDescription,
    });
    console.log('Navigating to /rfp/create with basic info:', { rfpTitle, eventDate, expectedAttendees, totalBudget, isTotalBudgetUndecided, rfpDescription });
    router.push('/rfp/create'); // 다음 단계인 기능 선택 페이지로 이동
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      <div className="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {/* 페이지 헤더 */}
        <div className="text-center mb-10">
          <h1 className="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-4">
            {t('rfp_basic_info.title')}
          </h1>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            {t('rfp_basic_info.subtitle')}
          </p>
        </div>

        {/* 진행 단계 표시 - 개선된 버전 */}
        <div className="mb-12">
          <div className="flex items-center justify-center">
            <div className="flex items-center">
              <div className="flex items-center text-blue-600">
                <div className="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl text-white text-sm font-bold shadow-lg">
                  1
                </div>
                <span className="ml-3 text-sm font-semibold text-gray-900">{t('rfp_basic_info.step1_title')}</span>
              </div>
              <div className="flex-1 mx-6 h-1 bg-gradient-to-r from-blue-300 to-gray-200 rounded-full w-20"></div>
              <div className="flex items-center text-gray-400">
                <div className="flex items-center justify-center w-12 h-12 bg-gray-200 rounded-2xl text-gray-500 text-sm font-bold">
                  2
                </div>
                <span className="ml-3 text-sm font-semibold">{t('rfp_basic_info.step2_title')}</span>
              </div>
              <div className="flex-1 mx-6 h-1 bg-gray-200 rounded-full w-20"></div>
              <div className="flex items-center text-gray-400">
                <div className="flex items-center justify-center w-12 h-12 bg-gray-200 rounded-2xl text-gray-500 text-sm font-bold">
                  3
                </div>
                <span className="ml-3 text-sm font-semibold">{t('rfp_basic_info.step3_title')}</span>
              </div>
              <div className="flex-1 mx-6 h-1 bg-gray-200 rounded-full w-20"></div>
              <div className="flex items-center text-gray-400">
                <div className="flex items-center justify-center w-12 h-12 bg-gray-200 rounded-2xl text-gray-500 text-sm font-bold">
                  4
                </div>
                <span className="ml-3 text-sm font-semibold">{t('rfp_basic_info.step4_title')}</span>
              </div>
            </div>
          </div>
        </div>

        {/* RFP 기본 정보 입력 폼 - 개선된 카드 */}
        <Card className="border-0 shadow-2xl bg-white/80 backdrop-blur-sm">
          <CardHeader className="text-center pb-8">
            <div className="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
              <span className="text-white text-2xl">📝</span>
            </div>
            <CardTitle className="text-2xl font-bold text-gray-900">{t('rfp_basic_info.section_title')}</CardTitle>
            <CardDescription className="text-gray-600">
              기본 정보를 입력하여 맞춤형 RFP 생성을 시작하세요
            </CardDescription>
          </CardHeader>
          
          <CardContent className="space-y-8">
            {/* RFP 제목 - 전체 너비 */}
            <div className="space-y-2">
              <Label htmlFor="rfpTitle" className="text-base font-semibold text-gray-700 flex items-center">
                <span className="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                {t('rfp_basic_info.rfp_title_label')}
              </Label>
              <Input
                id="rfpTitle"
                type="text"
                value={rfpTitle}
                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setRfpTitle(e.target.value)}
                placeholder={t('rfp_basic_info.rfp_title_placeholder')}
                required
                className="h-12 text-lg border-2 border-gray-200 focus:border-blue-500 transition-colors duration-200"
              />
            </div>

            {/* 날짜와 참석자 수 */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-2">
                <Label htmlFor="eventDate" className="text-base font-semibold text-gray-700 flex items-center">
                  <span className="text-blue-500 mr-2">📅</span>
                  {t('rfp_basic_info.event_date_label')}
                </Label>
                <Input
                  id="eventDate"
                  type="date"
                  value={eventDate}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEventDate(e.target.value)}
                  className="h-12 border-2 border-gray-200 focus:border-blue-500 transition-colors duration-200"
                />
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="expectedAttendees" className="text-base font-semibold text-gray-700 flex items-center">
                  <span className="text-green-500 mr-2">👥</span>
                  {t('rfp_basic_info.expected_attendees_label')}
                </Label>
                <Input
                  id="expectedAttendees"
                  type="number"
                  value={expectedAttendees}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) => setExpectedAttendees(e.target.value)}
                  placeholder={t('rfp_basic_info.expected_attendees_placeholder')}
                  className="h-12 border-2 border-gray-200 focus:border-blue-500 transition-colors duration-200"
                />
              </div>
            </div>

            {/* 예산 섹션 */}
            <div className="space-y-4">
              <Label htmlFor="totalBudget" className="text-base font-semibold text-gray-700 flex items-center">
                <span className="text-yellow-500 mr-2">💰</span>
                {t('rfp_basic_info.total_budget_label')}
              </Label>
              <Input
                id="totalBudget"
                type="number"
                value={totalBudget}
                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setTotalBudget(e.target.value)}
                placeholder={t('rfp_basic_info.total_budget_placeholder')}
                disabled={isTotalBudgetUndecided}
                className="h-12 border-2 border-gray-200 focus:border-blue-500 transition-colors duration-200 disabled:bg-gray-100"
              />
              <div className="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                <Checkbox
                  id="isTotalBudgetUndecided"
                  checked={isTotalBudgetUndecided}
                  onCheckedChange={(checked) => setIsTotalBudgetUndecided(!!checked)}
                  className="w-5 h-5"
                />
                <Label
                  htmlFor="isTotalBudgetUndecided"
                  className="ml-3 text-sm font-medium text-gray-700 cursor-pointer"
                >
                  {t('rfp_basic_info.total_budget_undecided_label')}
                </Label>
              </div>
            </div>

            {/* 설명 */}
            <div className="space-y-2">
              <Label htmlFor="rfpDescription" className="text-base font-semibold text-gray-700 flex items-center">
                <span className="text-purple-500 mr-2">📄</span>
                {t('rfp_basic_info.rfp_description_label')}
              </Label>
              <Textarea
                id="rfpDescription"
                value={rfpDescription}
                onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setRfpDescription(e.target.value)}
                placeholder={t('rfp_basic_info.rfp_description_placeholder')}
                rows={5}
                className="border-2 border-gray-200 focus:border-blue-500 transition-colors duration-200 resize-none"
              />
            </div>
          </CardContent>
        </Card>

        {/* 다음 단계 버튼 - 개선된 스타일 */}
        <div className="mt-12 text-center">
          <Button 
            onClick={handleNext} 
            className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 px-8 text-lg rounded-2xl shadow-xl transform hover:scale-105 transition-all duration-300 border-0"
          >
            <span className="mr-2">🚀</span>
            {t('rfp_basic_info.next_step_button')}
            <svg className="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </Button>
          
          <p className="mt-4 text-sm text-gray-500">
            다음 단계에서 행사 구성요소를 선택할 수 있습니다
          </p>
        </div>
      </div>
    </div>
  )
}