import { useCallback } from 'react';
import { useLanguageStore } from '@/stores/languageStore';
import { ko } from './locales/ko';
import { en } from './locales/en';
import { zh } from './locales/zh';
import { ja } from './locales/ja';

export type Language = 'ko' | 'en' | 'zh' | 'ja';

export interface LocalizedContent {
  ko: { name: string; emoji: string; description?: string };
  en: { name: string; emoji: string; description?: string };
  zh: { name: string; emoji: string; description?: string };
  ja: { name: string; emoji: string; description?: string };
}

const translations = {
  ko,
  en,
  zh,
  ja,
};

// 중첩된 키 경로를 따라 텍스트를 찾는 헬퍼 함수
export function getTranslation(lang: Language, key: string, params?: Record<string, string | number>): string {
  const langContent = translations[lang];
  if (!langContent) {
    console.warn(`Language content for "${lang}" not found.`);
    return `[Missing Language: ${lang}]`;
  }
 
  let current: string | object = langContent;
  const path = key.split('.');

  for (let i = 0; i < path.length; i++) {
    const segment = path[i];
    if (typeof current === 'object' && current !== null && segment in current) {
      current = (current as Record<string, unknown>)[segment] as string | object;
    } else {
      console.warn(`Translation key "${key}" not found for language "${lang}". Missing segment: "${segment}"`);
      return `[Missing Text: ${key}]`;
    }
  }

  if (typeof current === 'string') {
    let translatedText = current;
    // 플레이스홀더 치환
    if (params) {
      for (const paramKey in params) {
        translatedText = translatedText.replace(new RegExp(`{${paramKey}}`, 'g'), String(params[paramKey]));
      }
    }
    return translatedText;
  } else {
    console.warn(`Translation for key "${key}" in language "${lang}" is not a string.`);
    return `[Invalid Text: ${key}]`;
  }
}

export function useTranslation() {
  const { currentLanguage } = useLanguageStore();
  const t = useCallback((key: string, params?: Record<string, string | number>): string => {
    return getTranslation(currentLanguage, key, params);
  }, [currentLanguage]);

  return { t, currentLanguage };
}
