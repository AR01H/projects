import { Link } from 'react-router-dom';
import type { Banner } from '@/types/product';

export function MiniPromoBanner({ banner }: { banner: Banner }) {
  return (
    <section className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <Link
        to={banner.ctaLink}
        className="group relative flex h-32 items-center overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-soft)] sm:h-40"
      >
        <img
          src={banner.image}
          alt={banner.title}
          loading="lazy"
          className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
        />
        <div
          className={`absolute inset-0 ${
            banner.theme === 'dark'
              ? 'bg-gradient-to-r from-[var(--color-dark)]/90 via-[var(--color-dark)]/50 to-transparent'
              : 'bg-gradient-to-r from-[var(--color-bg-light)]/90 via-[var(--color-bg-light)]/40 to-transparent'
          }`}
        />
        <div className="relative z-10 px-6 sm:px-10">
          <h3
            className={`font-display text-xl sm:text-2xl ${
              banner.theme === 'dark' ? 'text-[var(--color-bg-light)]' : 'text-[var(--color-text-primary)]'
            }`}
          >
            {banner.title}
          </h3>
          <p
            className={`mt-1 max-w-xs text-xs sm:text-sm ${
              banner.theme === 'dark' ? 'text-[var(--color-bg-light)]/75' : 'text-[var(--color-text-secondary)]'
            }`}
          >
            {banner.subtitle}
          </p>
          <span
            className={`mt-3 inline-block text-xs font-semibold uppercase tracking-wider underline decoration-[var(--color-secondary)] decoration-2 underline-offset-4 ${
              banner.theme === 'dark' ? 'text-[var(--color-secondary)]' : 'text-[var(--color-primary)]'
            }`}
          >
            {banner.ctaLabel} →
          </span>
        </div>
      </Link>
    </section>
  );
}
