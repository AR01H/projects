import { Rating } from '@/components/common/Rating';
import { SectionHeading } from '@/components/common/SectionHeading';
import { Reveal } from '@/components/common/Reveal';
import { SITE_CONFIG } from '@/config/site';

export function Testimonials() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Loved by Customers" title="What They're Saying" align="center" />
      <div className="grid gap-6 sm:grid-cols-3">
        {SITE_CONFIG.testimonials.map((t, i) => (
          <Reveal key={t.name} delay={i * 120}>
            <div className="flex h-full flex-col gap-4 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6 shadow-[var(--shadow-soft)] transition-transform duration-300 hover:-translate-y-1">
              <Rating value={t.rating} size="md" />
              <p className="text-sm leading-relaxed text-[var(--color-text-secondary)]">"{t.text}"</p>
              <p className="mt-auto font-display text-sm text-[var(--color-text-primary)]">{t.name}</p>
            </div>
          </Reveal>
        ))}
      </div>
    </section>
  );
}
