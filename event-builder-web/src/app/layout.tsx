import type { Metadata } from "next";
import { GeistSans } from 'geist/font/sans';
import { GeistMono } from 'geist/font/mono';
import "./globals.css";
import ErrorBoundary from '@/components/common/ErrorBoundary';
import { getTranslation, Language } from '@/lib/i18n';
import { headers } from 'next/headers';
import { ThemeProvider } from 'next-themes';
import { Toaster } from '@/components/ui/sonner';

export async function generateMetadata(): Promise<Metadata> {
  const headersList = headers();
  const acceptLanguage = (await headersList).get('accept-language') || 'ko';
  const lang: Language = acceptLanguage.split(',')[0].split('-')[0] as Language;

  const title = getTranslation(lang, 'common.app_name') + ' | Bidly';
  const description = getTranslation(lang, 'common.app_description');

  return {
    title: title,
    description: description,
  };
}

export default async function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const headersList = headers();
  const acceptLanguage = (await headersList).get('accept-language') || 'ko';
  const lang: Language = acceptLanguage.split(',')[0].split('-')[0] as Language;

  return (
    <html lang={lang} suppressHydrationWarning className={`${GeistSans.variable} ${GeistMono.variable}`}>
      <body>
        <ThemeProvider attribute="class" defaultTheme="system" enableSystem>
          <ErrorBoundary>
            {children}
          </ErrorBoundary>
          <Toaster />
        </ThemeProvider>
      </body>
    </html>
  )
}
