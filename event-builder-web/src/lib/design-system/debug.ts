/**
 * 개발 환경에서 디자인 시스템 클래스 검증
 */
export function validateDesignSystemClass(className: string): boolean {
  if (process.env.NODE_ENV !== 'development') return true;
  
  const validPrefixes = [
    'text-', 'bg-', 'border-', 'shadow-', 'hover-', 'focus-',
    'card-', 'btn-', 'badge-', 'input-', 'status-', 'category-',
    'container-', 'section-', 'animate-', 'transition-',
    'p-', 'm-', 'px-', 'py-', 'mx-', 'my-',
    'pt-', 'pb-', 'pl-', 'pr-', 'mt-', 'mb-', 'ml-', 'mr-'
  ];
  
  return validPrefixes.some(prefix => className.startsWith(prefix));
}

interface WindowWithDesignSystem extends Window {
  __designSystemUsage?: Record<string, number>;
}

/**
 * 디자인 시스템 클래스 사용량 추적 (개발 환경용)
 */
export function trackDesignSystemUsage(className: string) {
  if (process.env.NODE_ENV !== 'development') return;
  
  // 브라우저 환경에서만 실행
  if (typeof window !== 'undefined') {
    const usage = (window as WindowWithDesignSystem).__designSystemUsage || {};
    usage[className] = (usage[className] || 0) + 1;
    (window as WindowWithDesignSystem).__designSystemUsage = usage;
  }
}