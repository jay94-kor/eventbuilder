'use client';

import { useTranslation } from '@/lib/i18n';
import { Button } from '@/components/ui/button';
import Link from 'next/link';

export default function HomePage() {
  const t = useTranslation();

  return (
    <div className="flex flex-col items-center justify-center min-h-[calc(100vh-4rem)] text-center px-4">
      <h1 className="text-4xl font-bold mb-4">{t('home.welcome_title')}</h1>
      <p className="text-lg text-muted-foreground mb-8">{t('home.welcome_message')}</p>
      <Link href="/dashboard">
        <Button size="lg">{t('home.go_to_dashboard')}</Button>
      </Link>
    </div>
  );
}