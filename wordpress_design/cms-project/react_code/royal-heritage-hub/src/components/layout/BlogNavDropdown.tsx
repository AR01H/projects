import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { blogApi } from '@/api/blog';
import { buildRoute, ROUTES } from '@/config/routes';
import { cn } from '@/utils/cn';
import type { BlogCategory, BlogPost } from '@/types/product';

export function BlogNavDropdown() {
  const [open, setOpen] = useState(false);
  const [categories, setCategories] = useState<BlogCategory[]>([]);
  const [recentPosts, setRecentPosts] = useState<BlogPost[]>([]);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open || categories.length > 0) return;
    blogApi.getAllCategories().then(setCategories);
    blogApi.getRecent(3).then(setRecentPosts);
  }, [open, categories.length]);

  useEffect(() => {
    function onClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener('mousedown', onClickOutside);
    return () => document.removeEventListener('mousedown', onClickOutside);
  }, []);

  return (
    <div
      ref={ref}
      className="relative"
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
    >
      <button
        onClick={() => setOpen((v) => !v)}
        className="flex items-center gap-1 text-sm font-medium tracking-wide text-[var(--color-text-primary)] transition-colors hover:text-[var(--color-primary)]"
      >
        Blog
        <svg
          viewBox="0 0 24 24"
          className={cn('h-3.5 w-3.5 transition-transform duration-200', open && 'rotate-180')}
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
        >
          <path d="M6 9l6 6 6-6" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </button>

      {open && (
        <div className="animate-fade-in-up absolute left-1/2 top-full z-50 pt-3 -translate-x-1/2">
          <div className="w-[420px] rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-5 shadow-[var(--shadow-hover)]">
          <div className="grid grid-cols-2 gap-6">
            <div>
              <p className="mb-3 text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
                Categories
              </p>
              <div className="flex flex-col gap-2">
                <Link
                  to={ROUTES.blog}
                  onClick={() => setOpen(false)}
                  className="text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-primary)]"
                >
                  All Posts
                </Link>
                {categories.map((c) => (
                  <Link
                    key={c.id}
                    to={buildRoute(ROUTES.blogCategory, { categorySlug: c.slug })}
                    onClick={() => setOpen(false)}
                    className="text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-primary)]"
                  >
                    {c.name}
                  </Link>
                ))}
              </div>
            </div>

            <div>
              <p className="mb-3 text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
                Recent Posts
              </p>
              <div className="flex flex-col gap-3">
                {recentPosts.map((post) => (
                  <Link
                    key={post.id}
                    to={buildRoute(ROUTES.blogPost, { postSlug: post.slug })}
                    onClick={() => setOpen(false)}
                    className="group flex gap-2.5"
                  >
                    <img
                      src={post.coverImage}
                      alt=""
                      className="h-10 w-10 flex-shrink-0 rounded-[var(--radius-btn)] object-cover"
                    />
                    <p className="text-xs leading-snug text-[var(--color-text-secondary)] transition-colors group-hover:text-[var(--color-primary)]">
                      {post.title}
                    </p>
                  </Link>
                ))}
              </div>
            </div>
          </div>
          </div>
        </div>
      )}
    </div>
  );
}
