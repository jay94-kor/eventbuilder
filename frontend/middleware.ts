// frontend/middleware.ts

import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export async function middleware(request: NextRequest) {
  // 미들웨어는 localStorage에 직접 접근할 수 없으므로, AuthProvider에서 클라이언트 라우팅으로 제어됩니다.
  // 여기서는 기본적인 공통 경로 처리만 담당합니다.

  // 기본적으로는 Next.js의 클라이언트 사이드 인증 로직 (AuthProvider)에 의존합니다.
  return NextResponse.next();
}

export const config = {
  matcher: [
    '/((?!api|_next/static|_next/image|favicon.ico).*)',
  ],
};