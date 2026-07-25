import type { ReactNode } from 'react';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import { cn } from '@/utils/cn';

interface RevealProps {
  children: ReactNode;
  delay?: number;
  className?: string;
  as?: 'div' | 'section';
}

export function Reveal({ children, delay = 0, className, as = 'div' }: RevealProps) {
  const { ref, isVisible } = useScrollReveal();
  const Tag = as;

  return (
    <Tag
      ref={ref as never}
      className={cn('reveal-on-scroll', isVisible && 'is-visible', className)}
      style={{ transitionDelay: isVisible ? `${delay}ms` : '0ms' }}
    >
      {children}
    </Tag>
  );
}
