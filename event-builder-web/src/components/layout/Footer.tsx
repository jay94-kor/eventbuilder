import { useTranslation } from '@/lib/i18n';

export default function Footer() {
  const { t } = useTranslation();
  return (
    <footer className="bg-background border-t border">
      <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div className="text-center text-description">
          <p>{t('footer.copyright')}</p>
        </div>
      </div>
    </footer>
  )
}