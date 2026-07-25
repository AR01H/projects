import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { APP_NAME } from '@/config/constants';
import { SITE_CONFIG } from '@/config/site';
import { ROUTES } from '@/config/routes';
import { useCartStore } from '@/store/useCartStore';
import { useWishlistStore } from '@/store/useWishlistStore';
import { CurrencySwitcher } from './CurrencySwitcher';
import { BlogNavDropdown } from './BlogNavDropdown';
import { cn } from '@/utils/cn';

const NAV_LINKS = [
  { label: 'Shop', href: ROUTES.shop },
  { label: 'Categories', href: ROUTES.categories },
  { label: 'Collections', href: ROUTES.collections },
  { label: 'About', href: ROUTES.about },
  { label: 'Contact', href: ROUTES.contact },
];

export function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [query, setQuery] = useState('');
  const navigate = useNavigate();

  const itemCount = useCartStore((s) => s.itemCount());
  const toggleCart = useCartStore((s) => s.toggleCart);
  const wishlistCount = useWishlistStore((s) => s.items.length);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  function handleSearchSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (query.trim()) {
      navigate(`${ROUTES.shop}?search=${encodeURIComponent(query.trim())}`);
      setSearchOpen(false);
      setQuery('');
    }
  }

  return (
    <header
      className={cn(
        'sticky top-0 z-40 w-full transition-all duration-300',
        scrolled
          ? 'bg-[var(--color-bg-light)]/95 shadow-[var(--shadow-soft)] backdrop-blur-md'
          : 'bg-[var(--color-bg-light)]'
      )}
    >
      {/* Top strip */}
      <div className="hidden bg-[var(--color-dark)] px-4 py-2 text-center text-xs text-[var(--color-bg-light)]/85 sm:block">
        {SITE_CONFIG.microcopy.announcementStrip}
      </div>

      <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <button
          className="lg:hidden"
          aria-label="Open menu"
          onClick={() => setMobileOpen(true)}
        >
          <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
            <path d="M3 6h18M3 12h18M3 18h18" strokeLinecap="round" />
          </svg>
        </button>

        <Link to={ROUTES.home} className="flex items-center gap-2">
          <span className="font-display text-xl tracking-wide text-[var(--color-primary)] sm:text-2xl">
            {APP_NAME}
          </span>
        </Link>

        <nav className="hidden items-center gap-8 lg:flex">
          {NAV_LINKS.slice(0, 3).map((link) => (
            <Link
              key={link.href}
              to={link.href}
              className="text-sm font-medium tracking-wide text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
            >
              {link.label}
            </Link>
          ))}
          <BlogNavDropdown />
          {NAV_LINKS.slice(3).map((link) => (
            <Link
              key={link.href}
              to={link.href}
              className="text-sm font-medium tracking-wide text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
            >
              {link.label}
            </Link>
          ))}
        </nav>

        <div className="flex items-center gap-4 sm:gap-5">
          <div className="hidden sm:block">
            <CurrencySwitcher />
          </div>
          <button aria-label="Search" onClick={() => setSearchOpen((v) => !v)}>
            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
              <circle cx="11" cy="11" r="7" />
              <path d="M21 21l-4.3-4.3" strokeLinecap="round" />
            </svg>
          </button>

          <Link to={ROUTES.wishlist} className="relative" aria-label="Wishlist">
            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
              <path d="M12 21s-7.5-4.6-10-9.1C.5 8.3 2.2 4.8 5.8 4.2c2-.3 3.9.7 5 2.3 1.1-1.6 3-2.6 5-2.3 3.6.6 5.3 4.1 3.8 7.7C19.5 16.4 12 21 12 21z" />
            </svg>
            {wishlistCount > 0 && (
              <span className="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-[var(--color-primary)] text-[0.6rem] text-[var(--color-bg-light)]">
                {wishlistCount}
              </span>
            )}
          </Link>

          <button className="relative" aria-label="Cart" onClick={() => toggleCart(true)}>
            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
              <path d="M6 6h15l-1.5 9h-12z" strokeLinejoin="round" />
              <path d="M6 6L4.5 3H2" strokeLinecap="round" />
              <circle cx="9" cy="20" r="1.3" />
              <circle cx="18" cy="20" r="1.3" />
            </svg>
            {itemCount > 0 && (
              <span
                key={itemCount}
                className="animate-scale-in absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-[var(--color-primary)] text-[0.6rem] text-[var(--color-bg-light)]"
              >
                {itemCount}
              </span>
            )}
          </button>
        </div>
      </div>

      {searchOpen && (
        <form
          onSubmit={handleSearchSubmit}
          className="border-t border-[var(--color-border)] bg-[var(--color-bg-light)] px-4 py-3 sm:px-6 lg:px-8"
        >
          <div className="mx-auto flex max-w-2xl items-center gap-3 rounded-[var(--radius-pill)] border border-[var(--color-border)] bg-[var(--color-bg-cream)] px-4 py-2.5">
            <svg viewBox="0 0 24 24" className="h-4 w-4 flex-shrink-0" fill="none" stroke="var(--color-text-muted)" strokeWidth="1.8">
              <circle cx="11" cy="11" r="7" />
              <path d="M21 21l-4.3-4.3" strokeLinecap="round" />
            </svg>
            <input
              autoFocus
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder={SITE_CONFIG.microcopy.searchPlaceholder}
              className="w-full bg-transparent text-sm outline-none placeholder:text-[var(--color-text-muted)]"
            />
          </div>
        </form>
      )}

      {/* Mobile drawer */}
      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="animate-fade-in absolute inset-0 bg-[var(--color-dark)]/50" onClick={() => setMobileOpen(false)} />
          <div className="animate-slide-in-left absolute left-0 top-0 h-full w-72 bg-[var(--color-bg-light)] p-6 shadow-xl">
            <div className="mb-8 flex items-center justify-between">
              <span className="font-display text-lg text-[var(--color-primary)]">{APP_NAME}</span>
              <button onClick={() => setMobileOpen(false)} aria-label="Close menu">
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
                  <path d="M6 6l12 12M18 6L6 18" strokeLinecap="round" />
                </svg>
              </button>
            </div>
            <nav className="flex flex-col gap-5">
              {NAV_LINKS.slice(0, 3).map((link) => (
                <Link
                  key={link.href}
                  to={link.href}
                  onClick={() => setMobileOpen(false)}
                  className="font-display text-lg text-[var(--color-text-primary)]"
                >
                  {link.label}
                </Link>
              ))}
              <Link
                to={ROUTES.blog}
                onClick={() => setMobileOpen(false)}
                className="font-display text-lg text-[var(--color-text-primary)]"
              >
                Blog
              </Link>
              {NAV_LINKS.slice(3).map((link) => (
                <Link
                  key={link.href}
                  to={link.href}
                  onClick={() => setMobileOpen(false)}
                  className="font-display text-lg text-[var(--color-text-primary)]"
                >
                  {link.label}
                </Link>
              ))}
            </nav>
            <div className="mt-8 border-t border-[var(--color-border)] pt-6">
              <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
                Currency
              </p>
              <CurrencySwitcher />
            </div>
          </div>
        </div>
      )}
    </header>
  );
}
