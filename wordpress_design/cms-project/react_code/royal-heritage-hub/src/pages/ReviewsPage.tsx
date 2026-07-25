import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { reviewsApi, type AggregatedReview } from '@/api/reviews';
import { PageHero } from '@/components/common/PageHero';
import { Rating } from '@/components/common/Rating';
import { Reveal } from '@/components/common/Reveal';
import { EmptyState } from '@/components/common/EmptyState';
import { buildRoute, ROUTES } from '@/config/routes';

export default function ReviewsPage() {
  const [reviews, setReviews] = useState<AggregatedReview[] | null>(null);
  const [stats, setStats] = useState<Awaited<ReturnType<typeof reviewsApi.getStats>> | null>(null);
  const [starFilter, setStarFilter] = useState<number | null>(null);

  useEffect(() => {
    reviewsApi.getAll().then(setReviews);
    reviewsApi.getStats().then(setStats);
  }, []);

  const filtered = reviews?.filter((r) => (starFilter ? Math.round(r.rating) === starFilter : true)) ?? [];

  return (
    <div>
      <PageHero pageKey="faqs" fallbackTitle="Customer Reviews" />

      <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        {stats && (
          <Reveal className="mb-10 flex flex-col items-center gap-4 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-8 text-center sm:flex-row sm:text-left">
            <div className="flex flex-col items-center sm:items-start">
              <span className="font-display text-5xl text-[var(--color-primary)]">
                {stats.avgRating.toFixed(1)}
              </span>
              <Rating value={stats.avgRating} size="md" />
              <p className="mt-1 text-xs text-[var(--color-text-muted)]">{stats.totalReviews} reviews</p>
            </div>
            <div className="flex-1 border-t border-[var(--color-border)] pt-4 sm:border-l sm:border-t-0 sm:pl-8 sm:pt-0">
              {stats.distribution.map((d) => (
                <button
                  key={d.star}
                  onClick={() => setStarFilter(starFilter === d.star ? null : d.star)}
                  className="mb-1.5 flex w-full items-center gap-3 text-xs"
                >
                  <span className="w-10 text-[var(--color-text-secondary)]">{d.star} star</span>
                  <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-[var(--color-border-soft)]">
                    <div
                      className="h-full rounded-full bg-[var(--color-secondary)] transition-all duration-500"
                      style={{ width: `${stats.totalReviews ? (d.count / stats.totalReviews) * 100 : 0}%` }}
                    />
                  </div>
                  <span className="w-6 text-[var(--color-text-muted)]">{d.count}</span>
                </button>
              ))}
            </div>
          </Reveal>
        )}

        {starFilter && (
          <button
            onClick={() => setStarFilter(null)}
            className="mb-6 text-xs font-medium text-[var(--color-primary)] underline"
          >
            Clear {starFilter}-star filter
          </button>
        )}

        {reviews === null ? (
          <p className="text-center text-sm text-[var(--color-text-muted)]">Loading reviews...</p>
        ) : filtered.length === 0 ? (
          <EmptyState title="No reviews yet" description="Be the first to leave one on any product page." />
        ) : (
          <div className="flex flex-col divide-y divide-[var(--color-border)]">
            {filtered.map((r, i) => (
              <Reveal key={r.id} delay={Math.min(i * 40, 300)} className="flex gap-4 py-6">
                <Link to={buildRoute(ROUTES.product, { productSlug: r.product.slug })} className="flex-shrink-0">
                  <img
                    src={r.product.thumbnail}
                    alt={r.product.name}
                    className="h-16 w-14 rounded-[var(--radius-btn)] object-cover"
                  />
                </Link>
                <div className="flex-1">
                  <div className="flex items-center justify-between gap-2">
                    <Rating value={r.rating} />
                    {r.verified && (
                      <span className="text-[0.65rem] font-semibold uppercase text-[var(--color-success)]">
                        Verified Purchase
                      </span>
                    )}
                  </div>
                  <p className="mt-1.5 font-display text-sm text-[var(--color-text-primary)]">{r.title}</p>
                  <p className="mt-1 text-sm text-[var(--color-text-secondary)]">{r.comment}</p>
                  <div className="mt-2 flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                    <span>{r.author}</span>
                    <span>·</span>
                    <span>{r.date}</span>
                    <span>·</span>
                    <Link
                      to={buildRoute(ROUTES.product, { productSlug: r.product.slug })}
                      className="text-[var(--color-primary)] underline"
                    >
                      {r.product.name}
                    </Link>
                  </div>
                </div>
              </Reveal>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
