import { Link } from 'react-router-dom';
import { cn } from '@/utils/cn';

interface TagPillProps {
  tag: string;
  label?: string;
  size?: 'xs' | 'sm';
  variant?: 'outline' | 'filled';
  onClick?: () => void;
}

function formatTagLabel(tag: string): string {
  return tag
    .split('-')
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ');
}

export function TagPill({ tag, label, size = 'sm', variant = 'outline', onClick }: TagPillProps) {
  const content = (
    <span
      className={cn(
        'inline-flex items-center rounded-[var(--radius-pill)] border transition-all duration-200',
        size === 'xs' ? 'px-2.5 py-1 text-[0.65rem]' : 'px-3.5 py-1.5 text-xs',
        variant === 'outline'
          ? 'border-[var(--color-border)] bg-[var(--color-bg-light)] text-[var(--color-text-secondary)] hover:border-[var(--color-secondary)] hover:text-[var(--color-primary)]'
          : 'border-transparent bg-[var(--color-bg-cream)] text-[var(--color-primary)] hover:bg-[var(--color-secondary)] hover:text-[var(--color-dark)]'
      )}
    >
      #{label ?? formatTagLabel(tag)}
    </span>
  );

  if (onClick) {
    return (
      <button type="button" onClick={onClick}>
        {content}
      </button>
    );
  }

  return <Link to={`/tags/${tag}`}>{content}</Link>;
}
