import { useEffect, useState } from 'react';
import { bannersApi } from '@/api/banners';
import { SITE_CONFIG } from '@/config/site';
import type { Banner } from '@/types/product';

interface PageHeroProps {
  pageKey: string;
  fallbackTitle?: string;
}

export function PageHero({ pageKey, fallbackTitle }: PageHeroProps) {
  const [banner, setBanner] = useState<Banner | null | undefined>(undefined);

  useEffect(() => {
    bannersApi.getPageHero(pageKey).then((b) => setBanner(b ?? null));
  }, [pageKey]);

  if (banner === undefined) {
    return <div className="aspect-[3/1] w-full animate-pulse bg-[var(--color-bg-cream)]" />;
  }

  if (banner === null) {
    return (
      <div className="flex aspect-[4/1] w-full items-center justify-center bg-[var(--color-dark)]">
        <h1 className="font-display text-3xl text-[var(--color-bg-light)] sm:text-4xl">
          {fallbackTitle}
        </h1>
      </div>
    );
  }

  return (
    <section className="relative aspect-[16/7] w-full overflow-hidden sm:aspect-[21/6]">
      <img src={banner.image} alt={banner.title} className="h-full w-full object-cover" />
      <div
        className={`absolute inset-0 ${
          banner.theme === 'dark'
            ? 'bg-gradient-to-t from-[var(--color-dark)]/85 via-[var(--color-dark)]/40 to-[var(--color-dark)]/10'
            : 'bg-gradient-to-t from-[var(--color-dark)]/70 via-[var(--color-dark)]/20 to-transparent'
        }`}
      />
      <div className="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
        <p className="mb-2 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--color-secondary)]">
          {SITE_CONFIG.story.heroEyebrow}
        </p>
        <h1 className="font-display text-3xl text-[var(--color-bg-light)] sm:text-4xl lg:text-5xl">
          {banner.title}
        </h1>
        {banner.subtitle && (
          <p className="mt-3 max-w-lg text-sm text-[var(--color-bg-light)]/85 sm:text-base">
            {banner.subtitle}
          </p>
        )}
      </div>
    </section>
  );
}
