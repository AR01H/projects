import type { ButtonHTMLAttributes, ReactNode } from 'react';
import { cn } from '@/utils/cn';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'dark';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
  icon?: ReactNode;
  fullWidth?: boolean;
}

const VARIANT_STYLES: Record<string, string> = {
  primary:
    'bg-[var(--color-primary)] text-[var(--color-bg-light)] hover:bg-[var(--color-primary-dark)] shadow-[var(--shadow-card)] hover:shadow-[var(--shadow-hover)]',
  secondary:
    'bg-[var(--color-secondary)] text-[var(--color-dark)] hover:bg-[var(--color-secondary-dark)] shadow-[var(--shadow-gold)]',
  outline:
    'bg-transparent border border-[var(--color-primary)] text-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-[var(--color-bg-light)]',
  ghost: 'bg-transparent text-[var(--color-text-primary)] hover:bg-[var(--color-bg-cream)]',
  dark: 'bg-[var(--color-dark)] text-[var(--color-bg-light)] hover:bg-[var(--color-dark-soft)]',
};

const SIZE_STYLES: Record<string, string> = {
  sm: 'px-4 py-2 text-sm',
  md: 'px-6 py-3 text-sm',
  lg: 'px-8 py-4 text-base',
};

export function Button({
  variant = 'primary',
  size = 'md',
  isLoading,
  icon,
  fullWidth,
  className,
  children,
  disabled,
  ...props
}: ButtonProps) {
  return (
    <button
      className={cn(
        'inline-flex items-center justify-center gap-2 rounded-[var(--radius-btn)] font-medium tracking-wide transition-all duration-300 ease-out disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]',
        VARIANT_STYLES[variant],
        SIZE_STYLES[size],
        fullWidth && 'w-full',
        className
      )}
      style={{ fontFamily: 'var(--font-sans)' }}
      disabled={disabled || isLoading}
      {...props}
    >
      {isLoading ? (
        <span className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
      ) : (
        icon
      )}
      {children}
    </button>
  );
}
