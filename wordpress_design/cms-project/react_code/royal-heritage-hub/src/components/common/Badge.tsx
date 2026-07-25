import type { ReactNode } from 'react';
import { cn } from '@/utils/cn';

interface BadgeProps {
  children: ReactNode;
  variant?: 'gold' | 'primary' | 'dark' | 'success' | 'danger' | 'outline';
  className?: string;
}

const STYLES: Record<string, string> = {
  gold: 'bg-[var(--color-secondary)] text-[var(--color-dark)]',
  primary: 'bg-[var(--color-primary)] text-[var(--color-bg-light)]',
  dark: 'bg-[var(--color-dark)] text-[var(--color-bg-light)]',
  success: 'bg-[var(--color-success)] text-[var(--color-bg-light)]',
  danger: 'bg-[var(--color-danger)] text-[var(--color-bg-light)]',
  outline: 'bg-[var(--color-bg-light)]/90 text-[var(--color-dark)] border border-[var(--color-border)]',
};

export function Badge({ children, variant = 'primary', className }: BadgeProps) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-[var(--radius-pill)] px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-wider',
        STYLES[variant],
        className
      )}
    >
      {children}
    </span>
  );
}
