import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import { Language } from '@/lib/i18n'; // i18n.ts에서 Language 타입 import

interface LanguageState {
  currentLanguage: Language;
  setLanguage: (language: Language) => void;
}

export const useLanguageStore = create<LanguageState>()(
  persist(
    (set) => ({
      currentLanguage: 'ko', // 기본 언어는 한국어
      setLanguage: (language: Language) => set({ currentLanguage: language }),
    }),
    {
      name: 'language-storage', // 로컬 스토리지에 저장될 이름
      storage: createJSONStorage(() => localStorage), // 로컬 스토리지 사용
    }
  )
);