'use client';

import Link from 'next/link'
import { useState, useEffect } from 'react'
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { useLanguageStore } from '@/stores/languageStore'
import { Language } from '@/lib/i18n'
import { useTranslation } from '@/lib/i18n';
import { useTheme } from 'next-themes';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import HelpDialog from '@/components/common/HelpDialog';
import { HelpCircleIcon } from 'lucide-react'; // HelpCircleIcon 임포트

export default function Header() {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const { user, logout, setOnboarded } = useAuth(); // setOnboarded 추가
  const { t } = useTranslation(); // useTranslation 훅 사용
  const { currentLanguage, setLanguage } = useLanguageStore(); // languageStore에서 상태와 액션 가져오기
  const { theme, setTheme } = useTheme();
  const [mounted, setMounted] = useState(false);
  const router = useRouter();

  const navItems = [
    { href: '/', textKey: 'common.home' },
    { href: '/dashboard', textKey: 'common.dashboard' },
    { href: '/rfp', textKey: 'common.rfp_list' },
    { href: '/rfp/create', textKey: 'common.create_rfp' },
  ];

  useEffect(() => {
    setMounted(true);
  }, []);

  const handleNavigationClick = () => {
    if (user && user.onboarded === false) {
      setOnboarded(true);
    }
    setIsMenuOpen(false); // 모바일 메뉴 닫기
  };

  const handleLanguageChange = (lang: Language) => {
    setLanguage(lang);
  };

  return (
    <header className="bg-background shadow-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center py-4">
          {/* 로고 */}
          <div className="flex items-center">
            <Link href="/" className="flex items-center">
              <h1 className="text-xl sm:text-heading-lg">{t('common.app_name')}</h1>
            </Link>
          </div>

          {/* 데스크톱 네비게이션 */}
          <nav className="hidden md:flex items-center space-x-8">
            {navItems.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="text-muted-foreground hover:text-foreground transition-colors text-sm font-medium"
                onClick={handleNavigationClick}
              >
                {t(item.textKey)}
              </Link>
            ))}
            
            {user && (
              <div className="flex items-center space-x-4">
                <Link
                  href="/profile"
                  className="text-muted-foreground hover:text-foreground transition-colors text-sm font-medium"
                  onClick={handleNavigationClick}
                >
                  {t('common.my_profile')}
                </Link>
                <span className="text-sm text-muted-foreground">
                  {user.name}{t('common.greeting_suffix')}
                </span>
                <Button
                  onClick={() => {
                    logout();
                    // 로그아웃 시 온보딩 상태 초기화 (선택 사항, 필요에 따라)
                    setOnboarded(false);
                    router.push('/login'); // 로그인 페이지로 리다이렉트
                  }}
                  variant="outline"
                  size="sm"
                >
                  {t('common.logout')}
                </Button>
              </div>
            )}
            {/* 도움말 버튼 (데스크톱) */}
            <HelpDialog />

            {/* 언어 선택 드롭다운 (데스크톱) */}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="justify-between">
                  {t(`common.lang_${currentLanguage}`)}
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem onClick={() => handleLanguageChange('ko')}>{t('common.lang_ko')}</DropdownMenuItem>
                <DropdownMenuItem onClick={() => handleLanguageChange('en')}>{t('common.lang_en')}</DropdownMenuItem>
                <DropdownMenuItem onClick={() => handleLanguageChange('zh')}>{t('common.lang_zh')}</DropdownMenuItem>
                <DropdownMenuItem onClick={() => handleLanguageChange('ja')}>{t('common.lang_ja')}</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            {/* 다크 모드 선택 드롭다운 (데스크톱) */}
            {mounted && (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="outline" size="sm" className="justify-between">
                    {t(`common.theme_${theme}`)}
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem onClick={() => setTheme('system')}>{t('common.theme_system')}</DropdownMenuItem>
                  <DropdownMenuItem onClick={() => setTheme('light')}>{t('common.theme_light')}</DropdownMenuItem>
                  <DropdownMenuItem onClick={() => setTheme('dark')}>{t('common.theme_dark')}</DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            )}
          </nav>

          {/* 모바일 햄버거 메뉴 버튼 */}
          <div className="md:hidden">
            <Button
              variant="ghost"
              size="icon-sm"
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="md:hidden"
              aria-expanded="false"
            >
              <span className="sr-only">{t('common.open_menu')}</span>
              {isMenuOpen ? (
                <svg className="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              ) : (
                <svg className="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              )}
            </Button>
          </div>
        </div>

        {/* 모바일 네비게이션 메뉴 */}
        {isMenuOpen && (
          <div className="md:hidden border-t border py-4">
            <div className="space-y-1">
              {navItems.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className="block px-3 py-2 text-base font-medium text-muted-foreground hover:text-foreground hover:bg-muted rounded-md"
                  onClick={handleNavigationClick}
                >
                  {t(item.textKey)}
                </Link>
              ))}
              
              {user && (
                <>
                  <Link
                    href="/profile"
                    className="block px-3 py-2 text-base font-medium text-muted-foreground hover:text-foreground hover:bg-muted rounded-md"
                    onClick={handleNavigationClick}
                  >
                    {t('common.my_profile')}
                  </Link>
                  <div className="px-3 py-2 text-description border-t border mt-4 pt-4">
                    {user.name}{t('common.greeting_suffix')}
                  </div>
                  <Button
                    onClick={() => {
                      logout();
                      setIsMenuOpen(false);
                      setOnboarded(false); // 로그아웃 시 온보딩 상태 초기화 (선택 사항, 필요에 따라)
                      router.push('/login'); // 로그인 페이지로 리다이렉트
                    }}
                    variant="ghost"
                    size="sm"
                    className="w-full justify-start text-base font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md"
                  >
                    {t('common.logout')}
                  </Button>
                </>
              )}
              {/* 도움말 버튼 (모바일) */}
              <div className="px-3 py-2">
                <HelpDialog>
                  <Button variant="ghost" size="sm" className="w-full justify-start text-base font-medium text-muted-foreground hover:text-foreground hover:bg-muted rounded-md">
                    <HelpCircleIcon className="h-5 w-5 mr-2" />
                    {t('help.button_text')}
                  </Button>
                </HelpDialog>
              </div>

              {/* 언어 선택 드롭다운 (모바일) */}
              <div className="px-3 py-2">
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm" className="w-full justify-between">
                      {t(`common.lang_${currentLanguage}`)}
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem onClick={() => handleLanguageChange('ko')}>{t('common.lang_ko')}</DropdownMenuItem>
                    <DropdownMenuItem onClick={() => handleLanguageChange('en')}>{t('common.lang_en')}</DropdownMenuItem>
                    <DropdownMenuItem onClick={() => handleLanguageChange('zh')}>{t('common.lang_zh')}</DropdownMenuItem>
                    <DropdownMenuItem onClick={() => handleLanguageChange('ja')}>{t('common.lang_ja')}</DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
              {/* 다크 모드 선택 드롭다운 (모바일) */}
              {mounted && (
                <div className="px-3 py-2">
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="outline" size="sm" className="w-full justify-between">
                        {t(`common.theme_${theme}`)}
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem onClick={() => setTheme('system')}>{t('common.theme_system')}</DropdownMenuItem>
                      <DropdownMenuItem onClick={() => setTheme('light')}>{t('common.theme_light')}</DropdownMenuItem>
                      <DropdownMenuItem onClick={() => setTheme('dark')}>{t('common.theme_dark')}</DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </header>
  )
}