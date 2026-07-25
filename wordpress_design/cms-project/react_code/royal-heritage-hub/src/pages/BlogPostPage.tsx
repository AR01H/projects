import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { blogApi } from '@/api/blog';
import { PageLoader } from '@/components/common/Skeleton';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { TagPill } from '@/components/common/TagPill';
import { Reveal } from '@/components/common/Reveal';
import { buildRoute, ROUTES } from '@/config/routes';
import type { BlogCategory, BlogPost } from '@/types/product';

export default function BlogPostPage() {
  const { postSlug } = useParams();
  const [post, setPost] = useState<BlogPost | null | undefined>(undefined);
  const [category, setCategory] = useState<BlogCategory | undefined>(undefined);
  const [related, setRelated] = useState<BlogPost[]>([]);

  useEffect(() => {
    if (!postSlug) return;
    setPost(undefined);
    blogApi.getBySlug(postSlug).then(async (p) => {
      setPost(p ?? null);
      if (p) {
        const [cat, rel] = await Promise.all([
          blogApi.getCategoryBySlug(p.categorySlug),
          blogApi.getRelated(p),
        ]);
        setCategory(cat);
        setRelated(rel);
      }
    });
  }, [postSlug]);

  if (post === undefined) return <PageLoader />;
  if (post === null) {
    return (
      <div className="mx-auto max-w-2xl px-4 py-24 text-center">
        <p className="font-display text-2xl text-[var(--color-text-primary)]">Post not found</p>
      </div>
    );
  }

  return (
    <div>
      <div className="relative aspect-[16/8] w-full overflow-hidden sm:aspect-[21/8]">
        <img src={post.coverImage} alt={post.title} className="h-full w-full object-cover" />
        <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/85 via-[var(--color-dark)]/25 to-transparent" />
        <div className="absolute inset-0 flex flex-col justify-end px-6 pb-10 sm:px-12 lg:px-20">
          {category && (
            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.25em] text-[var(--color-secondary)]">
              {category.name}
            </p>
          )}
          <h1 className="max-w-2xl font-display text-3xl leading-tight text-[var(--color-bg-light)] sm:text-4xl">
            {post.title}
          </h1>
        </div>
      </div>

      <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <Breadcrumbs
          items={[
            { label: 'Blog', href: ROUTES.blog },
            ...(category ? [{ label: category.name, href: buildRoute(ROUTES.blogCategory, { categorySlug: category.slug }) }] : []),
            { label: post.title },
          ]}
        />

        <div className="mb-8 flex items-center gap-3 text-xs text-[var(--color-text-muted)]">
          <span>{post.author}</span>
          <span>·</span>
          <span>{post.date}</span>
          <span>·</span>
          <span>{post.readMinutes} min read</span>
        </div>

        <div className="flex flex-col gap-5 text-sm leading-relaxed text-[var(--color-text-secondary)] sm:text-base">
          {post.content.map((para, i) => (
            <p key={i}>{para}</p>
          ))}
        </div>

        {post.tags.length > 0 && (
          <div className="mt-8 flex flex-wrap gap-2 border-t border-[var(--color-border)] pt-6">
            {post.tags.map((t) => (
              <TagPill key={t} tag={t} size="xs" />
            ))}
          </div>
        )}
      </div>

      {related.length > 0 && (
        <div className="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
          <p className="mb-5 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            Related Reading
          </p>
          <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
            {related.map((p, i) => (
              <Reveal key={p.id} delay={i * 60}>
                <Link to={buildRoute(ROUTES.blogPost, { postSlug: p.slug })} className="group block">
                  <div className="aspect-[4/3] overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]">
                    <img
                      src={p.coverImage}
                      alt={p.title}
                      className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                    />
                  </div>
                  <p className="mt-3 font-display text-sm text-[var(--color-text-primary)] transition-colors group-hover:text-[var(--color-primary)]">
                    {p.title}
                  </p>
                </Link>
              </Reveal>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
