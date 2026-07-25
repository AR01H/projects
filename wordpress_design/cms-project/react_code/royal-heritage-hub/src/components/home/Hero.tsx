import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { bannersApi } from '@/api/banners';
import { Button } from '@/components/common/Button';
import { SITE_CONFIG } from '@/config/site';
import type { Banner } from '@/types/product';

export function Hero() {
  const [banners, setBanners] = useState<Banner[]>([]);
  const [active, setActive] = useState(0);
  const [loaded, setLoaded] = useState<Record<string, boolean>>({});

  useEffect(() => {
    bannersApi.getHero().then(setBanners);
  }, []);

  useEffect(() => {
    if (banners.length < 2) return;
    const t = setInterval(() => setActive((a) => (a + 1) % banners.length), 6000);
    return () => clearInterval(t);
  }, [banners.length]);

  if (banners.length === 0) {
    return <div className="aspect-[4/5] w-full animate-pulse bg-[var(--color-bg-cream)] sm:aspect-[16/9] lg:aspect-[21/9]" />;
  }

  const banner = banners[active];

  return (
    <section className="relative overflow-hidden bg-[var(--color-dark)]">
      <div className="relative aspect-[4/5] w-full sm:aspect-[16/9] lg:aspect-[21/9]">
        {banners.map((b, i) => (
          <img
            key={b.id}
            src={b.image}
            alt={b.title}
            loading={i === 0 ? 'eager' : 'lazy'}
            onLoad={() => setLoaded((prev) => ({ ...prev, [b.id]: true }))}
            className={`absolute inset-0 h-full w-full object-cover transition-opacity duration-[1200ms] ease-in-out ${
              i === active ? 'opacity-100' : 'opacity-0'
            }`}
          />
        ))}

        {/* Solid gradient fallback so text stays legible even before/if an image loads */}
        <div className="absolute inset-0 bg-gradient-to-br from-[var(--color-primary-dark)] via-[var(--color-dark)] to-[var(--color-dark-soft)]" />

        {banners.map((b, i) =>
          loaded[b.id] ? (
            <img
              key={`loaded-${b.id}`}
              src={b.image}
              alt=""
              aria-hidden
              className={`absolute inset-0 h-full w-full object-cover transition-opacity duration-[1200ms] ease-in-out ${
                i === active ? 'opacity-100' : 'opacity-0'
              }`}
            />
          ) : null
        )}

        <div
          className={`absolute inset-0 ${
            banner.theme === 'dark'
              ? 'bg-gradient-to-t from-[var(--color-dark)]/90 via-[var(--color-dark)]/40 to-[var(--color-dark)]/10'
              : 'bg-gradient-to-t from-[var(--color-dark)]/80 via-[var(--color-dark)]/25 to-transparent'
          }`}
        />

        <div className="absolute inset-0 flex flex-col items-start justify-end px-6 pb-14 sm:px-12 sm:pb-20 lg:px-20">
          <p className="mb-3 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--color-secondary)]">
            {SITE_CONFIG.story.heroEyebrow}
          </p>
          <h1 className="max-w-xl font-display text-4xl leading-[1.05] text-[var(--color-bg-light)] sm:text-5xl lg:text-6xl">
            {banner.title}
          </h1>
          <p className="mt-4 max-w-md text-sm text-[var(--color-bg-light)]/90 sm:text-base">
            {banner.subtitle}
          </p>
          <div className="mt-7">
            <Link to={banner.ctaLink}>
              <Button variant="secondary" size="lg">
                {banner.ctaLabel}
              </Button>
            </Link>
          </div>
        </div>

        <div className="absolute bottom-6 right-6 flex gap-2 sm:right-12">
          {banners.map((b, i) => (
            <button
              key={b.id}
              onClick={() => setActive(i)}
              aria-label={`Go to slide ${i + 1}`}
              className={`h-1.5 rounded-full transition-all duration-300 ${
                i === active ? 'w-8 bg-[var(--color-secondary)]' : 'w-1.5 bg-[var(--color-bg-light)]/50'
              }`}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
