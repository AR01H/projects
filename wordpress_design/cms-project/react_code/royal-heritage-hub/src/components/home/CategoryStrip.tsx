import { useEffect, useState } from 'react';
import { categoryApi } from '@/api/category';
import { SectionHeading } from '@/components/common/SectionHeading';
import { HorizontalScroller } from '@/components/common/HorizontalScroller';
import { CategoryPill } from '@/components/product/CategoryCard';
import type { Category } from '@/types/product';

export function CategoryStrip() {
  const [categories, setCategories] = useState<Category[]>([]);

  useEffect(() => {
    categoryApi.getAll().then(setCategories);
  }, []);

  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Explore" title="Shop by Category" />
      <HorizontalScroller>
        {categories.map((c) => (
          <CategoryPill key={c.id} category={c} />
        ))}
      </HorizontalScroller>
    </section>
  );
}
