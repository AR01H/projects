import { cn } from '@/utils/format';

const VARIANTS: Record<string, string> = {
  success: 'bg-green-100 text-green-700',
  warning: 'bg-yellow-100 text-yellow-700',
  danger: 'bg-red-100 text-red-700',
  info: 'bg-blue-100 text-blue-700',
  neutral: 'bg-gray-100 text-gray-700',
  primary: 'bg-indigo-100 text-indigo-700',
};

export function Badge({ children, variant = 'neutral' }: { children: React.ReactNode; variant?: string }) {
  return (
    <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', VARIANTS[variant] || VARIANTS.neutral)}>
      {children}
    </span>
  );
}
