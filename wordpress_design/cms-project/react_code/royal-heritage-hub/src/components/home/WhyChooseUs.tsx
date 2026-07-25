import { SITE_CONFIG } from '@/config/site';
import { Reveal } from '@/components/common/Reveal';

const ICONS: Record<string, React.ReactNode> = {
  sparkle: <path d="M12 2l2.5 6.5L21 11l-6.5 2.5L12 20l-2.5-6.5L3 11l6.5-2.5L12 2z" />,
  maker: <path d="M12 12a5 5 0 100-10 5 5 0 000 10zM3 21a9 9 0 0118 0" />,
  shipping: <path d="M3 8h13l4 4v6h-3M3 8v10h3m8-10v10M7 21a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z" />,
  secure: <path d="M3 6h18v12H3zM3 10h18M7 15h4" />,
};

export function WhyChooseUs() {
  return (
    <section className="textured-bg py-16">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <Reveal className="mb-10 text-center">
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            Our Promise
          </p>
          <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
            Why Choose {SITE_CONFIG.brand.name}
          </h2>
        </Reveal>
        <div className="grid grid-cols-2 gap-8 lg:grid-cols-4">
          {SITE_CONFIG.story.whyChooseUs.map((f, i) => (
            <Reveal key={f.title} delay={i * 100} className="flex flex-col items-center text-center">
              <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[var(--color-secondary)]/40 bg-[var(--color-bg-light)] transition-transform duration-300 hover:scale-110">
                <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="var(--color-primary)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                  {ICONS[f.icon]}
                </svg>
              </div>
              <h3 className="font-display text-base text-[var(--color-text-primary)]">{f.title}</h3>
              <p className="mt-1.5 text-xs leading-relaxed text-[var(--color-text-secondary)]">
                {f.description}
              </p>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
