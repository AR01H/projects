import { useEffect, useState, useRef, useCallback } from 'react';
import { useParams } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { categoryApi } from '@/api/category';
import { useCartStore } from '@/store/useCartStore';
import { useWishlistStore } from '@/store/useWishlistStore';
import { useRecentlyViewedStore } from '@/store/useRecentlyViewedStore';
import { formatDiscount, useFormatCurrency } from '@/utils/formatCurrency';
import { Button } from '@/components/common/Button';
import { Badge } from '@/components/common/Badge';
import { Rating } from '@/components/common/Rating';
import { PageLoader } from '@/components/common/Skeleton';
import { EmptyState } from '@/components/common/EmptyState';
import { Breadcrumbs, type BreadcrumbItem } from '@/components/common/Breadcrumbs';
import { ProductRail } from '@/components/home/ProductRail';
import { RecentlyViewed } from '@/components/home/RecentlyViewed';
import { TagPill } from '@/components/common/TagPill';
import { ImageZoom } from '@/components/product/ImageZoom';
import { StickyBuyBar } from '@/components/product/StickyBuyBar';
import { VariantSelector } from '@/components/product/VariantSelector';
import { ReviewForm } from '@/components/common/ReviewForm';
import { NotifyWhenAvailable } from '@/components/common/NotifyWhenAvailable';
import { SEO } from '@/components/common/SEO';
import { SHIPPING } from '@/config/constants';
import { SITE_CONFIG } from '@/config/site';
import { buildRoute, ROUTES } from '@/config/routes';
import type { Category, Product } from '@/types/product';

type Tab = 'description' | 'specifications' | 'reviews' | 'shipping';

