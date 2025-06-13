/**
 * 디자인 토큰 정의
 * 
 * 이 파일은 프로젝트 전반에서 사용되는 디자인 토큰을 JavaScript/TypeScript 객체로 정의합니다.
 * CSS 변수와 연동하여 일관된 디자인 시스템을 구축합니다.
 */

export const colors = {
  background: 'var(--background)',
  foreground: 'var(--foreground)',
  card: 'var(--card)',
  cardForeground: 'var(--card-foreground)',
  popover: 'var(--popover)',
  popoverForeground: 'var(--popover-foreground)',
  primary: {
    DEFAULT: 'var(--primary)',
    foreground: 'var(--primary-foreground)',
  },
  secondary: {
    DEFAULT: 'var(--secondary)',
    foreground: 'var(--secondary-foreground)',
  },
  muted: {
    DEFAULT: 'var(--muted)',
    foreground: 'var(--muted-foreground)',
  },
  accent: {
    DEFAULT: 'var(--accent)',
    foreground: 'var(--accent-foreground)',
  },
  destructive: {
    DEFAULT: 'var(--destructive)',
    foreground: 'var(--destructive-foreground)',
  },
  border: 'var(--border)',
  input: 'var(--input)',
  ring: 'var(--ring)',
  success: {
    DEFAULT: 'var(--success)',
    foreground: 'var(--success-foreground)',
  },
  warning: {
    DEFAULT: 'var(--warning)',
    foreground: 'var(--warning-foreground)',
  },
  info: {
    DEFAULT: 'var(--info)',
    foreground: 'var(--info-foreground)',
  },
  chart: {
    1: 'var(--chart-1)',
    2: 'var(--chart-2)',
    3: 'var(--chart-3)',
    4: 'var(--chart-4)',
    5: 'var(--chart-5)',
    6: 'var(--chart-6)',
    7: 'var(--chart-7)',
    8: 'var(--chart-8)',
    9: 'var(--chart-9)',
    10: 'var(--chart-10)',
  },
  sidebar: {
    DEFAULT: 'var(--sidebar)',
    foreground: 'var(--sidebar-foreground)',
    primary: {
      DEFAULT: 'var(--sidebar-primary)',
      foreground: 'var(--sidebar-primary-foreground)',
    },
    accent: {
      DEFAULT: 'var(--sidebar-accent)',
      foreground: 'var(--sidebar-accent-foreground)',
    },
    border: 'var(--sidebar-border)',
    ring: 'var(--sidebar-ring)',
  },
};

export const spacing = {
  xs: 'var(--spacing-xs)',
  sm: 'var(--spacing-sm)',
  md: 'var(--spacing-md)',
  lg: 'var(--spacing-lg)',
  xl: 'var(--spacing-xl)',
  '2xl': 'var(--spacing-2xl)',
  '3xl': 'var(--spacing-3xl)',
  '4xl': 'var(--spacing-4xl)',
  '5xl': 'var(--spacing-5xl)',
  '6xl': 'var(--spacing-6xl)',
};

export const borderRadius = {
  none: '0',
  sm: 'var(--radius-sm)',
  DEFAULT: 'var(--radius)',
  md: 'var(--radius-md)',
  lg: 'var(--radius-lg)',
  xl: 'var(--radius-xl)',
  '2xl': 'var(--radius-2xl)',
  full: '9999px',
};

export const boxShadow = {
  xs: 'var(--shadow-xs)',
  sm: 'var(--shadow-sm)',
  DEFAULT: 'var(--shadow-md)',
  md: 'var(--shadow-md)',
  lg: 'var(--shadow-lg)',
  xl: 'var(--shadow-xl)',
  '2xl': 'var(--shadow-2xl)',
  brand: 'var(--shadow-brand)',
  brandStrong: 'var(--shadow-brand-strong)',
  elevated: 'var(--shadow-elevated)',
  success: '0 10px 15px -3px oklch(var(--success) / 0.2), 0 4px 6px -2px oklch(var(--success) / 0.1)',
  warning: '0 10px 15px -3px oklch(var(--warning) / 0.2), 0 4px 6px -2px oklch(var(--warning) / 0.1)',
  destructive: '0 10px 15px -3px oklch(var(--destructive) / 0.2), 0 4px 6px -2px oklch(var(--destructive) / 0.1)',
  inner: 'inset 0 2px 4px 0 rgb(0 0 0 / 0.05)',
  none: 'none',
};

export const transitions = {
  fast: 'var(--transition-fast)',
  base: 'var(--transition-base)',
  smooth: 'var(--transition-smooth)',
  slow: 'var(--transition-slow)',
};

export const containers = {
  xs: 'var(--container-xs)',
  sm: 'var(--container-sm)',
  md: 'var(--container-md)',
  lg: 'var(--container-lg)',
  xl: 'var(--container-xl)',
  '2xl': 'var(--container-2xl)',
  brand: 'var(--container-brand)',
};

export const animations = {
  fadeInUp: 'fade-in-up 0.6s ease-out forwards',
  fadeInDown: 'fade-in-down 0.6s ease-out forwards',
  fadeInLeft: 'fade-in-left 0.6s ease-out forwards',
  fadeInRight: 'fade-in-right 0.6s ease-out forwards',
  scaleIn: 'scale-in 0.4s ease-out forwards',
  pulseSubtle: 'pulse-subtle 2s ease-in-out infinite',
  bounceSubtle: 'bounce 1s infinite',
  wiggle: 'wiggle 1s ease-in-out infinite',
};

const designTokens = {
  colors,
  spacing,
  borderRadius,
  boxShadow,
  transitions,
  containers,
  animations,
};

export default designTokens;