import { useParams } from 'react-router-dom';
import { getCustomContentPage } from '@/config/customContentPages';
import { PageHero } from '@/components/common/PageHero';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { EmptyState } from '@/components/common/EmptyState';
import { SEO } from '@/components/common/SEO';

export default function CustomContentPage() {
  const { pageKey } = useParams();
  const page = pageKey ? getCustomContentPage(pageKey) : undefined;

  if (!page) {
    return (
      <div className="mx-auto max-w-2xl px-4 py-24">
        <EmptyState
          title="Page not found"
          description="This page hasn't been configured yet. Add it to src/config/customContentPages.ts."
        />
      </div>
    );
  }

  return (
    <div>
      <SEO title={page.title} description={page.description ?? `${page.title} — Royal Heritage Hub`} />
      <PageHero pageKey="faqs" fallbackTitle={page.title} />

      <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <Breadcrumbs items={[{ label: page.title }]} />

        <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">{page.title}</h1>
        {page.description && (
          <p className="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)]">{page.description}</p>
        )}

        {page.iframeUrl && (
          <div className="mt-8 overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] shadow-[var(--shadow-card)]">
            <iframe
              src={page.iframeUrl}
              title={page.title}
              width="100%"
              height={page.iframeHeight ?? 500}
              style={{ border: 0 }}
              loading="lazy"
              allowFullScreen
            />
          </div>
        )}

        {page.html && (
          <div
            className="mt-8 text-sm leading-relaxed text-[var(--color-text-secondary)]"
            // eslint-disable-next-line react/no-danger
            dangerouslySetInnerHTML={{ __html: page.html }}
          />
        )}

        {!page.iframeUrl && !page.html && (
          <p className="mt-8 text-sm text-[var(--color-text-muted)]">
            No content configured for this page yet.
          </p>
        )}
      </div>
    </div>
  );
}
