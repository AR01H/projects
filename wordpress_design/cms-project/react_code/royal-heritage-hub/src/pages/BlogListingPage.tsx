import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { blogApi } from '@/api/blog';
import { PageHero } from '@/components/common/PageHero';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { Reveal } from '@/components/common/Reveal';
import { buildRoute, ROUTES } from '@/config/routes';
import { cn } from '@/utils/cn';
import type { BlogCategory, BlogPost } from '@/types/product';

export default function BlogListingPage() {
  const { categorySlug } = useParams();
  const [posts, setPosts] = useState<BlogPost[] | null>(null);
  const [categories, setCategories] = useState<BlogCategory[]>([]);

  useEffect(() => {
    blogApi.getAllCategories().then(setCategories);
  }, []);

  useEffect(() => {
    setPosts(null);
    if (categorySlug) {
      blogApi.getByCategory(categorySlug).then(setPosts);
    } else {
      blogApi.getAllPosts().then(setPosts);
    }
  }, [categorySlug]);

  const activeCategory = categories.find((c) => c.slug === categorySlug);

  return (
    <div>
      <PageHero pageKey="faqs" fallbackTitle="The Journal" />

      <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <Breadcrumbs
          items={
            activeCategory
              ? [{ label: 'Blog', href: ROUTES.blog }, { label: activeCategory.name }]
              : [{ label: 'Blog' }]
          }
        />

        <div className="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
          <div>
            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
              Stories & Guides
            </p>
            <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">
              {activeCategory ? activeCategory.name : 'The Journal'}
            </h1>
          </div>
        </div>

        {/* Category filter pills */}
        <div className="mb-10 flex flex-wrap gap-2">
          <Link
            to={ROUTES.blog}
            className={cn(
              'rounded-[var(--radius-pill)] border px-4 py-2 text-xs font-medium transition-colors',
              !categorySlug
                ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-secondary)]'
            )}
          >
            All Posts
          </Link>
          {categories.map((c) => (
            <Link
              key={c.id}
              to={buildRoute(ROUTES.blogCategory, { categorySlug: c.slug })}
              className={cn(
                'rounded-[var(--radius-pill)] border px-4 py-2 text-xs font-medium transition-colors',
                categorySlug === c.slug
                  ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                  : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-secondary)]'
              )}
            >
              {c.name}
            </Link>
          ))}
        </div>

        {posts === null ? (
          <p className="text-sm text-[var(--color-text-muted)]">Loading...</p>
        ) : posts.length === 0 ? (
          <p className="text-sm text-[var(--color-text-muted)]">No posts in this category yet.</p>
        ) : (
          <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {posts.map((post, i) => (
              <Reveal key={post.id} delay={i * 60}>
                <Link to={buildRoute(ROUTES.blogPost, { postSlug: post.slug })} className="group block">
                  <div className="aspect-[4/3] overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]">
                    <img
                      src={post.coverImage}
                      alt={post.title}
                      loading="lazy"
                      className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                    />
                  </div>
                  <div className="mt-4">
                    <p className="text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-secondary-dark)]">
                      {categories.find((c) => c.slug === post.categorySlug)?.name}
                    </p>
                    <h2 className="mt-1.5 font-display text-lg leading-snug text-[var(--color-text-primary)] transition-colors group-hover:text-[var(--color-primary)]">
                      {post.title}
                    </h2>
                    <p className="mt-2 text-sm text-[var(--color-text-secondary)]">{post.excerpt}</p>
                    <p className="mt-3 text-xs text-[var(--color-text-muted)]">
                      {post.date} · {post.readMinutes} min read
                    </p>
                  </div>
                </Link>
              </Reveal>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
