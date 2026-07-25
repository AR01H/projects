import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Button } from '@/components/common/Button';
import { ROUTES } from '@/config/routes';
import { SEO } from '@/components/common/SEO';

const POPULAR_LINKS = [
  { label: 'Shop All', href: ROUTES.shop },
  { label: 'Best Sellers', href: `${ROUTES.shop}?sort=best-selling` },
  { label: 'New Arrivals', href: `${ROUTES.shop}?sort=newest` },
  { label: 'Collections', href: ROUTES.collections },
  { label: 'About Us', href: ROUTES.about },
  { label: 'Contact', href: ROUTES.contact },
  { label: 'FAQs', href: ROUTES.faqs },
];

export default function NotFoundPage() {
  const [query, setQuery] = useState('');
  const navigate = useNavigate();

  function handleSearch(e: React.FormEvent) {
    e.preventDefault();
    if (query.trim()) {
      navigate(`${ROUTES.shop}?search=${encodeURIComponent(query.trim())}`);
    }
  }

  return (
    <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:py-32">
      <SEO title="Page Not Found" description="The page you're looking for doesn't exist. Try searching or explore our popular pages." />
      <p className="font-display text-7xl text-[var(--color-secondary)]/30">404</p>
      <h1 className="mt-2 font-display text-3xl text-[var(--color-text-primary)]">Page Not Found</h1>
      <p className="mt-3 text-sm text-[var(--color-text-secondary)]">
        The page you're looking for may have been moved or doesn't exist.
        Try searching for what you need, or explore our popular pages below.
      </p>

      {/* Search bar */}
      <form onSubmit={handleSearch} className="mx-auto mt-8 flex max-w-md gap-3">
        <input
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Search for products..."
          className="w-full rounded-[var(--radius-btn)] border border-[var(--color-border)] bg-[var(--color-bg-cream)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />
        <Button type="submit" variant="primary">
          Search
        </Button>
      </form>

      {/* Popular links */}
      <div className="mt-10">
        <p className="mb-4 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
          Popular Pages
        </p>
        <div className="flex flex-wrap justify-center gap-2">
          {POPULAR_LINKS.map((link) => (
            <Link
              key={link.href}
              to={link.href}
              className="rounded-full border border-[var(--color-border)] bg-[var(--color-bg-light)] px-4 py-2 text-xs font-medium text-[var(--color-text-secondary)] transition-colors hover:border-[var(--color-primary)] hover:text-[var(--color-primary)]"
            >
              {link.label}
            </Link>
          ))}
        </div>
      </div>

      {/* Go home */}
      <Link to={ROUTES.home}>
        <Button variant="primary" className="mt-10">
          Back to Home
        </Button>
      </Link>
    </div>
  );
}
