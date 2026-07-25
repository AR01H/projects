import { useEffect, useState } from 'react';
import { categoryApi } from '@/api/category';
import { SectionHeading } from '@/components/common/SectionHeading';
import { HorizontalScroller } from '@/components/common/HorizontalScroller';
import { CategoryCard } from '@/components/product/CategoryCard';
import { ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';
import type { Category } from '@/types/product';

export function FeaturedCategories() {
  const [categories, setCategories] = useState<Category[]>([]);

  useEffect(() => {
    categoryApi.getFeatured(8).then(setCategories);
  }, []);

  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading
        eyebrow="Curated Selections"
        title="Featured Categories"
        description={SITE_CONFIG.microcopy.featuredCategoriesDescription}
        viewAllLink={ROUTES.shop}
      />
      <HorizontalScroller>
        {categories.map((c) => (
          <CategoryCard key={c.id} category={c} />
        ))}
      </HorizontalScroller>
    </section>
  );
}
