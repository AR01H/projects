import { Link } from 'react-router-dom';
import { Button } from '@/components/common/Button';
import { Reveal } from '@/components/common/Reveal';
import { ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';

export function CraftsmanshipStory() {
  const { aboutHeadline, aboutParagraphs } = SITE_CONFIG.story;

  return (
    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <div className="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <Reveal className="order-2 lg:order-1">
          <p className="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            Our Story
          </p>
          <h2 className="font-display text-3xl leading-tight text-[var(--color-text-primary)] sm:text-4xl">
            {aboutHeadline}
          </h2>
          {aboutParagraphs.map((para, i) => (
            <p key={i} className="mt-5 text-sm leading-relaxed text-[var(--color-text-secondary)] sm:text-base first:mt-5">
              {para}
            </p>
          ))}
          <div className="mt-8">
            <Link to={ROUTES.about}>
              <Button variant="outline">Read Our Full Story</Button>
            </Link>
          </div>
        </Reveal>
        <Reveal delay={150} className="order-1 grid grid-cols-2 gap-4 lg:order-2">
          <img
            src="https://picsum.photos/seed/rhh-story-main/1200/675"
            alt="Behind the scenes"
            className="col-span-2 aspect-[16/9] w-full rounded-[var(--radius-card)] object-cover shadow-[var(--shadow-card)]"
          />
          <img
            src="https://picsum.photos/seed/rhh-story-1/600/600"
            alt="Detail shot"
            className="aspect-square w-full rounded-[var(--radius-card)] object-cover shadow-[var(--shadow-card)]"
          />
          <img
            src="https://picsum.photos/seed/rhh-story-2/600/600"
            alt="Process detail"
            className="aspect-square w-full rounded-[var(--radius-card)] object-cover shadow-[var(--shadow-card)]"
          />
        </Reveal>
      </div>
    </section>
  );
}
