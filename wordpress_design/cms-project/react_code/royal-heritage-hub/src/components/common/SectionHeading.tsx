import { Link } from 'react-router-dom';
import { Reveal } from './Reveal';

interface SectionHeadingProps {
  eyebrow?: string;
  title: string;
  description?: string;
  viewAllLink?: string;
  align?: 'left' | 'center';
}

export function SectionHeading({
  eyebrow,
  title,
  description,
  viewAllLink,
  align = 'left',
}: SectionHeadingProps) {
  return (
    <Reveal
      className={`mb-8 flex flex-col gap-2 ${align === 'center' ? 'items-center text-center' : 'items-start justify-between sm:flex-row sm:items-end'}`}
    >
      <div className={align === 'center' ? 'max-w-xl' : ''}>
        {eyebrow && (
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            {eyebrow}
          </p>
        )}
        <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">{title}</h2>
        {description && (
          <p className="mt-2 text-sm text-[var(--color-text-secondary)]">{description}</p>
        )}
      </div>
      {viewAllLink && (
        <Link
          to={viewAllLink}
          className="whitespace-nowrap text-sm font-medium text-[var(--color-primary)] underline decoration-[var(--color-secondary)] decoration-2 underline-offset-4 transition-opacity hover:opacity-70"
        >
          View All →
        </Link>
      )}
    </Reveal>
  );
}
