import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { tagsApi, type TagSummary } from '@/api/tags';
import { ProductGrid } from '@/components/product/ProductGrid';
import { PageHero } from '@/components/common/PageHero';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { TagPill } from '@/components/common/TagPill';
import { Reveal } from '@/components/common/Reveal';
import { ROUTES } from '@/config/routes';
import type { Product } from '@/types/product';

export default function TagCollectionPage() {
  const { tag } = useParams();
  const [products, setProducts] = useState<Product[] | null>(null);
  const [currentTag, setCurrentTag] = useState<TagSummary | null>(null);
  const [parentTag, setParentTag] = useState<TagSummary | null>(null);
  const [childTags, setChildTags] = useState<TagSummary[]>([]);
  const [siblingTags, setSiblingTags] = useState<TagSummary[]>([]);

  useEffect(() => {
    if (!tag) return;
    setProducts(null);
    tagsApi.getByTag(tag).then(setProducts);

    tagsApi.getAll().then(async (all) => {
      const current = all.find((t) => t.tag === tag) ?? null;
      setCurrentTag(current);

      if (current?.parentTag) {
        const parent = all.find((t) => t.tag === current.parentTag) ?? null;
        setParentTag(parent);
        setSiblingTags(all.filter((t) => t.parentTag === current.parentTag && t.tag !== tag));
      } else {
        setParentTag(null);
        setSiblingTags([]);
      }

      setChildTags(all.filter((t) => t.parentTag === tag));
    });
  }, [tag]);

  const breadcrumbItems = [
    { label: 'Tags', href: ROUTES.tags },
    ...(parentTag ? [{ label: parentTag.label, href: `/tags/${parentTag.tag}` }] : []),
    { label: currentTag?.label ?? tag ?? '' },
  ];

  return (
    <div>
      <PageHero pageKey="shop" fallbackTitle={`#${currentTag?.label ?? tag ?? ''}`} />

      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <Breadcrumbs items={breadcrumbItems} />

        <Reveal>
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            Tagged
          </p>
          <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">
            #{currentTag?.label ?? tag}
          </h1>
          <p className="mt-2 text-sm text-[var(--color-text-muted)]">
            {products ? `${products.length} products found` : 'Loading...'}
          </p>
        </Reveal>

        {/* Sub-tags under this tag */}
        {childTags.length > 0 && (
          <div className="mt-6">
            <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
              Narrow down
            </p>
            <div className="flex flex-wrap gap-2">
              {childTags.map((t) => (
                <TagPill key={t.tag} tag={t.tag} label={t.label} />
              ))}
            </div>
          </div>
        )}

        {/* Related sibling tags */}
        {siblingTags.length > 0 && (
          <div className="mt-4">
            <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
              Related tags
            </p>
            <div className="flex flex-wrap gap-2">
              {siblingTags.map((t) => (
                <TagPill key={t.tag} tag={t.tag} label={t.label} size="xs" />
              ))}
            </div>
          </div>
        )}

        <div className="mt-8">
          <ProductGrid products={products ?? []} isLoading={!products} columns={4} />
        </div>
      </div>
    </div>
  );
}
