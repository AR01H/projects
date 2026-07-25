import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { certificationsApi } from '@/api/certifications';
import { buildRoute, ROUTES } from '@/config/routes';
import { formatCurrency } from '@/utils/formatCurrency';
import { Reveal } from '@/components/common/Reveal';
import type { Product, CertificationEntry } from '@/types/product';

export function GITaggedShowcase() {
  const [products, setProducts] = useState<Product[]>([]);
  const [certifications, setCertifications] = useState<CertificationEntry[]>([]);

  useEffect(() => {
    productsApi.getAll().then(({ data }) => { if (data) setProducts(data); });
    certificationsApi.getAll().then(({ data }) => { if (data) setCertifications(data); });
  }, []);

  const giProducts = useMemo(() => {
    return products.filter((p) => p.tags.includes('gi-tagged'));
  }, [products]);

  if (giProducts.length === 0) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <Reveal className="mb-10 text-center">
        <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
          Government Certified
        </p>
        <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
          GI Tagged Heritage
        </h2>
        <p className="mx-auto mt-3 max-w-xl text-sm text-[var(--color-text-secondary)]">
          Products officially recognised by the Government of India for their authentic origin and traditional craftsmanship.
        </p>
      </Reveal>

      {/* Certification badges */}
      <div className="mb-10 flex flex-wrap justify-center gap-4">
        {certifications.map((cert) => (
          <Reveal key={cert.id} className="flex items-center gap-2 rounded-full border border-[var(--color-border)] bg-[var(--color-bg-light)] px-4 py-2 shadow-sm">
            <svg viewBox="0 0 24 24" className="h-5 w-5 flex-shrink-0" fill="none" stroke="var(--color-secondary)" strokeWidth="1.5">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
              <path d="M9 12l2 2 4-4" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            <div>
              <p className="text-xs font-semibold text-[var(--color-text-primary)]">{cert.title.split('—')[0].trim()}</p>
              <p className="text-[0.6rem] text-[var(--color-text-muted)]">{cert.issuedBy.split(',')[0]}</p>
            </div>
          </Reveal>
        ))}
      </div>

      {/* GI Tagged Products */}
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {giProducts.map((product, i) => (
          <Reveal key={product.id} delay={i * 100}>
            <Link
              to={buildRoute(ROUTES.product, { productSlug: product.slug })}
              className="group flex gap-4 overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4 shadow-[var(--shadow-card)] transition-all duration-300 hover:shadow-[var(--shadow-hover)]"
            >
              <div className="h-24 w-24 flex-shrink-0 overflow-hidden rounded-[var(--radius-btn)]">
                <img
                  src={product.thumbnail}
                  alt={product.name}
                  className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                />
              </div>
              <div className="flex flex-1 flex-col justify-center">
                <div className="mb-1 flex items-center gap-1.5">
                  <span className="inline-flex items-center gap-1 rounded-full bg-[var(--color-secondary)]/15 px-2 py-0.5 text-[0.6rem] font-semibold text-[var(--color-secondary-dark)]">
                    <svg viewBox="0 0 16 16" className="h-3 w-3" fill="currentColor">
                      <path d="M8 0l2.2 4.5L15 5.3l-3.5 3.4.8 4.8L8 11.3 3.7 13.5l.8-4.8L1 5.3l4.8-.8L8 0z" />
                    </svg>
                    GI Tagged
                  </span>
                </div>
                <h3 className="font-display text-sm font-medium text-[var(--color-text-primary)] transition-colors group-hover:text-[var(--color-primary)]">
                  {product.name}
                </h3>
                <p className="mt-1 text-xs text-[var(--color-text-muted)]">
                  {product.makerName ? `by ${product.makerName}` : product.specs.find((s) => s.key === 'origin')?.value?.split(',')[0]}
                </p>
                <p className="mt-1 font-semibold text-[var(--color-primary)]">
                  {formatCurrency(product.price)}
                </p>
              </div>
            </Link>
          </Reveal>
        ))}
      </div>
    </section>
  );
}
