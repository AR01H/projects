import type { ReactNode } from 'react';

interface EmptyStateProps {
  icon?: ReactNode;
  title: string;
  description?: string;
  action?: ReactNode;
}

export function EmptyState({ icon, title, description, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center justify-center gap-4 py-24 text-center">
      {icon && <div className="text-[var(--color-secondary)] opacity-70">{icon}</div>}
      <h3 className="font-display text-2xl text-[var(--color-text-primary)]">{title}</h3>
      {description && (
        <p className="max-w-sm text-sm text-[var(--color-text-secondary)]">{description}</p>
      )}
      {action}
    </div>
  );
}
