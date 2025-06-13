import { cn } from "./utils";
import { getPaddingClass } from "./spacing";
import type {
  ButtonVariant,
  ButtonSize,
  CardVariant,
  BadgeVariant,
  InputVariant,
  StatusVariant,
  EventCategory,
  SpacingSize,
} from "@/types/design-system";

/**
 * 버튼 클래스 생성
 */
export function getButtonClass(
  variant: ButtonVariant = 'default',
  size: ButtonSize = 'md',
  className?: string
): string {
  const baseClass = 'btn-base';
  const variantClass = variant === 'default' ? '' : `btn-${variant}`;
  const sizeClass = size === 'md' ? '' : `btn-${size}`;
  
  return cn(baseClass, variantClass, sizeClass, className);
}

/**
 * 카드 클래스 생성
 */
export function getCardClass(
  variant: CardVariant = 'base',
  className?: string
): string {
  return cn(`card-${variant}`, className);
}

/**
 * 배지 클래스 생성
 */
export function getBadgeClass(
  variant: BadgeVariant = 'default',
  className?: string
): string {
  return cn(`badge-${variant}`, className);
}

/**
 * 입력 필드 클래스 생성
 */
export function getInputClass(
  variant: InputVariant = 'base',
  error?: boolean,
  success?: boolean,
  className?: string
): string {
  const baseClass = `input-${variant}`;
  const stateClass = error ? 'input-error' : success ? 'input-success' : '';
  
  return cn(baseClass, stateClass, className);
}

/**
 * 상태 배지 클래스 생성
 */
export function getStatusClass(
  status: StatusVariant,
  className?: string
): string {
  return cn(`status-${status}`, className);
}

/**
 * 이벤트 카테고리 클래스 생성
 */
export function getCategoryClass(
  category: EventCategory,
  className?: string
): string {
  return cn(`category-${category}`, className);
}

/**
 * 폼 그룹 클래스 생성 (라벨, 입력, 에러 메시지를 포함한 완전한 폼 필드)
 */
export function getFormGroupClass(
  error?: boolean,
  success?: boolean,
  className?: string
): string {
  const baseClass = 'form-group';
  const stateClass = error ? 'form-group-error' : success ? 'form-group-success' : '';
  
  return cn(baseClass, stateClass, className);
}

/**
 * 인터랙티브 요소 클래스 생성 (호버, 포커스, 액티브 상태 포함)
 */
export function getInteractiveClass(
  disabled?: boolean,
  className?: string
): string {
  const baseClass = 'transition-smooth focus-brand';
  const hoverClass = disabled ? '' : 'hover-glow';
  const disabledClass = disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer';
  
  return cn(baseClass, hoverClass, disabledClass, className);
}

/**
 * 로딩 상태 클래스 생성
 */
export function getLoadingClass(
  type: 'spinner' | 'pulse' | 'overlay' = 'spinner',
  className?: string
): string {
  const loadingClasses = {
    spinner: 'loading-spinner',
    pulse: 'loading-pulse',
    overlay: 'loading-overlay'
  };
  
  return cn(loadingClasses[type], className);
}

/**
 * 완전한 버튼 스타일 생성 (모든 상태와 변형 포함)
 */
export function createButtonStyles(options: {
  variant?: ButtonVariant;
  size?: ButtonSize;
  disabled?: boolean;
  loading?: boolean;
  fullWidth?: boolean;
  className?: string;
}) {
  const {
    variant = 'default',
    size = 'md',
    disabled = false,
    loading = false,
    fullWidth = false,
    className
  } = options;

  return cn(
    getButtonClass(variant, size),
    disabled && 'opacity-50 cursor-not-allowed',
    loading && 'relative overflow-hidden',
    fullWidth && 'w-full',
    className
  );
}

/**
 * 완전한 카드 스타일 생성 (상호작용, 애니메이션 포함)
 */
export function createCardStyles(options: {
  variant?: CardVariant;
  interactive?: boolean;
  hover?: boolean;
  padding?: SpacingSize;
  className?: string;
}) {
  const {
    variant = 'base',
    interactive = false,
    hover = false,
    padding = 'lg',
    className
  } = options;

  return cn(
    getCardClass(variant),
    interactive && 'cursor-pointer',
    hover && 'hover-lift',
    getPaddingClass(padding),
    className
  );
}