import type { ColorVariant, ColorIntensity } from "@/types/design-system";

/**
 * 색상 variant와 intensity에 따른 배경색 클래스를 반환
 */
export function getBackgroundColorClass(
  variant: ColorVariant,
  intensity: ColorIntensity = 'DEFAULT'
): string {
  if (intensity === 'DEFAULT') {
    return `bg-${variant}`;
  }
  return `bg-${variant}-${intensity}`;
}

/**
 * 색상 variant에 따른 텍스트 색상 클래스를 반환
 */
export function getTextColorClass(variant: ColorVariant): string {
  return `text-${variant}`;
}

/**
 * 색상 variant에 따른 테두리 색상 클래스를 반환
 */
export function getBorderColorClass(
  variant: ColorVariant,
  intensity: ColorIntensity = 'DEFAULT'
): string {
  if (intensity === 'DEFAULT') {
    return `border-${variant}`;
  }
  return `border-${variant}-${intensity}`;
}