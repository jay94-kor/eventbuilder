import Link from 'next/link';
import { fetchRfpDetails, Rfp } from '@/lib/api';
import { notFound } from 'next/navigation';
import { getTranslation, Language } from '@/lib/i18n';
import { cookies } from 'next/headers';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  CalendarIcon,
  CheckCircleIcon,
  ChevronLeftIcon,
  ClipboardIcon,
  CurrencyDollarIcon,
  PencilIcon,
  ShareIcon,
  ArrowDownTrayIcon,
} from '@heroicons/react/24/outline';

interface RfpDetailPageProps {
  params: Promise<{
    id: string;
  }>;
}

// Feature.config.fields 내부 객체의 타입 정의
interface FeatureConfigField {
  name: string;
  key: string;
  type: string;
  unit?: string;
  options?: Array<{ label:string; value: string }>;
}

// 카테고리별로 선택된 features를 그룹화하는 함수
function groupSelectionsByCategory(rfp: Rfp) {
  const categorizedSelections: Record<
    string,
    Array<{
      featureName: string;
      details: Record<string, unknown>;
      featureConfigFields: FeatureConfigField[] | undefined;
    }>
  > = {};

  rfp.selections?.forEach((selection) => {
    if (selection && selection.feature && selection.feature.category) {
      const categoryName = selection.feature.category.name;
      if (!categorizedSelections[categoryName]) {
        categorizedSelections[categoryName] = [];
      }
      categorizedSelections[categoryName].push({
        featureName: selection.feature.name,
        details: selection.details || {},
        featureConfigFields: selection.feature.config?.fields || [],
      });
    }
  });
  return categorizedSelections;
}

// details 객체를 사용자 친화적인 문자열로 변환하는 함수
function formatDetails(
  details: Record<string, unknown>,
  featureConfigFields: FeatureConfigField[] | undefined,
  t: (key: string) => string
): string[] {
  const formatted: string[] = [];
  if (!featureConfigFields) return formatted;

  featureConfigFields.forEach(
    (field) => {
      const value = details[field.key];
      if (value !== null && value !== undefined && value !== '') {
        let formattedValue: string;
        if (typeof value === 'boolean') {
          formattedValue = value ? t('common.yes') : t('common.no');
        } else if (Array.isArray(value)) {
          const selectedLabels = value.map((v) => {
            const option = field.options?.find((opt) => opt.value === v);
            return option ? option.label : v;
          });
          formattedValue = selectedLabels.join(', ');
        } else {
          const option = field.options?.find((opt) => opt.value === value);
          formattedValue = option ? option.label : String(value);
        }
        const unit = field.unit ? ` ${field.unit}` : '';
        formatted.push(`${field.name}: ${formattedValue}${unit}`);
      }
    }
  );
  return formatted;
}

