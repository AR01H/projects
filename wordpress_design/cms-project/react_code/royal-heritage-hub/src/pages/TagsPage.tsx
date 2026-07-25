import { useEffect, useState } from 'react';
import { tagsApi, type TagSummary } from '@/api/tags';
import { PageHero } from '@/components/common/PageHero';
import { TagPill } from '@/components/common/TagPill';
import { Reveal } from '@/components/common/Reveal';
import { SEO } from '@/components/common/SEO';

export default function TagsPage() {
  const [topLevelTags, setTopLevelTags] = useState<TagSummary[]>([]);
  const [allTags, setAllTags] = useState<TagSummary[]>([]);

  useEffect(() => {
    tagsApi.getAll().then(setAllTags);
    tagsApi.getTopLevel().then(setTopLevelTags);
  }, []);

  function childrenOf(parentTag: string) {
    return allTags.filter((t) => t.parentTag === parentTag);
  }

  return (
    <div>
      <SEO title="Tags" description="Browse and explore our collection through the lens of what makes each handcrafted piece unique." />
      <PageHero pageKey="shop" fallbackTitle="Browse by Tag" />
      <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <Reveal className="mb-10 text-center">
          <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">Browse by Tag</h1>
          <p className="mt-2 text-sm text-[var(--color-text-secondary)]">
            Explore our collection through the lens of what makes each piece unique.
          </p>
        </Reveal>

        <div className="flex flex-col gap-8">
          {topLevelTags.map((parent, i) => {
            const children = childrenOf(parent.tag);
            return (
              <Reveal key={parent.tag} delay={i * 60}>
                <div className="flex flex-wrap items-center gap-3">
                  <TagPill tag={parent.tag} label={parent.label} size="sm" variant="filled" />
                  {children.length > 0 && (
                    <span className="text-[var(--color-text-muted)]">→</span>
                  )}
                  {children.map((child) => (
                    <TagPill key={child.tag} tag={child.tag} label={child.label} size="xs" />
                  ))}
                </div>
              </Reveal>
            );
          })}
        </div>
      </div>
    </div>
  );
}
