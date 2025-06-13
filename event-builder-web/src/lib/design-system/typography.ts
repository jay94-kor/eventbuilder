import { cn } from "./utils";
import type { TypographyVariant } from "@/types/design-system";

/**
 * 타이포그래피 variant에 따른 텍스트 클래스를 반환
 */
export function getTypographyClass(variant: TypographyVariant): string {
  return `text-${variant}`;
}

/**
 * 제목용 타이포그래피 클래스 생성
 */
export function getHeadingClass(
  level: 1 | 2 | 3 | 4 | 5 | 6,
  className?: string
): string {
  const headingClasses = {
    1: 'text-heading-xl',
    2: 'text-heading-lg',
    3: 'text-heading-md',
    4: 'text-heading-sm',
    5: 'text-heading-xs',
    6: 'text-body-lg font-semibold'
  };

  return cn(headingClasses[level], className);
}