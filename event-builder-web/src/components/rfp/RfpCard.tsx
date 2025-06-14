import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge'; // Badge 컴포넌트 임포트
import Link from 'next/link';
import { useTranslation } from '@/lib/i18n';
import { format } from 'date-fns';
import { CalendarIcon, ClockIcon } from 'lucide-react'; // 아이콘 임포트

interface RfpCardProps {
  rfp: {
    id: string;
    title: string;
    event_date: string | null;
    created_at: string;
    status: 'draft' | 'completed' | 'archived';
  };
}
 
export default function RfpCard({ rfp }: RfpCardProps) {
  const { t } = useTranslation();
 
  const formattedEventDate = rfp.event_date
    ? format(new Date(rfp.event_date), t('common.date_format'))
    : t('common.date_undetermined');
  const formattedCreatedAt = format(new Date(rfp.created_at), t('common.date_format'));

  const getStatusText = (status: string) => {
    switch (status) {
      case 'draft':
        return t('common.status_draft');
      case 'completed':
        return t('common.status_completed');
      case 'archived':
        return t('common.status_archived');
      default:
        return t('common.status_unknown');
    }
  };

  return (
    <Card className="w-full max-w-sm transition-all duration-200 hover:shadow-lg hover:border-primary">
      <CardHeader className="pb-2">
        <CardTitle className="text-xl font-semibold truncate">{rfp.title}</CardTitle>
        <CardDescription>
          <Badge className={`text-xs font-semibold ${
            rfp.status === 'completed' ? 'bg-green-500 text-white' :
            rfp.status === 'draft' ? 'bg-blue-500 text-white' :
            rfp.status === 'archived' ? 'bg-gray-500 text-white' :
            'bg-gray-300 text-gray-800'
          }`}>
            {getStatusText(rfp.status)}
          </Badge>
        </CardDescription>
      </CardHeader>
      <CardContent className="text-sm text-muted-foreground space-y-1">
        <p className="flex items-center">
          <CalendarIcon className="mr-2 h-4 w-4 text-primary" />
          {t('rfp_card.event_date_label')}{formattedEventDate}
        </p>
        <p className="flex items-center">
          <ClockIcon className="mr-2 h-4 w-4 text-primary" />
          {t('rfp_card.created_date_label')}{formattedCreatedAt}
        </p>
      </CardContent>
      <CardFooter className="pt-4">
        <Link href={`/rfp/${rfp.id}`} className="w-full">
          <Button variant="secondary" className="w-full">{t('rfp_card.view_details_button')}</Button>
        </Link>
      </CardFooter>
    </Card>
  );
}