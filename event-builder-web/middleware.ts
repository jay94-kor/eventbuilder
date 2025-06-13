import { NextResponse } from 'next/server'
import type { NextRequest } from 'next/server'

// 인증이 필요한 경로들
const protectedPaths = [
  '/dashboard',
  '/profile',
  '/onboarding',
  '/rfp',
  '/event-basics'
]

// 인증된 사용자가 접근하면 안 되는 경로들 (로그인, 회원가입 페이지)
const authPaths = [
  '/login',
  '/register'
]

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl
  
  // 쿠키에서 토큰 확인
  const token = request.cookies.get('auth-token')?.value
  
  // 보호된 경로에 접근하려고 하는데 토큰이 없는 경우
  if (protectedPaths.some(path => pathname.startsWith(path)) && !token) {
    // 현재 경로를 쿼리 파라미터로 저장하여 로그인 후 원래 페이지로 돌아갈 수 있게 함
    const loginUrl = new URL('/login', request.url)
    loginUrl.searchParams.set('redirect', pathname)
    
    return NextResponse.redirect(loginUrl)
  }
  
  // 이미 로그인한 사용자가 로그인/회원가입 페이지에 접근하는 경우
  if (authPaths.some(path => pathname.startsWith(path)) && token) {
    // 리다이렉트 쿼리 파라미터가 있으면 해당 페이지로, 없으면 대시보드로
    const redirectTo = request.nextUrl.searchParams.get('redirect') || '/dashboard'
    return NextResponse.redirect(new URL(redirectTo, request.url))
  }
  
  return NextResponse.next()
}

// 미들웨어가 실행될 경로 설정
export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     * - public folder files
     */
    '/((?!api|_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)',
  ],
} 