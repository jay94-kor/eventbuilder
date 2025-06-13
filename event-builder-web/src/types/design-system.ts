/**
 * 디자인 시스템 타입 정의
 * 
 * 이 파일은 프로젝트 전반에서 사용되는 디자인 토큰과 CSS 클래스들의 
 * 타입 안전성을 보장하고 일관성을 유지하기 위한 TypeScript 타입 정의입니다.
 */

// ==========================================================================
// 색상 시스템 타입
// ==========================================================================

export type ColorVariant = 
  | 'primary'
  | 'secondary'
  | 'success'
  | 'warning'
  | 'destructive'
  | 'info'
  | 'muted'
  | 'accent';

export type ColorIntensity = 
  | 'light'
  | 'medium'
  | 'strong'
  | 'DEFAULT';

export type SemanticColor = 
  | 'background'
  | 'foreground'
  | 'card'
  | 'card-foreground'
  | 'popover'
  | 'popover-foreground'
  | 'border'
  | 'input'
  | 'ring';

// ==========================================================================
// 타이포그래피 시스템 타입
// ==========================================================================

export type TypographyVariant = 
  | 'display-2xl'
  | 'display-xl'
  | 'display-lg'
  | 'display-md'
  | 'display-sm'
  | 'heading-xl'
  | 'heading-lg'
  | 'heading-md'
  | 'heading-sm'
  | 'heading-xs'
  | 'body-lg'
  | 'body-md'
  | 'body-sm'
  | 'body-xs'
  | 'label-lg'
  | 'label-md'
  | 'label-sm'
  | 'description'
  | 'caption';

export type FontWeight = 
  | 'thin'
  | 'extralight'
  | 'light'
  | 'normal'
  | 'medium'
  | 'semibold'
  | 'bold'
  | 'extrabold'
  | 'black';

// ==========================================================================
// 스페이싱 시스템 타입
// ==========================================================================

export type SpacingSize = 
  | 'xs'
  | 'sm'
  | 'md'
  | 'lg'
  | 'xl'
  | '2xl'
  | '3xl'
  | '4xl'
  | '5xl'
  | '6xl';

// ==========================================================================
// 컴포넌트 사이즈 타입
// ==========================================================================

export type ComponentSize = 
  | 'xs'
  | 'sm'
  | 'md'
  | 'lg'
  | 'xl';

// ==========================================================================
// 버튼 관련 타입
// ==========================================================================

export type ButtonVariant = 
  | 'default'
  | 'destructive'
  | 'outline'
  | 'secondary'
  | 'ghost'
  | 'link'
  | 'brand'
  | 'success'
  | 'warning';

export type ButtonSize = 
  | 'sm'
  | 'md'
  | 'lg'
  | 'icon';

// ==========================================================================
// 카드 관련 타입
// ==========================================================================

export type CardVariant = 
  | 'base'
  | 'interactive'
  | 'brand'
  | 'elevated'
  | 'glass';

// ==========================================================================
// 배지 관련 타입
// ==========================================================================

export type BadgeVariant = 
  | 'default'
  | 'secondary'
  | 'destructive'
  | 'outline'
  | 'brand'
  | 'success'
  | 'warning'
  | 'info';

// ==========================================================================
// 입력 필드 관련 타입
// ==========================================================================

export type InputVariant = 
  | 'base'
  | 'error'
  | 'success';

export type InputState = 
  | 'default'
  | 'error'
  | 'success'
  | 'disabled'
  | 'loading';

// ==========================================================================
// 애니메이션 타입
// ==========================================================================

export type AnimationType = 
  | 'fade-in-up'
  | 'fade-in-down'
  | 'fade-in-left'
  | 'fade-in-right'
  | 'scale-in'
  | 'pulse-subtle'
  | 'bounce-subtle'
  | 'wiggle';

export type TransitionSpeed = 
  | 'fast'
  | 'base'
  | 'smooth'
  | 'slow';

// ==========================================================================
// 그림자 타입
// ==========================================================================

export type ShadowVariant = 
  | 'xs'
  | 'sm'
  | 'md'
  | 'lg'
  | 'xl'
  | '2xl'
  | 'brand'
  | 'brand-strong'
  | 'elevated'
  | 'success'
  | 'warning'
  | 'destructive'
  | 'inner'
  | 'none';

// ==========================================================================
// 반지름 타입
// ==========================================================================

export type BorderRadiusSize = 
  | 'none'
  | 'sm'
  | 'md'
  | 'lg'
  | 'xl'
  | '2xl'
  | 'full';

// ==========================================================================
// 상태 관련 타입
// ==========================================================================

export type StatusVariant = 
  | 'active'
  | 'inactive'
  | 'pending'
  | 'error';

// ==========================================================================
// 이벤트 카테고리 타입
// ==========================================================================

export type EventCategory = 
  | 'venue'
  | 'tech'
  | 'catering'
  | 'staff'
  | 'design'
  | 'marketing';

// ==========================================================================
// 레이아웃 관련 타입
// ==========================================================================

