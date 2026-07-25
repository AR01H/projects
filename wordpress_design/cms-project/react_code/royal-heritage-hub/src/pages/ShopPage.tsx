import { useEffect, useState, useMemo } from 'react';
import { useParams, useSearchParams } from 'react-router-dom';
import { productsApi, filterProducts, type ProductFilters } from '@/api/products';
import { categoryApi } from '@/api/category';
import { ProductGrid } from '@/components/product/ProductGrid';
import { ProductListItem } from '@/components/product/ProductListItem';
import { PageHero } from '@/components/common/PageHero';
import { Breadcrumbs, type BreadcrumbItem } from '@/components/common/Breadcrumbs';
import { SEO } from '@/components/common/SEO';
import { ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';
import { MOCK_TAGS } from '@/data/mockData';
import { cn } from '@/utils/cn';
import { useFormatCurrency } from '@/utils/formatCurrency';
import type { Category, Product } from '@/types/product';

const SORT_OPTIONS: { value: NonNullable<ProductFilters['sortBy']>; label: string }[] = [
  { value: 'featured', label: 'Featured' },
  { value: 'newest', label: 'Newest First' },
  { value: 'best-selling', label: 'Best Selling' },
  { value: 'price-asc', label: 'Price: Low to High' },
  { value: 'price-desc', label: 'Price: High to Low' },
  { value: 'rating', label: 'Top Rated' },
];

const PRICE_CAPS = [2000, 5000, 10000];

const MATERIALS = ['Wood', 'Brass', 'Softwood', 'Sheesham', 'Mango Wood', 'Rosewood', 'Teak'];

const PAGE_SIZE = 12;

export default function ShopPage() {
  const { categorySlug } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const [allProducts, setAllProducts] = useState<Product[] | null>(null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [inStockOnly, setInStockOnly] = useState(false);
  const [maxPrice, setMaxPrice] = useState(10000);
  const [selectedTag, setSelectedTag] = useState<string | undefined>(undefined);
  const [selectedMaterial, setSelectedMaterial] = useState<string | undefined>(undefined);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [sheetOpen, setSheetOpen] = useState<'filter' | 'sort' | null>(null);
  const [scrolled, setScrolled] = useState(false);
  const [visibleCount, setVisibleCount] = useState(PAGE_SIZE);
  const formatCurrency = useFormatCurrency();

  const sortBy = (searchParams.get('sort') as ProductFilters['sortBy']) || 'featured';
  const search = searchParams.get('search') || '';
  const urlTag = searchParams.get('tag') || undefined;
  const activeCategory = categorySlug || searchParams.get('category') || undefined;

  useEffect(() => {
    productsApi.getAll().then(setAllProducts);
    categoryApi.getAll().then(setCategories);
  }, []);

  useEffect(() => {
    setSelectedTag(urlTag);
  }, [urlTag]);

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
        tag: selectedTag,
        material: selectedMaterial,
      })
    );
    setVisibleCount(PAGE_SIZE);
  }, [allProducts, activeCategory, search, sortBy, inStockOnly, maxPrice, selectedTag, selectedMaterial]);

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

  const activeFilterCount =
    (activeCategory ? 1 : 0) +
    (inStockOnly ? 1 : 0) +
    (maxPrice < 10000 ? 1 : 0) +
    (selectedTag ? 1 : 0) +
    (selectedMaterial ? 1 : 0);

  const availableTags = useMemo(() => {
    if (!allProducts) return [];
    const tagSet = new Set<string>();
    for (const p of allProducts) {
      for (const t of p.tags) tagSet.add(t);
    }
    return MOCK_TAGS.filter((t) => tagSet.has(t.tag) && !t.parentTag).slice(0, 15);
  }, [allProducts]);

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

  function setTag(tag?: string) {
    setSelectedTag(tag);
    setSearchParams((p) => {
      if (tag) p.set('tag', tag);
      else p.delete('tag');
      return p;
    });
  }

  function clearAllFilters() {
    setCategory(undefined);
    setMaxPrice(10000);
    setInStockOnly(false);
    setSelectedTag(undefined);
    setSelectedMaterial(undefined);
    setSearchParams((p) => {
      p.delete('category');
      p.delete('tag');
      p.delete('sort');
      return p;
    });
  }

  return (
    <div className="pb-24 lg:pb-0">
      <SEO
        title={currentCategory ? currentCategory.name : 'Shop All'}
        description={currentCategory?.description || 'Browse our collection of handcrafted Indian treasures'}
      />
      <PageHero pageKey="shop" fallbackTitle="Shop All" />

      {/* Search Bar */}
      <div className="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <form
          onSubmit={(e) => {
            e.preventDefault();
            const q = (e.target as HTMLFormElement).querySelector('input')?.value || '';
            setSearchParams((p) => { if (q) p.set('search', q); else p.delete('search'); return p; });
          }}
          className="flex items-center gap-2 rounded-full border border-[var(--color-border)] bg-[var(--color-bg-light)] px-4 py-2.5 shadow-sm transition-shadow focus-within:shadow-[var(--shadow-card)] focus-within:border-[var(--color-primary)]"
        >
          <svg viewBox="0 0 24 24" className="h-4 w-4 flex-shrink-0 text-[var(--color-text-muted)]" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="11" cy="11" r="7" />
            <path d="M21 21l-4.3-4.3" strokeLinecap="round" />
          </svg>
          <input
            type="text"
            defaultValue={search}
            placeholder={SITE_CONFIG.microcopy.searchPlaceholder}
            className="w-full bg-transparent text-sm outline-none placeholder:text-[var(--color-text-muted)]"
          />
          {search && (
            <button
              type="button"
              onClick={() => setSearchParams((p) => { p.delete('search'); return p; })}
              className="flex h-6 w-6 items-center justify-center rounded-full text-[var(--color-text-muted)] hover:bg-[var(--color-bg-cream)]"
            >
              <svg viewBox="0 0 16 16" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 4l8 8M12 4l-8 8" strokeLinecap="round" /></svg>
            </button>
          )}
        </form>
      </div>

      {/* Category chips — sticky below header */}
      <div
        className={cn(
          'sticky top-[57px] z-30 transition-all duration-300 sm:top-[97px]',
          scrolled ? 'glass-surface shadow-[var(--shadow-soft)]' : 'bg-[var(--color-bg-light)]'
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
                  : 'border-[var(--color-border)] bg-[var(--color-bg-light)] text-[var(--color-text-secondary)] hover:border-[var(--color-primary)]'
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
                    : 'border-[var(--color-border)] bg-[var(--color-bg-light)] text-[var(--color-text-secondary)] hover:border-[var(--color-primary)]'
                )}
              >
                {c.name}
              </button>
            ))}
          </div>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        {/* Breadcrumbs */}
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

        {/* Page heading */}
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

        {/* Active filter chips */}
        {activeFilterCount > 0 && (
          <div className="mb-4 flex flex-wrap items-center gap-2">
            <span className="text-xs font-medium text-[var(--color-text-muted)]">Active:</span>
            {activeCategory && (
              <button
                onClick={() => setCategory(undefined)}
                className="inline-flex items-center gap-1.5 rounded-full border border-[var(--color-primary)]/30 bg-[var(--color-primary)]/10 px-2.5 py-1 text-xs font-medium text-[var(--color-primary)]"
              >
                {currentCategory?.name}
                <svg viewBox="0 0 16 16" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M4 4l8 8M12 4l-8 8" strokeLinecap="round" />
                </svg>
              </button>
            )}
            {selectedTag && (
              <button
                onClick={() => setTag(undefined)}
                className="inline-flex items-center gap-1.5 rounded-full border border-[var(--color-primary)]/30 bg-[var(--color-primary)]/10 px-2.5 py-1 text-xs font-medium text-[var(--color-primary)]"
              >
                {MOCK_TAGS.find((t) => t.tag === selectedTag)?.label || selectedTag}
                <svg viewBox="0 0 16 16" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M4 4l8 8M12 4l-8 8" strokeLinecap="round" />
                </svg>
              </button>
            )}
            {selectedMaterial && (
              <button
                onClick={() => setSelectedMaterial(undefined)}
                className="inline-flex items-center gap-1.5 rounded-full border border-[var(--color-primary)]/30 bg-[var(--color-primary)]/10 px-2.5 py-1 text-xs font-medium text-[var(--color-primary)]"
              >
                {selectedMaterial}
                <svg viewBox="0 0 16 16" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M4 4l8 8M12 4l-8 8" strokeLinecap="round" />
                </svg>
              </button>
            )}
            {maxPrice < 10000 && (
              <button
                onClick={() => setMaxPrice(10000)}
                className="inline-flex items-center gap-1.5 rounded-full border border-[var(--color-primary)]/30 bg-[var(--color-primary)]/10 px-2.5 py-1 text-xs font-medium text-[var(--color-primary)]"
              >
                Under ₹{maxPrice.toLocaleString('en-IN')}
                <svg viewBox="0 0 16 16" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M4 4l8 8M12 4l-8 8" strokeLinecap="round" />
                </svg>
              </button>
            )}
            {inStockOnly && (
              <button
                onClick={() => setInStockOnly(false)}
                className="inline-flex items-center gap-1.5 rounded-full border border-[var(--color-primary)]/30 bg-[var(--color-primary)]/10 px-2.5 py-1 text-xs font-medium text-[var(--color-primary)]"
              >
                In Stock
                <svg viewBox="0 0 16 16" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M4 4l8 8M12 4l-8 8" strokeLinecap="round" />
                </svg>
              </button>
            )}
            <button
              onClick={clearAllFilters}
              className="text-xs font-medium text-[var(--color-text-muted)] underline underline-offset-2 hover:text-[var(--color-primary)]"
            >
              Clear all
            </button>
          </div>
        )}

        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[280px_1fr]">
          {/* ═══ DESKTOP SIDEBAR ═══ */}
          <aside className="hidden flex-col gap-4 lg:flex">
            {/* Sidebar header */}
            <div className="flex items-center justify-between">
              <h2 className="font-display text-lg text-[var(--color-text-primary)]">Filters</h2>
              {activeFilterCount > 0 && (
                <button
                  onClick={clearAllFilters}
                  className="text-xs font-medium text-[var(--color-primary)] underline underline-offset-2 hover:text-[var(--color-primary-dark)]"
                >
                  Clear all ({activeFilterCount})
                </button>
              )}
            </div>

            {/* Category section */}
            <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4">
              <h3 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Category</h3>
              <div className="flex flex-col gap-0.5">
                <button
                  onClick={() => setCategory(undefined)}
                  className={cn(
                    'flex items-center justify-between rounded-[var(--radius-btn)] px-3 py-2.5 text-left text-sm transition-all',
                    !activeCategory
                      ? 'bg-[var(--color-primary)] font-semibold text-[var(--color-bg-light)] shadow-sm'
                      : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-cream)]'
                  )}
                >
                  <span>All Categories</span>
                  <span className={cn(
                    'text-[0.65rem]',
                    !activeCategory ? 'text-[var(--color-bg-light)]/70' : 'text-[var(--color-text-muted)]'
                  )}>
                    {allProducts?.length ?? 0}
                  </span>
                </button>
                {categories.map((c) => (
                  <button
                    key={c.id}
                    onClick={() => setCategory(c.slug)}
                    className={cn(
                      'flex items-center justify-between rounded-[var(--radius-btn)] px-3 py-2.5 text-left text-sm transition-all',
                      activeCategory === c.slug
                        ? 'bg-[var(--color-primary)] font-semibold text-[var(--color-bg-light)] shadow-sm'
                        : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-cream)]'
                    )}
                  >
                    <span>{c.name}</span>
                    <span className={cn(
                      'rounded-full px-1.5 py-0.5 text-[0.6rem] font-medium',
                      activeCategory === c.slug
                        ? 'bg-[var(--color-bg-light)]/20 text-[var(--color-bg-light)]'
                        : 'bg-[var(--color-bg-cream)] text-[var(--color-text-muted)]'
                    )}>
                      {c.productCount}
                    </span>
                  </button>
                ))}
              </div>
            </div>

            {/* Tags section */}
            {availableTags.length > 0 && (
              <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4">
                <h3 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Tags</h3>
                <div className="flex flex-wrap gap-1.5">
                  {availableTags.map((t) => (
                    <button
                      key={t.tag}
                      onClick={() => setTag(selectedTag === t.tag ? undefined : t.tag)}
                      className={cn(
                        'rounded-full border px-3 py-1.5 text-xs font-medium transition-all duration-200',
                        selectedTag === t.tag
                          ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)] shadow-sm'
                          : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-primary)] hover:text-[var(--color-primary)] hover:shadow-sm'
                      )}
                    >
                      {t.label}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* Material section */}
            <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4">
              <h3 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Material</h3>
              <div className="flex flex-wrap gap-1.5">
                {MATERIALS.map((m) => (
                  <button
                    key={m}
                    onClick={() => setSelectedMaterial(selectedMaterial === m ? undefined : m)}
                    className={cn(
                      'rounded-full border px-3 py-1.5 text-xs font-medium transition-all duration-200',
                      selectedMaterial === m
                        ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)] shadow-sm'
                        : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-primary)] hover:text-[var(--color-primary)] hover:shadow-sm'
                    )}
                  >
                    {m}
                  </button>
                ))}
              </div>
            </div>

            {/* Price section */}
            <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4">
              <h3 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Price Range</h3>
              <div className="rounded-[var(--radius-btn)] bg-[var(--color-bg-cream)] px-3 py-2 text-center">
                <span className="font-display text-sm font-semibold text-[var(--color-primary)]">
                  Up to {formatCurrency(maxPrice)}
                </span>
              </div>
              <input
                type="range"
                min={500}
                max={10000}
                step={500}
                value={maxPrice}
                onChange={(e) => setMaxPrice(Number(e.target.value))}
                className="mt-3 w-full accent-[var(--color-primary)]"
              />
              <div className="mt-1 flex items-center justify-between text-[0.65rem] text-[var(--color-text-muted)]">
                <span>{formatCurrency(500)}</span>
                <span>{formatCurrency(10000)}</span>
              </div>
            </div>

            {/* In Stock toggle */}
            <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4">
              <label className="flex cursor-pointer items-center justify-between">
                <span className="text-sm font-medium text-[var(--color-text-primary)]">In Stock Only</span>
                <div className="flex items-center gap-2">
                  <div
                    className={cn(
                      'relative h-6 w-11 rounded-full transition-colors duration-200',
                      inStockOnly ? 'bg-[var(--color-primary)]' : 'bg-[var(--color-border)]'
                    )}
                    onClick={() => setInStockOnly(!inStockOnly)}
                  >
                    <div
                      className={cn(
                        'absolute top-0.5 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200',
                        inStockOnly ? 'translate-x-[22px]' : 'translate-x-0.5'
                      )}
                    />
                  </div>
                </div>
              </label>
            </div>
          </aside>

          {/* ═══ PRODUCTS AREA ═══ */}
          <div>
            {/* Toolbar */}
            <div className="mb-5 flex items-center justify-between border-b border-[var(--color-border)] pb-4">
              <p className="text-sm text-[var(--color-text-muted)]">
                {filteredProducts ? (
                  <>Showing <span className="font-medium text-[var(--color-text-primary)]">{filteredProducts.length}</span> products</>
                ) : 'Loading...'}
              </p>
              <div className="flex items-center gap-3">
                {/* View toggle — desktop only */}
                <div className="hidden items-center gap-0.5 rounded-lg border border-[var(--color-border)] p-0.5 lg:flex">
                  <button
                    onClick={() => setViewMode('grid')}
                    className={cn(
                      'rounded-md p-1.5 transition-colors',
                      viewMode === 'grid'
                        ? 'bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                        : 'text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)]'
                    )}
                    aria-label="Grid view"
                  >
                    <svg viewBox="0 0 16 16" className="h-4 w-4" fill="currentColor">
                      <rect x="0" y="0" width="7" height="7" rx="1" />
                      <rect x="9" y="0" width="7" height="7" rx="1" />
                      <rect x="0" y="9" width="7" height="7" rx="1" />
                      <rect x="9" y="9" width="7" height="7" rx="1" />
                    </svg>
                  </button>
                  <button
                    onClick={() => setViewMode('list')}
                    className={cn(
                      'rounded-md p-1.5 transition-colors',
                      viewMode === 'list'
                        ? 'bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                        : 'text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)]'
                    )}
                    aria-label="List view"
                  >
                    <svg viewBox="0 0 16 16" className="h-4 w-4" fill="currentColor">
                      <rect x="0" y="0" width="16" height="3" rx="1" />
                      <rect x="0" y="6.5" width="16" height="3" rx="1" />
                      <rect x="0" y="13" width="16" height="3" rx="1" />
                    </svg>
                  </button>
                </div>
                {/* Sort — desktop */}
                <select
                  value={sortBy}
                  onChange={(e) => setSort(e.target.value)}
                  className="rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-light)] px-3 py-2 text-sm outline-none transition-colors focus:border-[var(--color-primary)]"
                >
                  {SORT_OPTIONS.map((o) => (
                    <option key={o.value} value={o.value}>
                      {o.label}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            {/* Product display */}
            {viewMode === 'grid' ? (
              <>
                <ProductGrid
                  products={filteredProducts?.slice(0, visibleCount) ?? []}
                  isLoading={!filteredProducts}
                  columns={3}
                />
                {filteredProducts && visibleCount < filteredProducts.length && (
                  <div className="mt-8 text-center">
                    <button
                      onClick={() => setVisibleCount((c) => c + PAGE_SIZE)}
                      className="rounded-[var(--radius-btn)] border border-[var(--color-primary)] px-8 py-3 text-sm font-semibold text-[var(--color-primary)] transition-colors hover:bg-[var(--color-primary)] hover:text-[var(--color-bg-light)]"
                    >
                      Load More ({filteredProducts.length - visibleCount} remaining)
                    </button>
                  </div>
                )}
              </>
            ) : (
              <div className="flex flex-col gap-4">
                {filteredProducts ? (
                  filteredProducts.length === 0 ? (
                    <div className="py-16 text-center">
                      <svg viewBox="0 0 24 24" className="mx-auto h-12 w-12 text-[var(--color-text-muted)]" fill="none" stroke="currentColor" strokeWidth="1">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.3-4.3" strokeLinecap="round" />
                      </svg>
                      <p className="mt-4 text-sm text-[var(--color-text-muted)]">No products match your filters.</p>
                      <button onClick={clearAllFilters} className="mt-2 text-sm font-medium text-[var(--color-primary)] underline">
                        Clear all filters
                      </button>
                    </div>
                  ) : (
                    <>
                      {filteredProducts.slice(0, visibleCount).map((p) => (
                        <ProductListItem key={p.id} product={p} />
                      ))}
                      {visibleCount < filteredProducts.length && (
                        <div className="mt-4 text-center">
                          <button
                            onClick={() => setVisibleCount((c) => c + PAGE_SIZE)}
                            className="rounded-[var(--radius-btn)] border border-[var(--color-primary)] px-8 py-3 text-sm font-semibold text-[var(--color-primary)] transition-colors hover:bg-[var(--color-primary)] hover:text-[var(--color-bg-light)]"
                          >
                            Load More ({filteredProducts.length - visibleCount} remaining)
                          </button>
                        </div>
                      )}
                    </>
                  )
                ) : (
                  Array.from({ length: 4 }).map((_, i) => (
                    <div key={i} className="h-40 animate-pulse rounded-[var(--radius-card)] bg-[var(--color-bg-cream)]" />
                  ))
                )}
              </div>
            )}
          </div>
        </div>
      </div>

      {/* ═══════════════════════════════════════════════════════════════
          MOBILE — Flipkart-style sticky bottom bar
          ═══════════════════════════════════════════════════════════════ */}
      <div className="fixed inset-x-0 bottom-0 z-40 border-t border-[var(--color-border)] bg-[var(--color-bg-light)] shadow-[0_-4px_20px_-4px_rgba(0,0,0,0.1)] lg:hidden">
        <div className="flex items-stretch">
          {/* Sort button */}
          <button
            onClick={() => setSheetOpen('sort')}
            className="flex flex-1 items-center justify-center gap-2 border-r border-[var(--color-border)] py-3.5 text-sm font-medium text-[var(--color-text-primary)] active:bg-[var(--color-bg-cream)]"
          >
            <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M3 6h18M3 12h12M3 18h7" strokeLinecap="round" />
            </svg>
            Sort
          </button>

          {/* Filter button */}
          <button
            onClick={() => setSheetOpen('filter')}
            className="flex flex-1 items-center justify-center gap-2 py-3.5 text-sm font-medium text-[var(--color-text-primary)] active:bg-[var(--color-bg-cream)]"
          >
            <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M4 6h16M7 12h10M10 18h4" strokeLinecap="round" />
            </svg>
            Filter
            {activeFilterCount > 0 && (
              <span className="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[var(--color-primary)] px-1.5 text-[0.6rem] font-bold text-[var(--color-bg-light)]">
                {activeFilterCount}
              </span>
            )}
          </button>
        </div>
      </div>

      {/* ═══════════════════════════════════════════════════════════════
          MOBILE — Sort bottom sheet
          ═══════════════════════════════════════════════════════════════ */}
      {sheetOpen === 'sort' && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="animate-fade-in absolute inset-0 bg-[var(--color-dark)]/50" onClick={() => setSheetOpen(null)} />
          <div className="animate-slide-up-sheet absolute inset-x-0 bottom-0 rounded-t-2xl bg-[var(--color-bg-light)] shadow-2xl">
            {/* Handle */}
            <div className="flex justify-center pt-3">
              <div className="h-1 w-10 rounded-full bg-[var(--color-border)]" />
            </div>
            {/* Header */}
            <div className="flex items-center justify-between border-b border-[var(--color-border)] px-5 py-4">
              <h3 className="font-display text-lg text-[var(--color-text-primary)]">Sort By</h3>
              <button onClick={() => setSheetOpen(null)} aria-label="Close" className="rounded-full p-1 hover:bg-[var(--color-bg-cream)]">
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
                  <path d="M6 6l12 12M18 6L6 18" strokeLinecap="round" />
                </svg>
              </button>
            </div>
            {/* Sort options — radio style */}
            <div className="max-h-[50vh] overflow-y-auto px-5 py-2">
              {SORT_OPTIONS.map((o) => (
                <button
                  key={o.value}
                  onClick={() => {
                    setSort(o.value);
                    setSheetOpen(null);
                  }}
                  className="flex w-full items-center gap-3 border-b border-[var(--color-border-soft)] py-4 text-left"
                >
                  <div
                    className={cn(
                      'flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2',
                      sortBy === o.value
                        ? 'border-[var(--color-primary)]'
                        : 'border-[var(--color-border)]'
                    )}
                  >
                    {sortBy === o.value && (
                      <div className="h-2.5 w-2.5 rounded-full bg-[var(--color-primary)]" />
                    )}
                  </div>
                  <span
                    className={cn(
                      'text-sm',
                      sortBy === o.value ? 'font-semibold text-[var(--color-primary)]' : 'text-[var(--color-text-secondary)]'
                    )}
                  >
                    {o.label}
                  </span>
                </button>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* ═══════════════════════════════════════════════════════════════
          MOBILE — Filter bottom sheet
          ═══════════════════════════════════════════════════════════════ */}
      {sheetOpen === 'filter' && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="animate-fade-in absolute inset-0 bg-[var(--color-dark)]/50" onClick={() => setSheetOpen(null)} />
          <div className="animate-slide-up-sheet absolute inset-x-0 bottom-0 max-h-[80vh] rounded-t-2xl bg-[var(--color-bg-light)] shadow-2xl">
            {/* Handle */}
            <div className="flex justify-center pt-3">
              <div className="h-1 w-10 rounded-full bg-[var(--color-border)]" />
            </div>
            {/* Header */}
            <div className="flex items-center justify-between border-b border-[var(--color-border)] px-5 py-4">
              <h3 className="font-display text-lg text-[var(--color-text-primary)]">Filters</h3>
              <button onClick={() => setSheetOpen(null)} aria-label="Close" className="rounded-full p-1 hover:bg-[var(--color-bg-cream)]">
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
                  <path d="M6 6l12 12M18 6L6 18" strokeLinecap="round" />
                </svg>
              </button>
            </div>

            {/* Filter sections */}
            <div className="max-h-[calc(80vh-80px)] overflow-y-auto">
              {/* Category */}
              <div className="border-b border-[var(--color-border)] px-5 py-4">
                <h4 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Category</h4>
                <div className="flex flex-wrap gap-2">
                  <button
                    onClick={() => setCategory(undefined)}
                    className={cn(
                      'rounded-full border px-4 py-2 text-xs font-medium transition-all',
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
                        'rounded-full border px-4 py-2 text-xs font-medium transition-all',
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

              {/* Tags */}
              <div className="border-b border-[var(--color-border)] px-5 py-4">
                <h4 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Tags</h4>
                <div className="flex flex-wrap gap-2">
                  {availableTags.map((t) => (
                    <button
                      key={t.tag}
                      onClick={() => setTag(selectedTag === t.tag ? undefined : t.tag)}
                      className={cn(
                        'rounded-full border px-4 py-2 text-xs font-medium transition-all',
                        selectedTag === t.tag
                          ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                          : 'border-[var(--color-border)] text-[var(--color-text-secondary)]'
                      )}
                    >
                      {t.label}
                    </button>
                  ))}
                </div>
              </div>

              {/* Material */}
              <div className="border-b border-[var(--color-border)] px-5 py-4">
                <h4 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Material</h4>
                <div className="flex flex-wrap gap-2">
                  {MATERIALS.map((m) => (
                    <button
                      key={m}
                      onClick={() => setSelectedMaterial(selectedMaterial === m ? undefined : m)}
                      className={cn(
                        'rounded-full border px-4 py-2 text-xs font-medium transition-all',
                        selectedMaterial === m
                          ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                          : 'border-[var(--color-border)] text-[var(--color-text-secondary)]'
                      )}
                    >
                      {m}
                    </button>
                  ))}
                </div>
              </div>

              {/* Price */}
              <div className="border-b border-[var(--color-border)] px-5 py-4">
                <h4 className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Max Price</h4>
                <div className="flex flex-wrap gap-2">
                  {PRICE_CAPS.concat(10000).map((cap) => (
                    <button
                      key={cap}
                      onClick={() => setMaxPrice(cap)}
                      className={cn(
                        'rounded-full border px-4 py-2 text-xs font-medium transition-all',
                        maxPrice === cap
                          ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                          : 'border-[var(--color-border)] text-[var(--color-text-secondary)]'
                      )}
                    >
                      {cap === 10000 ? 'Any Price' : `Under ₹${cap.toLocaleString('en-IN')}`}
                    </button>
                  ))}
                </div>
              </div>

              {/* In Stock */}
              <div className="px-5 py-4">
                <label className="flex cursor-pointer items-center gap-3">
                  <div
                    className={cn(
                      'relative h-5 w-9 rounded-full transition-colors',
                      inStockOnly ? 'bg-[var(--color-primary)]' : 'bg-[var(--color-border)]'
                    )}
                    onClick={() => setInStockOnly(!inStockOnly)}
                  >
                    <div
                      className={cn(
                        'absolute top-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform',
                        inStockOnly ? 'translate-x-4' : 'translate-x-0.5'
                      )}
                    />
                  </div>
                  <span className="text-sm text-[var(--color-text-secondary)]">In Stock Only</span>
                </label>
              </div>
            </div>

            {/* Bottom action bar */}
            <div className="flex border-t border-[var(--color-border)] px-5 py-4">
              <button
                onClick={clearAllFilters}
                className="flex-1 rounded-lg border border-[var(--color-border)] py-3 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-bg-cream)]"
              >
                Clear All
              </button>
              <button
                onClick={() => setSheetOpen(null)}
                className="ml-3 flex-1 rounded-lg bg-[var(--color-primary)] py-3 text-sm font-semibold text-[var(--color-bg-light)] transition-colors hover:bg-[var(--color-primary-dark)]"
              >
                Apply {activeFilterCount > 0 && `(${activeFilterCount})`}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
