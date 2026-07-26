import { useEffect, useState, useRef, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { SITE_CONFIG } from '@/config/site';
import { ROUTES } from '@/config/routes';
import { TOP_NAV_LINKS } from '@/config/navigation';
import { useCartStore } from '@/store/useCartStore';
import { useWishlistStore } from '@/store/useWishlistStore';
import { CurrencySwitcher } from './CurrencySwitcher';
import { BlogNavDropdown } from './BlogNavDropdown';
import { MegaMenu } from './MegaMenu';
import { CollectionsMegaMenu } from './CollectionsMegaMenu';
import { MobileNav } from './MobileNav';
import { cn } from '@/utils/cn';
import { SearchAutocomplete } from '@/components/common/SearchAutocomplete';

export function Header() {
  const [scrolled, setScrolled] = useState(false);
  const lastScrollY = useRef(0);
  const [searchOpen, setSearchOpen] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [megaMenuOpen, setMegaMenuOpen] = useState<string | null>(null);
  const [query, setQuery] = useState('');
  const navigate = useNavigate();
  const megaMenuTimeout = useRef<ReturnType<typeof setTimeout>>(undefined);
  const headerRef = useRef<HTMLElement>(null);

  const itemCount = useCartStore((s) => s.itemCount());
  const toggleCart = useCartStore((s) => s.toggleCart);
  const wishlistCount = useWishlistStore((s) => s.items.length);

  useEffect(() => {
    const onScroll = () => {
      const y = window.scrollY;
      setScrolled(y > 12);
      lastScrollY.current = y;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const handleMegaEnter = useCallback((label: string) => {
    clearTimeout(megaMenuTimeout.current);
    setMegaMenuOpen(label);
  }, []);

  const handleMegaLeave = useCallback(() => {
    megaMenuTimeout.current = setTimeout(() => setMegaMenuOpen(null), 200);
  }, []);

  const handleMegaMenuEnter = useCallback(() => {
    clearTimeout(megaMenuTimeout.current);
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
      ref={headerRef}
      className={cn(
        'header-grain sticky top-0 z-40 w-full transition-all duration-300',
        scrolled
          ? 'shadow-[var(--shadow-soft)] bg-[var(--color-bg-light)]'
          : ''
      )}
    >
      <div className="header-grain-overlay" />
      {/* Top strip */}
      <div className="header-strip relative hidden px-4 py-2 text-center text-xs text-[var(--color-bg-light)]/85 sm:block">
        <div className="header-strip-grain" />
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

        <Link to={ROUTES.home} className="flex items-center flex-shrink-0">
          <img src="/logo.svg" alt="Royal Heritage Hub" className="h-9 sm:h-10" />
        </Link>

        {/* Desktop mega menu nav */}
        <nav className="hidden items-center gap-0.5 lg:flex">
          {TOP_NAV_LINKS.map((link) => {
            const hasMega = link.children && link.children.length > 0;
            return (
              <div
                key={link.label}
                className="relative"
                onMouseEnter={() => hasMega && handleMegaEnter(link.label)}
                onMouseLeave={() => hasMega && handleMegaLeave()}
              >
                <Link
                  to={link.href}
                  className={cn(
                    'flex items-center gap-1 whitespace-nowrap px-2.5 py-2 text-[0.8rem] font-medium transition-colors',
                    megaMenuOpen === link.label
                      ? 'text-[var(--color-primary)]'
                      : 'text-[var(--color-text-primary)] hover:text-[var(--color-primary)]'
                  )}
                >
                  {link.label}
                  {hasMega && (
                    <svg
                      viewBox="0 0 24 24"
                      className={cn(
                        'h-3.5 w-3.5 transition-transform duration-200',
                        megaMenuOpen === link.label && 'rotate-180'
                      )}
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="2"
                    >
                      <path d="M6 9l6 6 6-6" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  )}
                </Link>
              </div>
            );
          })}
          <BlogNavDropdown />
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

      {/* Desktop mega menu panels */}
      {TOP_NAV_LINKS.map((link) =>
        link.children && link.children.length > 0 ? (
          <div
            key={link.label}
            className="hidden lg:block"
            onMouseEnter={handleMegaMenuEnter}
            onMouseLeave={handleMegaLeave}
          >
            {link.label === 'Collections' ? (
              <CollectionsMegaMenu
                items={link.children}
                isOpen={megaMenuOpen === link.label}
                onClose={() => setMegaMenuOpen(null)}
              />
            ) : (
              <MegaMenu
                items={link.children}
                isOpen={megaMenuOpen === link.label}
                onClose={() => setMegaMenuOpen(null)}
              />
            )}
          </div>
        ) : null
      )}

      {/* Search bar */}
      {searchOpen && (
        <div className="border-t border-[var(--color-border)]/50 bg-[var(--color-bg-light)]/95 backdrop-blur-xl px-4 py-3 sm:px-6 lg:px-8">
          <div className="relative mx-auto max-w-2xl">
            <form
              onSubmit={handleSearchSubmit}
              className="flex items-center gap-3 rounded-full border border-[var(--color-border)]/50 bg-[var(--color-bg-light)]/60 px-4 py-2.5 backdrop-blur-sm"
            >
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
            </form>
            <SearchAutocomplete query={query} onSelect={() => { setSearchOpen(false); setQuery(''); }} />
          </div>
        </div>
      )}

      {/* Mobile nav */}
      <MobileNav isOpen={mobileOpen} onClose={() => setMobileOpen(false)} />
    </header>
  );
}