// 날짜 포맷팅 함수
function formatDate(dateString: string | undefined, t: (key:string) => string) {
  if (!dateString) return t('rfp_detail.date_unspecified');
  return new Date(dateString).toLocaleDateString('ko-KR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

// 상태 텍스트 변환 함수
function getStatusText(status: string, t: (key: string) => string) {
  const statusMap: Record<string, string> = {
    draft: t('rfp_detail.status_draft'),
    completed: t('rfp_detail.status_completed'),
    archived: t('rfp_detail.status_archived'),
  };
  return statusMap[status] || t('rfp_detail.status_unknown');
}

// 상태 색상 클래스 반환 함수
function getStatusColor(status: string) {
  const colorMap: Record<string, string> = {
    draft: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    completed: 'bg-green-100 text-green-800 border-green-200',
    archived: 'bg-gray-100 text-gray-800 border-gray-200',
  };
  return colorMap[status] || 'bg-gray-100 text-gray-800 border-gray-200';
}

export default async function RfpDetailPage({ params }: RfpDetailPageProps) {
  // 1. Await a-sync operations first
  const cookieStore = await cookies();
  const { id } = await params; // Now this is safe

  // 2. Get token and translation function
  const token = cookieStore.get('auth-token')?.value || null;
  const lang = (cookieStore.get('NEXT_LOCALE')?.value || 'ko') as Language;
  const t = (key: string) => getTranslation(lang, key);
  
  let rfp: Rfp;

  // 3. Fetch data with token
  try {
    // Pass both id and token to the fetching function
    const response = await fetchRfpDetails(id, token);
    if (!response.success) {
      console.error('Failed to fetch RFP details:', response.message);
      // If unauthenticated or not found, show 404
      if (response.message?.includes('Unauthenticated') || response.message?.includes('not found')) {
         notFound();
      }
      // For other errors, we could show a generic error message, but notFound is simple
      notFound();
    }
    rfp = response.data;
  } catch (error) {
    console.error('Error fetching RFP details:', error);
    notFound();
  }

  const categorizedSelections = groupSelectionsByCategory(rfp);

  return (
    <div className="min-h-screen bg-gray-50">
      <main className="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div className="mb-6">
          <Link href="/dashboard" className="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <ChevronLeftIcon className="w-5 h-5 mr-2" />
            {t('rfp_detail.back_to_dashboard')}
          </Link>
        </div>

        <header className="mb-8">
          <div className="flex flex-col md:flex-row justify-between items-start gap-4">
            <div>
              <div className="flex items-center gap-3 mb-2">
                <Badge className={getStatusColor(rfp.status)}>
                  {getStatusText(rfp.status, t)}
                </Badge>
              </div>
              <h1 className="text-3xl font-bold text-gray-900 tracking-tight">{rfp.title}</h1>
            </div>
            <div className="flex-shrink-0 flex items-center gap-2">
              <Link href={`/rfp/${rfp.id}/edit`}>
                <Button variant="outline" className="inline-flex items-center">
                  <PencilIcon className="w-4 h-4 mr-2" />
                  {t('common.edit')}
                </Button>
              </Link>
              <Button variant="outline" className="inline-flex items-center">
                <ShareIcon className="w-4 h-4 mr-2" />
                {t('common.share')}
              </Button>
              <Button className="inline-flex items-center">
                <ArrowDownTrayIcon className="w-4 h-4 mr-2" />
                {t('common.export_pdf')}
              </Button>
            </div>
          </div>
        </header>

        <Card className="mb-8 bg-white shadow-sm">
          <CardContent className="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
            <InfoItem icon={CalendarIcon} label={t('rfp_detail.event_date_label')} value={formatDate(rfp.event_date, t)} />
            <InfoItem icon={ClipboardIcon} label={t('rfp_detail.selected_items_label')} value={`${rfp.selections?.length || 0}${t('rfp_detail.items_suffix')}`} />
            <InfoItem icon={CurrencyDollarIcon} label="총 예산" value="₩12,000,000" />
            <InfoItem icon={CheckCircleIcon} label="마지막 업데이트" value={formatDate(rfp.updated_at, t)} />
          </CardContent>
        </Card>
        
        <div className="space-y-6">
          {Object.entries(categorizedSelections).map(([category, selections]) => (
            <Card key={category} className="bg-white shadow-sm">
              <CardHeader>
                <CardTitle className="text-xl font-semibold text-gray-800">{category}</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="divide-y divide-gray-200">
                  {selections.map((selection, index) => (
                    <div key={index} className="py-4">
                      <h4 className="font-medium text-gray-700">{selection.featureName}</h4>
                      <ul className="mt-2 space-y-1 pl-1">
                        {formatDetails(selection.details, selection.featureConfigFields, t).map((detail, i) => (
                           <li key={i} className="text-sm text-gray-500 flex items-start">
                             <span className="w-1 h-1 bg-gray-400 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                             {detail}
                           </li>
                        ))}
                      </ul>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {Object.keys(categorizedSelections).length === 0 && (
          <Card className="text-center py-12">
            <CardContent>
              <ClipboardIcon className="mx-auto h-12 w-12 text-gray-400" />
              <h3 className="mt-2 text-sm font-medium text-gray-900">{t('rfp_detail.no_selected_items_title')}</h3>
              <p className="mt-1 text-sm text-gray-500">{t('rfp_detail.no_selected_items_description')}</p>
              <div className="mt-6">
                <Link href={`/rfp/${rfp.id}/edit`}>
                  <Button>{t('common.add_items')}</Button>
                </Link>
              </div>
            </CardContent>
          </Card>
        )}
      </main>
    </div>
  );
}

const InfoItem = ({ icon: Icon, label, value }: { icon: React.ElementType; label: string; value: string }) => (
  <div className="flex items-start">
    <Icon className="w-6 h-6 text-gray-400 mr-3 mt-1 flex-shrink-0" />
    <div>
      <dt className="text-sm font-medium text-gray-500">{label}</dt>
      <dd className="mt-1 text-lg font-semibold text-gray-900">{value}</dd>
    </div>
  </div>
);

// ... (기존 helper 함수들은 여기에 위치할 수 있음)