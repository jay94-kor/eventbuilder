/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      // ==========================================================================
      // 색상 시스템
      // ==========================================================================
      colors: {
        border: "oklch(var(--border))",
        input: "oklch(var(--input))",
        ring: "oklch(var(--ring))",
        background: "oklch(var(--background))",
        foreground: "oklch(var(--foreground))",
        primary: {
          DEFAULT: "oklch(var(--primary) / <alpha-value>)",
          foreground: "oklch(var(--primary-foreground) / <alpha-value>)",
        },
        secondary: {
          DEFAULT: "oklch(var(--secondary) / <alpha-value>)",
          foreground: "oklch(var(--secondary-foreground) / <alpha-value>)",
        },
        muted: {
          DEFAULT: "oklch(var(--muted) / <alpha-value>)",
          foreground: "oklch(var(--muted-foreground) / <alpha-value>)",
        },
        accent: {
          DEFAULT: "oklch(var(--accent) / <alpha-value>)",
          foreground: "oklch(var(--accent-foreground) / <alpha-value>)",
        },
        destructive: {
          DEFAULT: "oklch(var(--destructive) / <alpha-value>)",
          foreground: "oklch(var(--destructive-foreground) / <alpha-value>)",
        },
        card: {
          DEFAULT: "oklch(var(--card) / <alpha-value>)",
          foreground: "oklch(var(--card-foreground) / <alpha-value>)",
        },
        popover: {
          DEFAULT: "oklch(var(--popover) / <alpha-value>)",
          foreground: "oklch(var(--popover-foreground) / <alpha-value>)",
        },
        sidebar: {
          DEFAULT: "oklch(var(--sidebar) / <alpha-value>)",
          foreground: "oklch(var(--sidebar-foreground) / <alpha-value>)",
          primary: {
            DEFAULT: "oklch(var(--sidebar-primary) / <alpha-value>)",
            foreground: "oklch(var(--sidebar-primary-foreground) / <alpha-value>)",
          },
          accent: {
            DEFAULT: "oklch(var(--sidebar-accent) / <alpha-value>)",
            foreground: "oklch(var(--sidebar-accent-foreground) / <alpha-value>)",
          },
          border: "oklch(var(--sidebar-border) / <alpha-value>)",
          ring: "oklch(var(--sidebar-ring) / <alpha-value>)",
        },
        // 확장 색상 팔레트
        success: {
          DEFAULT: "oklch(var(--success) / <alpha-value>)",
          foreground: "oklch(var(--success-foreground) / <alpha-value>)",
        },
        warning: {
          DEFAULT: "oklch(var(--warning) / <alpha-value>)",
          foreground: "oklch(var(--warning-foreground) / <alpha-value>)",
        },
        info: {
          DEFAULT: "oklch(var(--info) / <alpha-value>)",
          foreground: "oklch(var(--info-foreground) / <alpha-value>)",
        },
        // 차트 색상
        chart: {
          1: "oklch(var(--chart-1) / <alpha-value>)",
          2: "oklch(var(--chart-2) / <alpha-value>)",
          3: "oklch(var(--chart-3) / <alpha-value>)",
          4: "oklch(var(--chart-4) / <alpha-value>)",
          5: "oklch(var(--chart-5) / <alpha-value>)",
          6: "oklch(var(--chart-6) / <alpha-value>)",
          7: "oklch(var(--chart-7) / <alpha-value>)",
          8: "oklch(var(--chart-8) / <alpha-value>)",
          9: "oklch(var(--chart-9) / <alpha-value>)",
          10: "oklch(var(--chart-10) / <alpha-value>)",
        },
        // 브랜드 색상 별칭
        brand: {
          DEFAULT: "oklch(var(--primary) / <alpha-value>)",
          foreground: "oklch(var(--primary-foreground) / <alpha-value>)",
          light: "oklch(var(--primary) / 0.1)",
          medium: "oklch(var(--primary) / 0.2)",
          strong: "oklch(var(--primary) / 0.3)",
        },
      },
      
      // ==========================================================================
      // 타이포그래피 시스템
      // ==========================================================================
      fontFamily: {
        sans: ['var(--font-geist-sans)', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['var(--font-geist-mono)', 'ui-monospace', 'monospace'],
      },
      fontSize: {
        // 기본 폰트 사이즈
        'xs': ['0.75rem', { lineHeight: '1.4' }],
        'sm': ['0.875rem', { lineHeight: '1.5' }],
        'base': ['1rem', { lineHeight: '1.6' }],
        'lg': ['1.125rem', { lineHeight: '1.6' }],
        'xl': ['1.25rem', { lineHeight: '1.4' }],
        '2xl': ['1.5rem', { lineHeight: '1.3' }],
        '3xl': ['1.875rem', { lineHeight: '1.3' }],
        '4xl': ['2.25rem', { lineHeight: '1.2' }],
        '5xl': ['3rem', { lineHeight: '1.1' }],
        '6xl': ['3.75rem', { lineHeight: '1.1' }],
        // 디스플레이 텍스트
        'display-sm': ['2rem', { lineHeight: '1.3', fontWeight: '700', letterSpacing: '-0.025em' }],
        'display-md': ['3rem', { lineHeight: '1.2', fontWeight: '700', letterSpacing: '-0.025em' }],
        'display-lg': ['4rem', { lineHeight: '1.2', fontWeight: '700', letterSpacing: '-0.025em' }],
        'display-xl': ['5rem', { lineHeight: '1.1', fontWeight: '700', letterSpacing: '-0.025em' }],
        'display-2xl': ['6rem', { lineHeight: '1.1', fontWeight: '700', letterSpacing: '-0.025em' }],
        // 본문 텍스트
        'body-xs': ['0.75rem', { lineHeight: '1.4' }],
        'body-sm': ['0.875rem', { lineHeight: '1.5' }],
        'body-md': ['1rem', { lineHeight: '1.6' }],
        'body-lg': ['1.125rem', { lineHeight: '1.6' }],
        // 라벨 텍스트
        'label-sm': ['0.75rem', { lineHeight: '1.3', fontWeight: '500' }],
        'label-md': ['0.875rem', { lineHeight: '1.4', fontWeight: '500' }],
        'label-lg': ['0.875rem', { lineHeight: '1.4', fontWeight: '500' }],
      },
      fontWeight: {
        thin: '100',
        extralight: '200',
        light: '300',
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
        extrabold: '800',
        black: '900',
      },
      letterSpacing: {
        tighter: '-0.05em',
        tight: '-0.025em',
        normal: '0em',
        wide: '0.025em',
        wider: '0.05em',
        widest: '0.1em',
      },
      
      // ==========================================================================
      // 스페이싱 시스템
      // ==========================================================================
      spacing: {
        'xs': 'var(--spacing-xs)',    // 4px
        'sm': 'var(--spacing-sm)',    // 8px
        'md': 'var(--spacing-md)',    // 12px
        'lg': 'var(--spacing-lg)',    // 16px
        'xl': 'var(--spacing-xl)',    // 20px
        '2xl': 'var(--spacing-2xl)',  // 24px
        '3xl': 'var(--spacing-3xl)',  // 32px
        '4xl': 'var(--spacing-4xl)',  // 40px
        '5xl': 'var(--spacing-5xl)',  // 48px
        '6xl': 'var(--spacing-6xl)',  // 64px
      },
      
      // ==========================================================================
      // 반지름 시스템
      // ==========================================================================
      borderRadius: {
        'none': '0',
        'sm': 'var(--radius-sm)',
        DEFAULT: 'var(--radius)',
        'md': 'var(--radius-md)',
        'lg': 'var(--radius-lg)',
        'xl': 'var(--radius-xl)',
        '2xl': 'var(--radius-2xl)',
        'full': '9999px',
      },
      
      // ==========================================================================
      // 그림자 시스템
      // ==========================================================================
      boxShadow: {
        'xs': 'var(--shadow-xs)',
        'sm': 'var(--shadow-sm)',
        DEFAULT: 'var(--shadow-md)',
        'md': 'var(--shadow-md)',
        'lg': 'var(--shadow-lg)',
        'xl': 'var(--shadow-xl)',
        '2xl': 'var(--shadow-2xl)',
        'brand': 'var(--shadow-brand)',
        'brand-strong': 'var(--shadow-brand-strong)',
        'elevated': 'var(--shadow-elevated)',
        'success': '0 10px 15px -3px oklch(var(--success) / 0.2), 0 4px 6px -2px oklch(var(--success) / 0.1)',
        'warning': '0 10px 15px -3px oklch(var(--warning) / 0.2), 0 4px 6px -2px oklch(var(--warning) / 0.1)',
        'destructive': '0 10px 15px -3px oklch(var(--destructive) / 0.2), 0 4px 6px -2px oklch(var(--destructive) / 0.1)',
        'inner': 'inset 0 2px 4px 0 rgb(0 0 0 / 0.05)',
        'none': 'none',
      },
      
      // ==========================================================================
      // 컨테이너 시스템
      // ==========================================================================
      container: {
        center: true,
        padding: {
          DEFAULT: '1rem',
          sm: '1.5rem',
          lg: '2rem',
        },
        screens: {
          'xs': '480px',
          'sm': '640px',
          'md': '768px',
          'lg': '1024px',
          'xl': '1280px',
          '2xl': '1400px',
        },
      },
      maxWidth: {
        'xs': 'var(--container-xs)',
        'sm': 'var(--container-sm)',
        'md': 'var(--container-md)',
        'lg': 'var(--container-lg)',
        'xl': 'var(--container-xl)',
        '2xl': 'var(--container-2xl)',
        'brand': 'var(--container-brand)',
      },
      
      // ==========================================================================
      // 트랜지션 시스템
      // ==========================================================================
      transitionDuration: {
        'fast': '150ms',
        'base': '200ms',
        'smooth': '300ms',
        'slow': '500ms',
      },
      transitionTimingFunction: {
        'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
        'bounce': 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
        'ease-in-out-back': 'cubic-bezier(0.68, -0.6, 0.32, 1.6)',
      },
      animation: {
        'fade-in-up': 'fade-in-up 0.6s ease-out forwards',
        'fade-in-down': 'fade-in-down 0.6s ease-out forwards',
        'fade-in-left': 'fade-in-left 0.6s ease-out forwards',
        'fade-in-right': 'fade-in-right 0.6s ease-out forwards',
        'scale-in': 'scale-in 0.4s ease-out forwards',
        'pulse-subtle': 'pulse-subtle 2s ease-in-out infinite',
        'bounce-subtle': 'bounce 1s infinite',
        'wiggle': 'wiggle 1s ease-in-out infinite',
      },
      keyframes: {
        'fade-in-up': {
          '0%': {
            opacity: '0',
            transform: 'translateY(20px)',
          },
          '100%': {
            opacity: '1',
            transform: 'translateY(0)',
          },
        },
        'fade-in-down': {
          '0%': {
            opacity: '0',
            transform: 'translateY(-20px)',
          },
          '100%': {
            opacity: '1',
            transform: 'translateY(0)',
          },
        },
        'fade-in-left': {
          '0%': {
            opacity: '0',
            transform: 'translateX(-20px)',
          },
          '100%': {
            opacity: '1',
            transform: 'translateX(0)',
          },
        },
        'fade-in-right': {
          '0%': {
            opacity: '0',
            transform: 'translateX(20px)',
          },
          '100%': {
            opacity: '1',
            transform: 'translateX(0)',
          },
        },
        'scale-in': {
          '0%': {
            opacity: '0',
            transform: 'scale(0.95)',
          },
          '100%': {
            opacity: '1',
            transform: 'scale(1)',
          },
        },
        'pulse-subtle': {
          '0%, 100%': {
            opacity: '1',
          },
          '50%': {
            opacity: '0.8',
          },
        },
        'wiggle': {
          '0%, 100%': {
            transform: 'rotate(-3deg)',
          },
          '50%': {
            transform: 'rotate(3deg)',
          },
        },
      },
      
      // ==========================================================================
      // 백드롭 및 필터
      // ==========================================================================
      backdropBlur: {
        'xs': '2px',
        'sm': '4px',
        DEFAULT: '8px',
        'md': '12px',
        'lg': '16px',
        'xl': '24px',
        '2xl': '40px',
        '3xl': '64px',
      },
      
      // ==========================================================================
      // 그라데이션 배경
      // ==========================================================================
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'gradient-conic': 'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
        'gradient-brand': 'linear-gradient(135deg, oklch(var(--primary) / 0.1) 0%, oklch(var(--accent) / 0.05) 50%, oklch(var(--secondary) / 0.1) 100%)',
        'gradient-hero': 'linear-gradient(135deg, oklch(var(--background)) 0%, oklch(var(--primary) / 0.05) 50%, oklch(var(--accent) / 0.1) 100%)',
        'gradient-card': 'linear-gradient(135deg, oklch(var(--card)) 0%, oklch(var(--card) / 0.8) 100%)',
        'gradient-primary': 'linear-gradient(135deg, oklch(var(--primary)) 0%, oklch(var(--primary) / 0.8) 100%)',
        'gradient-success': 'linear-gradient(135deg, oklch(var(--success)) 0%, oklch(var(--success) / 0.8) 100%)',
        'gradient-warning': 'linear-gradient(135deg, oklch(var(--warning)) 0%, oklch(var(--warning) / 0.8) 100%)',
        'gradient-destructive': 'linear-gradient(135deg, oklch(var(--destructive)) 0%, oklch(var(--destructive) / 0.8) 100%)',
      },
      
      // ==========================================================================
      // Z-Index 시스템
      // ==========================================================================
      zIndex: {
        'hide': '-1',
        'auto': 'auto',
        'base': '0',
        'docked': '10',
        'dropdown': '1000',
        'sticky': '1100',
        'banner': '1200',
        'overlay': '1300',
        'modal': '1400',
        'popover': '1500',
        'skipLink': '1600',
        'toast': '1700',
        'tooltip': '1800',
      },
      
      // ==========================================================================
      // 반응형 브레이크포인트
      // ==========================================================================
      screens: {
        'xs': '480px',
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        '2xl': '1536px',
        '3xl': '1920px',
      },
      
      // ==========================================================================
      // 추가 유틸리티
      // ==========================================================================
      aspectRatio: {
        'auto': 'auto',
        'square': '1 / 1',
        'video': '16 / 9',
        'golden': '1.618 / 1',
        '4/3': '4 / 3',
        '3/2': '3 / 2',
        '2/1': '2 / 1',
      },
      scale: {
        '102': '1.02',
        '103': '1.03',
        '98': '0.98',
        '97': '0.97',
      },
    },
  },
  plugins: [
    // 필요시 추가 플러그인을 여기에 추가
    // require('@tailwindcss/typography'),
    // require('@tailwindcss/forms'),
    // require('@tailwindcss/aspect-ratio'),
  ],
};