import type { SpacingSize } from "@/types/design-system";

type PaddingProperty = 'p' | 'px' | 'py' | 'pt' | 'pb' | 'pl' | 'pr';
type MarginProperty = 'm' | 'mx' | 'my' | 'mt' | 'mb' | 'ml' | 'mr';

/**
 * 스페이싱 사이즈와 속성에 따른 클래스를 반환
 */
export function getSpacingClass(
  size: SpacingSize,
  property: PaddingProperty | MarginProperty
): string {
  return `${property}-${size}`;
}

/**
 * 패딩 클래스 생성
 */
export function getPaddingClass(size: SpacingSize, direction?: 'x' | 'y' | 't' | 'b' | 'l' | 'r'): string {
  const property: PaddingProperty = direction ? `p${direction}` : 'p';
  return getSpacingClass(size, property);
}

/**
 * 마진 클래스 생성
 */
export function getMarginClass(size: SpacingSize, direction?: 'x' | 'y' | 't' | 'b' | 'l' | 'r'): string {
  const property: MarginProperty = direction ? `m${direction}` : 'm';
  return getSpacingClass(size, property);
}