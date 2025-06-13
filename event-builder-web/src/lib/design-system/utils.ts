import { type ClassValue, clsx } from "clsx";
import { twMerge } from "tailwind-merge";

/**
 * Tailwind CSS 클래스들을 조건부로 병합하고 중복을 제거하는 함수
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}