// frontend/app/layout.tsx

import './globals.css';
import Providers from './Providers';

import localFont from 'next/font/local'; // localFont 임포트

const pretendard = localFont({
  src: '../public/fonts/PretendardVariable.woff2', // 폰트 파일 경로
  display: 'swap',
  variable: '--font-pretendard', // CSS 변수 이름 설정
  weight: '400 900', // 폰트가 지원하는 가변 폰트 범위
});

export const metadata = {
  title: 'Bidly Platform',
  description: '행사 기획 대행사와 용역사를 연결하는 입찰 플랫폼',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    // html 태그에 폰트 변수 클래스 적용
    <html lang="ko" className={`${pretendard.variable}`}>
      <body>
        <Providers>
          {children}
        </Providers>
      </body>
    </html>
  );
}