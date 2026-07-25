import { useEffect, useState } from 'react';
import { useParams, useSearchParams } from 'react-router-dom';
import { productsApi, filterProducts, type ProductFilters } from '@/api/products';
import { categoryApi } from '@/api/category';
import { ProductGrid } from '@/components/product/ProductGrid';
import { PageHero } from '@/components/common/PageHero';
import { Breadcrumbs, type BreadcrumbItem } from '@/components/common/Breadcrumbs';
import { ROUTES } from '@/config/routes';
import { cn } from '@/utils/cn';
import type { Category, Product } from '@/types/product';

const SORT_OPTIONS: { value: NonNullable<ProductFilters['sortBy']>; label: string }[] = [
  { value: 'featured', label: 'Featured' },
  { value: 'newest', label: 'Newest' },
  { value: 'best-selling', label: 'Best Selling' },
  { value: 'price-asc', label: 'Price: Low to High' },
  { value: 'price-desc', label: 'Price: High to Low' },
  { value: 'rating', label: 'Top Rated' },
];

const PRICE_CAPS = [2000, 5000, 10000];

export default function ShopPage() {
  const { categorySlug } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const [allProducts, setAllProducts] = useState<Product[] | null>(null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [inStockOnly, setInStockOnly] = useState(false);
  const [maxPrice, setMaxPrice] = useState(10000);
  const [sheetOpen, setSheetOpen] = useState<'filter' | 'sort' | null>(null);
  const [scrolled, setScrolled] = useState(false);

  const sortBy = (searchParams.get('sort') as ProductFilters['sortBy']) || 'featured';
  const search = searchParams.get('search') || '';
  const activeCategory = categorySlug || searchParams.get('category') || undefined;

  useEffect(() => {
    productsApi.getAll().then(setAllProducts);
    categoryApi.getAll().then(setCategories);
  }, []);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 220);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const [filteredProducts, setFilteredProducts] = useState<Product[] | null>(null);

  useEffect(() => {
    if (!allProducts) return;
    setFilteredProducts(
      filterProducts(allProducts, {
        categorySlug: activeCategory,
        search,
        sortBy,
        inStockOnly,
        maxPrice,
      })
    );
  }, [allProducts, activeCategory, search, sortBy, inStockOnly, maxPrice]);

  const currentCategory = categories.find((c) => c.slug === activeCategory);
  const activeSort = SORT_OPTIONS.find((o) => o.value === sortBy);

  const [categoryAncestors, setCategoryAncestors] = useState<Category[]>([]);
  useEffect(() => {
    if (!currentCategory) {
      setCategoryAncestors([]);
      return;
    }
    categoryApi.getAncestors(currentCategory).then(setCategoryAncestors);
  }, [currentCategory]);
  const activeFilterCount = (activeCategory ? 1 : 0) + (inStockOnly ? 1 : 0) + (maxPrice < 10000 ? 1 : 0);

  function setCategory(slug?: string) {
    setSearchParams((p) => {
      if (slug) p.set('category', slug);
      else p.delete('category');
      return p;
    });
  }

  function setSort(value: string) {
    setSearchParams((p) => {
      p.set('sort', value);
      return p;
    });
  }

  return (
    <div className="pb-20 lg:pb-0">
      <PageHero pageKey="shop" fallbackTitle="Shop All" />

      {/* Glass filter bar — blends over the hero/content edge, category chips scroll horizontally */}
      <div
        className={cn(
          'sticky top-[57px] z-30 transition-all duration-300 sm:top-[97px]',
          scrolled ? 'glass-surface shadow-[var(--shadow-soft)]' : 'bg-transparent'
        )}
      >
        <div className="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
          <div className="flex items-center gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <button
              onClick={() => setCategory(undefined)}
              className={cn(
                'flex-shrink-0 rounded-[var(--radius-pill)] border px-4 py-2 text-xs font-medium transition-all duration-200',
                !activeCategory
                  ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                  : 'border-[var(--color-border)] bg-[var(--color-bg-light)] text-[var(--color-text-secondary)]'
              )}
            >
              All
            </button>
            {categories.map((c) => (
              <button
                key={c.id}
                onClick={() => setCategory(c.slug)}
                className={cn(
                  'flex-shrink-0 rounded-[var(--radius-pill)] border px-4 py-2 text-xs font-medium transition-all duration-200',
                  activeCategory === c.slug
                    ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                    : 'border-[var(--color-border)] bg-[var(--color-bg-light)] text-[var(--color-text-secondary)]'
                )}
              >
                {c.name}
              </button>
            ))}
          </div>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        {currentCategory && (
          <Breadcrumbs
            items={[
              { label: 'Shop', href: ROUTES.shop },
              ...categoryAncestors.map((c): BreadcrumbItem => ({
                label: c.name,
                href: `${ROUTES.shop}?category=${c.slug}`,
              })),
              { label: currentCategory.name },
            ]}
          />
        )}
        <div className="mb-6 animate-fade-in-up">
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            {currentCategory ? currentCategory.name : 'Shop All'}
          </p>
          <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">
            {currentCategory ? currentCategory.name : search ? `Results for "${search}"` : 'All Products'}
          </h1>
          {currentCategory && (
            <p className="mt-2 max-w-2xl text-sm text-[var(--color-text-secondary)]">
              {currentCategory.description}
            </p>
          )}
        </div>

        <div className="grid grid-cols-1 gap-10 lg:grid-cols-[240px_1fr]">
          {/* Desktop sidebar — hidden on mobile, replaced by bottom sheet */}
          <aside className="hidden flex-col gap-8 lg:flex">
            <div>
              <h3 className="mb-3 font-display text-base text-[var(--color-text-primary)]">Category</h3>
              <div className="flex flex-col gap-2">
                <button
                  onClick={() => setCategory(undefined)}
                  className={`text-left text-sm ${!activeCategory ? 'font-semibold text-[var(--color-primary)]' : 'text-[var(--color-text-secondary)]'}`}
                >
                  All Categories
                </button>
                {categories.map((c) => (
                  <button
                    key={c.id}
                    onClick={() => setCategory(c.slug)}
                    className={`text-left text-sm ${activeCategory === c.slug ? 'font-semibold text-[var(--color-primary)]' : 'text-[var(--color-text-secondary)]'}`}
                  >
                    {c.name} <span className="text-[var(--color-text-muted)]">({c.productCount})</span>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <h3 className="mb-3 font-display text-base text-[var(--color-text-primary)]">Max Price</h3>
              <input
                type="range"
                min={500}
                max={10000}
                step={500}
                value={maxPrice}
                onChange={(e) => setMaxPrice(Number(e.target.value))}
                className="w-full accent-[var(--color-primary)]"
              />
              <p className="mt-1 text-xs text-[var(--color-text-muted)]">Up to ₹{maxPrice.toLocaleString('en-IN')}</p>
            </div>

            <label className="flex items-center gap-2 text-sm text-[var(--color-text-secondary)]">
              <input
                type="checkbox"
                checked={inStockOnly}
                onChange={(e) => setInStockOnly(e.target.checked)}
                className="accent-[var(--color-primary)]"
              />
              In Stock Only
            </label>
          </aside>

          {/* Products */}
          <div>
            <div className="mb-6 hidden items-center justify-between lg:flex">
              <p className="text-sm text-[var(--color-text-muted)]">
                {filteredProducts ? `${filteredProducts.length} products` : 'Loading...'}
              </p>
              <select
                value={sortBy}
                onChange={(e) => setSort(e.target.value)}
                className="rounded-[var(--radius-btn)] border border-[var(--color-border)] bg-[var(--color-bg-light)] px-3 py-2 text-sm outline-none"
              >
                {SORT_OPTIONS.map((o) => (
                  <option key={o.value} value={o.value}>
                    Sort: {o.label}
                  </option>
                ))}
              </select>
            </div>
            <p className="mb-4 text-sm text-[var(--color-text-muted)] lg:hidden">
              {filteredProducts ? `${filteredProducts.length} products` : 'Loading...'}
            </p>
            <ProductGrid products={filteredProducts ?? []} isLoading={!filteredProducts} columns={3} />
          </div>
        </div>
      </div>

      {/* Mobile: Flipkart-style sticky bottom bar */}
      <div className="glass-surface fixed inset-x-0 bottom-0 z-40 flex border-t border-[var(--color-border)] lg:hidden">
        <button
          onClick={() => setSheetOpen('sort')}
          className="flex flex-1 items-center justify-center gap-2 py-3.5 text-sm font-medium text-[var(--color-text-primary)]"
        >
          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="1.8">
            <path d="M3 7h18M6 12h12M10 17h4" strokeLinecap="round" />
          </svg>
          Sort{activeSort && activeSort.value !== 'featured' ? `: ${activeSort.label}` : ''}
        </button>
        <div className="w-px bg-[var(--color-border)]" />
        <button
          onClick={() => setSheetOpen('filter')}
          className="flex flex-1 items-center justify-center gap-2 py-3.5 text-sm font-medium text-[var(--color-text-primary)]"
        >
          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="1.8">
            <path d="M4 6h16M7 12h10M10 18h4" strokeLinecap="round" />
          </svg>
          Filter
          {activeFilterCount > 0 && (
            <span className="flex h-4 w-4 items-center justify-center rounded-full bg-[var(--color-primary)] text-[0.6rem] text-[var(--color-bg-light)]">
              {activeFilterCount}
            </span>
          )}
        </button>
      </div>

      {/* Bottom sheets */}
      {sheetOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-[var(--color-dark)]/50" onClick={() => setSheetOpen(null)} />
          <div className="animate-slide-up-sheet absolute inset-x-0 bottom-0 max-h-[75vh] overflow-y-auto rounded-t-[1.5rem] bg-[var(--color-bg-light)] pb-6 shadow-2xl">
            <div className="sticky top-0 flex items-center justify-between border-b border-[var(--color-border)] bg-[var(--color-bg-light)] px-5 py-4">
              <h3 className="font-display text-lg text-[var(--color-text-primary)]">
                {sheetOpen === 'sort' ? 'Sort By' : 'Filters'}
              </h3>
              <button onClick={() => setSheetOpen(null)} aria-label="Close">
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
                  <path d="M6 6l12 12M18 6L6 18" strokeLinecap="round" />
                </svg>
              </button>
            </div>

            {sheetOpen === 'sort' && (
              <div className="flex flex-col px-5 py-2">
                {SORT_OPTIONS.map((o) => (
                  <button
                    key={o.value}
                    onClick={() => {
                      setSort(o.value);
                      setSheetOpen(null);
                    }}
                    className={cn(
                      'flex items-center justify-between border-b border-[var(--color-border-soft)] py-3.5 text-left text-sm',
                      sortBy === o.value ? 'font-semibold text-[var(--color-primary)]' : 'text-[var(--color-text-secondary)]'
                    )}
                  >
                    {o.label}
                    {sortBy === o.value && (
                      <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2">
                        <path d="M20 6L9 17l-5-5" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    )}
                  </button>
                ))}
              </div>
            )}

            {sheetOpen === 'filter' && (
              <div className="flex flex-col gap-6 px-5 py-4">
                <div>
                  <h4 className="mb-3 font-display text-sm text-[var(--color-text-primary)]">Category</h4>
                  <div className="flex flex-wrap gap-2">
                    <button
                      onClick={() => setCategory(undefined)}
                      className={cn(
                        'rounded-[var(--radius-pill)] border px-3.5 py-1.5 text-xs',
                        !activeCategory
                          ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                          : 'border-[var(--color-border)] text-[var(--color-text-secondary)]'
                      )}
                    >
                      All
                    </button>
                    {categories.map((c) => (
                      <button
                        key={c.id}
                        onClick={() => setCategory(c.slug)}
                        className={cn(
                          'rounded-[var(--radius-pill)] border px-3.5 py-1.5 text-xs',
                          activeCategory === c.slug
                            ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                            : 'border-[var(--color-border)] text-[var(--color-text-secondary)]'
                        )}
                      >
                        {c.name}
                      </button>
                    ))}
                  </div>
                </div>

                <div>
                  <h4 className="mb-3 font-display text-sm text-[var(--color-text-primary)]">Max Price</h4>
                  <div className="flex flex-wrap gap-2">
                    {PRICE_CAPS.concat(10000).map((cap) => (
                      <button
                        key={cap}
                        onClick={() => setMaxPrice(cap)}
                        className={cn(
                          'rounded-[var(--radius-pill)] border px-3.5 py-1.5 text-xs',
                          maxPrice === cap
                            ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                            : 'border-[var(--color-border)] text-[var(--color-text-secondary)]'
                        )}
                      >
                        {cap === 10000 ? 'Any' : `Under ₹${cap.toLocaleString('en-IN')}`}
                      </button>
                    ))}
                  </div>
                </div>

                <label className="flex items-center gap-2 text-sm text-[var(--color-text-secondary)]">
                  <input
                    type="checkbox"
                    checked={inStockOnly}
                    onChange={(e) => setInStockOnly(e.target.checked)}
                    className="accent-[var(--color-primary)]"
                  />
                  In Stock Only
                </label>

                <div className="flex gap-3 pt-2">
                  <button
                    onClick={() => {
                      setCategory(undefined);
                      setMaxPrice(10000);
                      setInStockOnly(false);
                    }}
                    className="flex-1 rounded-[var(--radius-btn)] border border-[var(--color-border)] py-3 text-sm font-medium text-[var(--color-text-secondary)]"
                  >
                    Clear All
                  </button>
                  <button
                    onClick={() => setSheetOpen(null)}
                    className="flex-1 rounded-[var(--radius-btn)] bg-[var(--color-primary)] py-3 text-sm font-medium text-[var(--color-bg-light)]"
                  >
                    Apply
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