export default function ProductDetailPage() {
  const { productSlug } = useParams();
  const [product, setProduct] = useState<Product | null | undefined>(undefined);
  const [categoryChain, setCategoryChain] = useState<Category[]>([]);
  const [activeImage, setActiveImage] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [tab, setTab] = useState<Tab>('description');
  const [showStickyBar, setShowStickyBar] = useState(false);
  const [selectedVariantId, setSelectedVariantId] = useState<string | null>(null);
  const addToBagRef = useRef<HTMLDivElement>(null);

  const addItem = useCartStore((s) => s.addItem);
  const toggleWishlist = useWishlistStore((s) => s.toggle);
  const isWishlisted = useWishlistStore((s) => product ? s.isWishlisted(product.id) : false);
  const trackViewed = useRecentlyViewedStore((s) => s.track);
  const formatCurrency = useFormatCurrency();

  const selectedVariant = product?.variants.find((v) => v.id === selectedVariantId);
  const effectivePrice = product ? product.price + (selectedVariant?.priceModifier ?? 0) : 0;

  const handleAddToBag = useCallback(() => {
    if (product && product.stock > 0) addItem(product, quantity, selectedVariantId ?? undefined);
  }, [product, quantity, addItem, selectedVariantId]);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => setShowStickyBar(!entry.isIntersecting),
      { threshold: 0 }
    );
    if (addToBagRef.current) observer.observe(addToBagRef.current);
    return () => observer.disconnect();
  }, []);

  useEffect(() => {
    if (!productSlug) return;
    setProduct(undefined);
    setActiveImage(0);
    setQuantity(1);
    setSelectedVariantId(null);
    productsApi.getBySlug(productSlug).then((p) => setProduct(p ?? null));
  }, [productSlug]);

  useEffect(() => {
    if (product) trackViewed(product.id);
  }, [product, trackViewed]);

  useEffect(() => {
    if (!product) {
      setCategoryChain([]);
      return;
    }
    categoryApi.getBySlug(product.categorySlug).then(async (cat) => {
      if (!cat) return;
      const ancestors = await categoryApi.getAncestors(cat);
      setCategoryChain([...ancestors, cat]);
    });
  }, [product]);

  if (product === undefined) return <PageLoader />;
  if (product === null) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-24">
        <EmptyState title="Product not found" description="This piece may have sold out or moved. Explore our full collection instead." />
      </div>
    );
  }

  const discount = formatDiscount(product.price, product.compareAtPrice);
  const isOutOfStock = product.stock === 0;
  const isLowStock = product.stock > 0 && product.stock <= product.lowStockThreshold;

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SEO
        title={product.name}
        description={product.shortDescription}
        image={product.thumbnail}
        type="product"
      />
      <Breadcrumbs
        items={[
          { label: 'Shop', href: ROUTES.shop },
          ...categoryChain.map((c): BreadcrumbItem => ({
            label: c.name,
            href: buildRoute(ROUTES.categoryLanding, { categorySlug: c.slug }),
          })),
          { label: product.name },
        ]}
      />
      <div className="grid grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-16">
        {/* Gallery */}
        <div>
          {/* Main media — video or image */}
          {activeImage === -1 && product.videoUrl ? (
            <div className="aspect-square w-full overflow-hidden rounded-[var(--radius-card)] bg-[var(--color-dark)] shadow-[var(--shadow-card)]">
              {product.videoUrl.includes('youtube.com') || product.videoUrl.includes('youtu.be') ? (
                <iframe
                  src={`https://www.youtube.com/embed/${product.videoUrl.includes('youtu.be') ? product.videoUrl.split('/').pop() : new URL(product.videoUrl).searchParams.get('v')}`}
                  className="h-full w-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                  title={`${product.name} video`}
                />
              ) : product.videoUrl.includes('vimeo.com') ? (
                <iframe
                  src={`https://player.vimeo.com/video/${product.videoUrl.split('/').pop()}`}
                  className="h-full w-full"
                  allow="autoplay; fullscreen; picture-in-picture"
                  allowFullScreen
                  title={`${product.name} video`}
                />
              ) : (
                <video
                  src={product.videoUrl}
                  controls
                  className="h-full w-full object-cover"
                  poster={product.thumbnail}
                />
              )}
            </div>
          ) : (
            <ImageZoom
              src={product.images[activeImage] || product.thumbnail}
              alt={product.name}
              className="aspect-square w-full rounded-[var(--radius-card)] bg-[var(--color-bg-cream)] shadow-[var(--shadow-card)]"
            />
          )}

          {/* Thumbnails */}
          <div className="mt-4 flex gap-3">
            {/* Video thumbnail */}
            {product.videoUrl && (
              <button
                onClick={() => setActiveImage(-1)}
                className={`relative h-20 w-20 overflow-hidden rounded-[var(--radius-btn)] border-2 transition-colors ${
                  activeImage === -1 ? 'border-[var(--color-primary)]' : 'border-transparent'
                }`}
              >
                <img src={product.thumbnail} alt="Video" className="h-full w-full object-cover" />
                <div className="absolute inset-0 flex items-center justify-center bg-[var(--color-dark)]/40">
                  <svg viewBox="0 0 24 24" className="h-8 w-8 text-white" fill="currentColor">
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </div>
              </button>
            )}
            {/* Image thumbnails */}
            {product.images.map((img, i) => (
              <button
                key={img}
                onClick={() => setActiveImage(i)}
                className={`h-20 w-20 overflow-hidden rounded-[var(--radius-btn)] border-2 transition-colors ${
                  activeImage === i ? 'border-[var(--color-primary)]' : 'border-transparent'
                }`}
              >
                <img src={img} alt="" className="h-full w-full object-cover" />
              </button>
            ))}
          </div>
        </div>

        {/* Info */}
        <div>
          <div className="mb-3 flex flex-wrap gap-2">
            {product.isBestSeller && <Badge variant="dark">Best Seller</Badge>}
            {product.isNewArrival && <Badge variant="gold">New Arrival</Badge>}
            {product.isLimitedEdition && <Badge variant="primary">Limited Edition</Badge>}
            {product.qualityBadges.map((b) => (
              <Badge key={b} variant="outline">{b}</Badge>
            ))}
            {discount && <Badge variant="danger">{discount}% OFF</Badge>}
          </div>

          <h1 className="font-display text-3xl leading-tight text-[var(--color-text-primary)] sm:text-4xl">
            {product.name}
          </h1>

          <div className="mt-3 flex items-center gap-3">
            <Rating value={product.rating} reviewCount={product.reviewCount} size="md" />
            {product.makerName && (
              <span className="text-xs text-[var(--color-text-muted)]">
                {SITE_CONFIG.terminology.qualityAdjective} by {product.makerName}
              </span>
            )}
          </div>

          <div className="mt-5 flex items-baseline gap-3">
            <span className="font-display text-3xl text-[var(--color-primary)]">
              {formatCurrency(effectivePrice)}
            </span>
            {product.compareAtPrice && (
              <span className="text-lg text-[var(--color-text-muted)] line-through">
                {formatCurrency(product.compareAtPrice)}
              </span>
            )}
            {selectedVariant?.priceModifier !== undefined && selectedVariant.priceModifier !== 0 && (
              <span className="text-xs text-[var(--color-text-muted)]">
                ({selectedVariant.priceModifier > 0 ? '+' : ''}{formatCurrency(selectedVariant.priceModifier)} for {selectedVariant.label})
              </span>
            )}
          </div>

          <p className="mt-5 text-sm leading-relaxed text-[var(--color-text-secondary)]">
            {product.shortDescription}
          </p>

          {/* Variant selector */}
          <div className="mt-5">
            <VariantSelector
              variants={product.variants}
              selectedVariantId={selectedVariantId}
              onSelect={setSelectedVariantId}
            />
          </div>

          {product.specs.some((s) => s.highlight) && (
            <div className="mt-4 flex flex-wrap gap-4">
              {product.specs
                .filter((s) => s.highlight)
                .map((s) => (
                  <div key={s.key}>
                    <p className="text-[0.65rem] uppercase tracking-wider text-[var(--color-text-muted)]">{s.label}</p>
                    <p className="text-sm text-[var(--color-text-primary)]">{s.value}</p>
                  </div>
                ))}
            </div>
          )}

          {isLowStock && (
            <p className="mt-4 text-xs font-semibold text-[var(--color-danger)]">
              Only {product.stock} left in stock — order soon
            </p>
          )}

          <div className="mt-7 flex items-center gap-4">
            <div className="flex items-center gap-4 rounded-full border border-[var(--color-border)] px-4 py-2.5">
              <button onClick={() => setQuantity((q) => Math.max(1, q - 1))} className="text-lg text-[var(--color-text-secondary)]">
                −
              </button>
              <span className="w-4 text-center text-sm">{quantity}</span>
              <button onClick={() => setQuantity((q) => Math.min(product.stock, q + 1))} className="text-lg text-[var(--color-text-secondary)]">
                +
              </button>
            </div>
            <button
              onClick={() => toggleWishlist(product)}
              aria-label="Toggle wishlist"
              className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border border-[var(--color-border)]"
            >
              <svg viewBox="0 0 24 24" className="h-5 w-5" fill={isWishlisted ? 'var(--color-primary)' : 'none'} stroke="var(--color-primary)" strokeWidth="1.8">
                <path d="M12 21s-7.5-4.6-10-9.1C.5 8.3 2.2 4.8 5.8 4.2c2-.3 3.9.7 5 2.3 1.1-1.6 3-2.6 5-2.3 3.6.6 5.3 4.1 3.8 7.7C19.5 16.4 12 21 12 21z" />
              </svg>
            </button>
            <button
              onClick={() => {
                if (navigator.share) {
                  navigator.share({ title: product.name, url: window.location.href }).catch(() => {});
                } else {
                  navigator.clipboard?.writeText(window.location.href);
                }
              }}
              aria-label="Share product"
              className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border border-[var(--color-border)]"
            >
              <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-primary)" strokeWidth="1.8">
                <circle cx="18" cy="5" r="2.5" />
                <circle cx="6" cy="12" r="2.5" />
                <circle cx="18" cy="19" r="2.5" />
                <path d="M8.2 10.7l7.6-4.4M8.2 13.3l7.6 4.4" strokeLinecap="round" />
              </svg>
            </button>
          </div>

          <div ref={addToBagRef} className="mt-4 flex flex-col gap-3 sm:flex-row">
            <Button
              variant="primary"
              size="lg"
              disabled={isOutOfStock}
              onClick={handleAddToBag}
              className="flex-1"
            >
              {isOutOfStock ? 'Out of Stock' : 'Add to Bag'}
            </Button>
            <Button
              variant="dark"
              size="lg"
              disabled={isOutOfStock}
              onClick={() => addItem(product, quantity)}
              className="flex-1"
            >
              Buy Now
            </Button>
          </div>

          {/* Back-in-stock notify */}
          {isOutOfStock && <NotifyWhenAvailable productName={product.name} />}

          <div className="mt-5 flex items-center gap-2 rounded-[var(--radius-btn)] bg-[var(--color-bg-cream)] px-4 py-3 text-xs text-[var(--color-text-secondary)]">
            <svg viewBox="0 0 24 24" className="h-4 w-4 flex-shrink-0" fill="none" stroke="var(--color-primary)" strokeWidth="1.8">
              <path d="M3 8h13l4 4v6h-3M3 8v10h3m8-10v10M7 21a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z" />
            </svg>
            <span>
              Free shipping above {formatCurrency(SHIPPING.freeShippingThreshold)} · Estimated delivery in{' '}
              {SHIPPING.estimatedDeliveryMin}–{SHIPPING.estimatedDeliveryMax} business days
            </span>
          </div>

          {/* Tabs */}
          <div className="mt-10 border-t border-[var(--color-border)] pt-6">
            <div className="mb-5 flex gap-6 border-b border-[var(--color-border)]">
              {(['description', 'specifications', 'reviews', 'shipping'] as Tab[]).map((t) => (
                <button
                  key={t}
                  onClick={() => setTab(t)}
                  className={`pb-3 text-sm font-medium capitalize transition-colors ${
                    tab === t
                      ? 'border-b-2 border-[var(--color-primary)] text-[var(--color-primary)]'
                      : 'text-[var(--color-text-muted)]'
                  }`}
                >
                  {t}
                </button>
              ))}
            </div>

            {tab === 'description' && (
              <div>
                <p className="text-sm leading-relaxed text-[var(--color-text-secondary)]">{product.description}</p>
                {product.tags.length > 0 && (
                  <div className="mt-5 flex flex-wrap gap-2">
                    {product.tags.map((t) => (
                      <TagPill key={t} tag={t} size="xs" />
                    ))}
                  </div>
                )}
              </div>
            )}

            {tab === 'specifications' && (
              <dl className="grid grid-cols-2 gap-y-3 text-sm">
                <div className="contents">
                  <dt className="text-[var(--color-text-muted)]">SKU</dt>
                  <dd className="text-[var(--color-text-primary)]">{product.sku}</dd>
                </div>
                {product.specs.map((spec) => (
                  <div key={spec.key} className="contents">
                    <dt className="text-[var(--color-text-muted)]">{spec.label}</dt>
                    <dd className="text-[var(--color-text-primary)]">{spec.value}</dd>
                  </div>
                ))}
                {product.qualityBadges.length > 0 && (
                  <div className="contents">
                    <dt className="text-[var(--color-text-muted)]">Quality</dt>
                    <dd className="text-[var(--color-text-primary)]">{product.qualityBadges.join(', ')}</dd>
                  </div>
                )}
              </dl>
            )}

            {tab === 'reviews' && (
              <div className="flex flex-col gap-5">
                {product.reviews.length === 0 ? (
                  <p className="text-sm text-[var(--color-text-muted)]">No reviews yet — be the first to review this product.</p>
                ) : (
                  product.reviews.map((r) => (
                    <div key={r.id} className="border-b border-[var(--color-border)] pb-4">
                      <div className="mb-1 flex items-center gap-2">
                        <Rating value={r.rating} />
                        {r.verified && <span className="text-[0.65rem] font-semibold uppercase text-[var(--color-success)]">Verified</span>}
                      </div>
                      <p className="font-display text-sm text-[var(--color-text-primary)]">{r.title}</p>
                      <p className="mt-1 text-sm text-[var(--color-text-secondary)]">{r.comment}</p>
                      <p className="mt-1 text-xs text-[var(--color-text-muted)]">{r.author} · {r.date}</p>
                    </div>
                  ))
                )}

                {/* Write a Review form */}
                <ReviewForm productId={product.id} onSubmitted={() => {
                  // Refresh product data to show new review
                  productsApi.getBySlug(product.slug).then((p) => setProduct(p ?? null));
                }} />
              </div>
            )}

            {tab === 'shipping' && (
              <div className="flex flex-col gap-2 text-sm text-[var(--color-text-secondary)]">
                <p>Free shipping on orders above {formatCurrency(SHIPPING.freeShippingThreshold)}.</p>
                <p>Estimated delivery: {SHIPPING.estimatedDeliveryMin}–{SHIPPING.estimatedDeliveryMax} business days.</p>
                <p>Cash on Delivery available with a {formatCurrency(SHIPPING.codCharge)} additional charge.</p>
                <p>Returns accepted within our standard return window — see Return Policy for details.</p>
              </div>
            )}
          </div>
        </div>
      </div>

      <ProductRail
        eyebrow="Complete the Look"
        title="Frequently Bought Together"
        fetcher={() => productsApi.getFrequentlyBoughtTogether(product)}
      />

      <ProductRail
        eyebrow="You May Also Like"
        title="Similar Products"
        fetcher={() => productsApi.getRelated(product)}
      />

      <RecentlyViewed />

      <StickyBuyBar
        product={product}
        quantity={quantity}
        onAddToBag={handleAddToBag}
        visible={showStickyBar}
        effectivePrice={effectivePrice}
      />
    </div>
  );
}