export type ContainerSize = 
  | 'xs'
  | 'sm'
  | 'md'
  | 'lg'
  | 'xl'
  | '2xl'
  | '3xl'
  | '4xl'
  | '5xl'
  | '6xl'
  | '7xl'
  | 'brand';

export type SectionSize = 
  | 'sm'
  | 'md'
  | 'lg'
  | 'brand';

// ==========================================================================
// 반응형 브레이크포인트 타입
// ==========================================================================

export type Breakpoint = 
  | 'xs'
  | 'sm'
  | 'md'
  | 'lg'
  | 'xl'
  | '2xl'
  | '3xl';

// ==========================================================================
// Z-Index 타입
// ==========================================================================

export type ZIndexLayer = 
  | 'hide'
  | 'auto'
  | 'base'
  | 'docked'
  | 'dropdown'
  | 'sticky'
  | 'banner'
  | 'overlay'
  | 'modal'
  | 'popover'
  | 'skipLink'
  | 'toast'
  | 'tooltip';

// ==========================================================================
// 유틸리티 클래스 생성 헬퍼 타입
// ==========================================================================

export type ClassNameBuilder<T extends string> = T | `${T}-${string}`;

// ==========================================================================
// CSS 클래스 조합 타입
// ==========================================================================

export interface DesignTokens {
  colors: {
    variant: ColorVariant;
    intensity?: ColorIntensity;
    semantic: SemanticColor;
  };
  typography: {
    variant: TypographyVariant;
    weight?: FontWeight;
  };
  spacing: {
    size: SpacingSize;
  };
  components: {
    button: {
      variant: ButtonVariant;
      size: ButtonSize;
    };
    card: {
      variant: CardVariant;
    };
    badge: {
      variant: BadgeVariant;
    };
    input: {
      variant: InputVariant;
      state: InputState;
    };
  };
  effects: {
    animation: AnimationType;
    transition: TransitionSpeed;
    shadow: ShadowVariant;
    radius: BorderRadiusSize;
  };
  layout: {
    container: ContainerSize;
    section: SectionSize;
    breakpoint: Breakpoint;
    zIndex: ZIndexLayer;
  };
}

// ==========================================================================
// 컴포넌트 props 확장을 위한 공통 인터페이스
// ==========================================================================

export interface BaseComponentProps {
  className?: string;
  variant?: string;
  size?: ComponentSize;
  disabled?: boolean;
  loading?: boolean;
}

export interface InteractiveComponentProps extends BaseComponentProps {
  onClick?: () => void;
  onHover?: () => void;
  onFocus?: () => void;
  onBlur?: () => void;
}

export interface FormComponentProps extends BaseComponentProps {
  error?: boolean;
  success?: boolean;
  required?: boolean;
  placeholder?: string;
  helperText?: string;
  errorMessage?: string;
}

// ==========================================================================
// 테마 관련 타입
// ==========================================================================

export type ThemeMode = 'light' | 'dark' | 'system';

export interface ThemeConfig {
  mode: ThemeMode;
  primaryColor: string;
  borderRadius: BorderRadiusSize;
  fontScale: number;
  spacingScale: number;
}

// ==========================================================================
// 브랜딩 관련 타입
// ==========================================================================

export interface BrandConfig {
  primaryColor: string;
  secondaryColor: string;
  accentColor: string;
  fontFamily: string;
  logoUrl?: string;
  brandName: string;
}

// ==========================================================================
// 사용자 정의 CSS 속성 타입 (Tailwind와 함께 사용)
// ==========================================================================

export interface CustomCSSProperties extends React.CSSProperties {
  '--primary'?: string;
  '--primary-foreground'?: string;
  '--secondary'?: string;
  '--secondary-foreground'?: string;
  '--background'?: string;
  '--foreground'?: string;
  '--border'?: string;
  '--radius'?: string;
  '--spacing-xs'?: string;
  '--spacing-sm'?: string;
  '--spacing-md'?: string;
  '--spacing-lg'?: string;
  '--spacing-xl'?: string;
  '--spacing-2xl'?: string;
  '--spacing-3xl'?: string;
  '--spacing-4xl'?: string;
  '--spacing-5xl'?: string;
  '--spacing-6xl'?: string;
}

// ==========================================================================
// 디자인 시스템 유틸리티 함수 타입
// ==========================================================================

export type ClassNameFunction = (...args: (string | undefined | null | boolean)[]) => string;

export interface DesignSystemUtils {
  cn: ClassNameFunction;
  getColorClass: (variant: ColorVariant, intensity?: ColorIntensity) => string;
  getTypographyClass: (variant: TypographyVariant) => string;
  getSpacingClass: (size: SpacingSize, property: 'p' | 'm' | 'px' | 'py' | 'mx' | 'my') => string;
  getShadowClass: (variant: ShadowVariant) => string;
  getAnimationClass: (animation: AnimationType) => string;
}

// ==========================================================================
// 컴포넌트별 특화 타입 내보내기 (향후 확장 가능)
// ==========================================================================

// export * from './components/button-types';
// export * from './components/card-types';
// export * from './components/form-types';
// export * from './components/layout-types';

// ==========================================================================
// 기본 내보내기
// ==========================================================================

export default DesignTokens; 