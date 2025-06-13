import { cn } from "./utils";
import type { ShadowVariant, AnimationType, ContainerSize, SectionSize, SpacingSize } from "@/types/design-system";

/**
 * 그림자 variant에 따른 클래스를 반환
 */
export function getShadowClass(variant: ShadowVariant): string {
  if (variant === 'none') {
    return 'shadow-none';
  }
  return `shadow-${variant}`;
}

/**
 * 애니메이션 타입에 따른 클래스를 반환
 */
export function getAnimationClass(animation: AnimationType): string {
  return `animate-${animation}`;
}

/**
 * 호버 효과 클래스 생성
 */
export function getHoverEffectClass(effect: 'lift' | 'glow' | 'scale' | 'fade'): string {
  return `hover-${effect}`;
}

/**
 * 컨테이너 클래스 생성
 */
export function getContainerClass(
  size: ContainerSize = 'brand',
  className?: string
): string {
  return cn(`container-${size}`, className);
}

/**
 * 섹션 클래스 생성
 */
export function getSectionClass(
  size: SectionSize = 'brand',
  className?: string
): string {
  return cn(`section-${size}`, className);
}

/**
 * 그리드 클래스 생성
 */
export function getGridClass(
  cols: 1 | 2 | 3 | 4 | 5 | 6 | 12,
  gap: SpacingSize = 'lg',
  className?: string
): string {
  return cn(`grid grid-cols-${cols} gap-${gap}`, className);
}

/**
 * Flexbox 클래스 생성
 */
export function getFlexClass(
  direction: 'row' | 'col' = 'row',
  align: 'start' | 'center' | 'end' | 'stretch' = 'start',
  justify: 'start' | 'center' | 'end' | 'between' | 'around' | 'evenly' = 'start',
  gap: SpacingSize = 'md',
  className?: string
): string {
  const directionClass = direction === 'row' ? 'flex-row' : 'flex-col';
  const alignClass = `items-${align}`;
  const justifyClass = `justify-${justify}`;
  const gapClass = `gap-${gap}`;
  
  return cn('flex', directionClass, alignClass, justifyClass, gapClass, className);
}

/**
 * 반응형 클래스 생성 헬퍼
 */
export function responsive<T extends string>(classes: {
  base?: T;
  sm?: T;
  md?: T;
  lg?: T;
  xl?: T;
  '2xl'?: T;
}): string {
  const classArray: string[] = [];
  
  if (classes.base) classArray.push(classes.base);
  if (classes.sm) classArray.push(`sm:${classes.sm}`);
  if (classes.md) classArray.push(`md:${classes.md}`);
  if (classes.lg) classArray.push(`lg:${classes.lg}`);
  if (classes.xl) classArray.push(`xl:${classes.xl}`);
  if (classes['2xl']) classArray.push(`2xl:${classes['2xl']}`);
  
  return classArray.join(' ');
}

/**
 * 글래스모피즘 효과 클래스 생성
 */
export function getGlassClass(
  intensity: 'subtle' | 'medium' | 'strong' = 'medium',
  className?: string
): string {
  const glassClasses = {
    subtle: 'glass-subtle',
    medium: 'glass',
    strong: 'glass-strong'
  };
  
  return cn(glassClasses[intensity], className);
}