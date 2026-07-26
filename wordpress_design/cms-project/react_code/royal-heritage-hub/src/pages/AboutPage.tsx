import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { PageHero } from '@/components/common/PageHero';
import { SEO } from '@/components/common/SEO';
import { Reveal } from '@/components/common/Reveal';
import { Button } from '@/components/common/Button';
import { SITE_CONFIG } from '@/config/site';
import { ROUTES } from '@/config/routes';
import { certificationsApi } from '@/api/certifications';
import type { CertificationEntry } from '@/types/product';

const CRAFT_TRADITIONS = [
  {
    name: 'Kondapalli Bommalu',
    region: 'Andhra Pradesh',
    age: '400+ years',
    description: 'Softwood toys hand-carved and painted with natural pigments in the village of Kondapalli. A GI-tagged tradition recognised by the Government of India.',
    image: 'https://picsum.photos/seed/about-kondapalli/600/400',
  },
  {
    name: 'Channapatna Toys',
    region: 'Karnataka',
    age: '200+ years',
    description: 'Ivory wood toys finished with vegetable-dye lacquer, known for their vibrant colours and non-toxic finish safe for children.',
    image: 'https://picsum.photos/seed/about-channapatna/600/400',
  },
  {
    name: 'Saharanpur Wood Carving',
    region: 'Uttar Pradesh',
    age: '400+ years',
    description: 'Intricate relief carvings on mango and sheesham wood, creating heirloom furniture, decor, and jewellery boxes.',
    image: 'https://picsum.photos/seed/about-saharanpur/600/400',
  },
  {
    name: 'Moradabad Brass Work',
    region: 'Uttar Pradesh',
    age: '300+ years',
    description: 'Lost-wax and hand-hammered brass artefacts — from Ganesha idols to festive diyas — cast by master brass-smiths.',
    image: 'https://picsum.photos/seed/about-moradabad/600/400',
  },
];

const TIMELINE = [
  { year: '2023', title: 'Founded', description: 'Royal Heritage Hub begins with a vision to connect artisan families directly with modern homes.' },
  { year: '2024', title: 'GI Recognition', description: 'Partnership with Kondapalli artisans secured GI-tagged products for authentic craft preservation.' },
  { year: '2024', title: 'MSME Certified', description: 'Registered as a Micro, Small & Medium Enterprise, supporting India\'s artisan economy.' },
  { year: '2025', title: 'Growing Collection', description: 'Expanded to 8+ craft traditions across 7 Indian states with 50+ artisan partners.' },
  { year: '2026', title: 'Export Ready', description: 'EPCH membership opens doors to share Indian handcraft with the world.' },
];

const STATS = [
  { number: '50+', label: 'Artisan Partners' },
  { number: '8', label: 'Craft Traditions' },
  { number: '7', label: 'Indian States' },
  { number: '400+', label: 'Years of Heritage' },
];

export default function AboutPage() {
  const [certifications, setCertifications] = useState<CertificationEntry[]>([]);

  useEffect(() => {
    certificationsApi.getAll().then((data) => { if (data) setCertifications(data); });
  }, []);

  return (
    <div>
      <SEO title="About Us" description="Learn about Royal Heritage Hub — four hundred years of Indian craft, carried forward by hand. Our mission to connect artisan families with modern homes." />
      <PageHero pageKey="about" fallbackTitle="Our Story" />

      {/* ═══════════════════════════════════════════════════════════════
          SECTION 1 — Brand Story with Stats
          ═══════════════════════════════════════════════════════════════ */}
      <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div className="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
          <Reveal>
            <p className="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
              Our Story
            </p>
            <h2 className="font-display text-3xl leading-tight text-[var(--color-text-primary)] sm:text-4xl lg:text-5xl">
              {SITE_CONFIG.story.aboutHeadline}
            </h2>
            {SITE_CONFIG.story.aboutParagraphs.map((para, i) => (
              <p key={i} className="mt-5 text-sm leading-relaxed text-[var(--color-text-secondary)] sm:text-base">
                {para}
              </p>
            ))}
            <div className="mt-8">
              <Link to={ROUTES.shop}>
                <Button variant="primary">Explore Our Collection</Button>
              </Link>
            </div>
          </Reveal>

          <Reveal delay={200}>
            <div className="relative">
              <img
                src="https://picsum.photos/seed/about-hero-main/800/600"
                alt="Artisan at work"
                className="rounded-[var(--radius-card)] shadow-[var(--shadow-hover)]"
              />
              <div className="absolute -bottom-6 -left-6 rounded-[var(--radius-card)] bg-[var(--color-bg-light)] p-5 shadow-[var(--shadow-card)]">
                <p className="font-display text-3xl text-[var(--color-primary)]">400+</p>
                <p className="text-xs text-[var(--color-text-secondary)]">Years of Craft Heritage</p>
              </div>
            </div>
          </Reveal>
        </div>

        {/* Stats row */}
        <div className="mt-20 grid grid-cols-2 gap-6 sm:grid-cols-4">
          {STATS.map((stat, i) => (
            <Reveal key={stat.label} delay={i * 100}>
              <div className="text-center">
                <p className="font-display text-4xl text-[var(--color-primary)] sm:text-5xl">{stat.number}</p>
                <p className="mt-2 text-sm text-[var(--color-text-secondary)]">{stat.label}</p>
              </div>
            </Reveal>
          ))}
        </div>
      </section>

      {/* ═══════════════════════════════════════════════════════════════
          SECTION 2 — Mission & Values
          ═══════════════════════════════════════════════════════════════ */}
      <section className="bg-[var(--color-bg-cream)] py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="mb-14 text-center">
            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
              What Drives Us
            </p>
            <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
              Our Mission & Values
            </h2>
          </Reveal>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
            {[
              {
                icon: (
                  <svg viewBox="0 0 24 24" className="h-8 w-8" fill="none" stroke="var(--color-primary)" strokeWidth="1.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    <path d="M9 12l2 2 4-4" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                ),
                title: 'Preserve Heritage',
                description: 'We exist to keep centuries-old Indian craft traditions alive by creating real economic value for artisan families.',
              },
              {
                icon: (
                  <svg viewBox="0 0 24 24" className="h-8 w-8" fill="none" stroke="var(--color-primary)" strokeWidth="1.5">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 6v6l4 2" strokeLinecap="round" />
                  </svg>
                ),
                title: 'Fair & Direct',
                description: 'Every purchase goes directly to the artisan. No middlemen, no exploitative pricing — just fair pay for masterful work.',
              },
              {
                icon: (
                  <svg viewBox="0 0 24 24" className="h-8 w-8" fill="none" stroke="var(--color-primary)" strokeWidth="1.5">
                    <path d="M12 2l2.5 6.5L21 11l-6.5 2.5L12 20l-2.5-6.5L3 11l6.5-2.5L12 2z" />
                  </svg>
                ),
                title: 'Quality Without Compromise',
                description: 'Every piece is inspected, every material sourced ethically, and every finish meets the standard our artisans set for themselves.',
              },
            ].map((value, i) => (
              <Reveal key={value.title} delay={i * 120}>
                <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-8 text-center shadow-[var(--shadow-card)] transition-shadow duration-300 hover:shadow-[var(--shadow-hover)]">
                  <div className="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full border border-[var(--color-secondary)]/30 bg-[var(--color-bg-cream)]">
                    {value.icon}
                  </div>
                  <h3 className="font-display text-lg text-[var(--color-text-primary)]">{value.title}</h3>
                  <p className="mt-3 text-sm leading-relaxed text-[var(--color-text-secondary)]">{value.description}</p>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* ═══════════════════════════════════════════════════════════════
          SECTION 3 — Craft Traditions
          ═══════════════════════════════════════════════════════════════ */}
      <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <Reveal className="mb-14 text-center">
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            The Crafts We Champion
          </p>
          <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
            Heritage Craft Traditions
          </h2>
          <p className="mx-auto mt-3 max-w-xl text-sm text-[var(--color-text-secondary)]">
            Each region of India has cultivated its own distinct craft identity over centuries. Here are the traditions we work to preserve.
          </p>
        </Reveal>

        <div className="flex flex-col gap-10">
          {CRAFT_TRADITIONS.map((craft, i) => (
            <Reveal key={craft.name} delay={i * 80}>
              <div className={`grid items-center gap-8 lg:grid-cols-2 lg:gap-14 ${i % 2 === 1 ? 'lg:direction-rtl' : ''}`}>
                <div className={i % 2 === 1 ? 'lg:order-2' : ''}>
                  <div className="overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]">
                    <img
                      src={craft.image}
                      alt={craft.name}
                      className="aspect-[3/2] w-full object-cover transition-transform duration-700 hover:scale-105"
                    />
                  </div>
                </div>
                <div className={i % 2 === 1 ? 'lg:order-1' : ''}>
                  <div className="flex items-center gap-3 mb-3">
                    <span className="rounded-full bg-[var(--color-secondary)]/15 px-3 py-1 text-xs font-semibold text-[var(--color-secondary-dark)]">
                      {craft.age}
                    </span>
                    <span className="text-xs text-[var(--color-text-muted)]">{craft.region}</span>
                  </div>
                  <h3 className="font-display text-2xl text-[var(--color-text-primary)]">{craft.name}</h3>
                  <p className="mt-4 text-sm leading-relaxed text-[var(--color-text-secondary)]">{craft.description}</p>
                  <Link
                    to={`${ROUTES.shop}?search=${encodeURIComponent(craft.region.split(',')[0])}`}
                    className="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-[var(--color-primary)] transition-colors hover:text-[var(--color-primary-dark)]"
                  >
                    Shop {craft.name}
                    <svg viewBox="0 0 16 16" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2">
                      <path d="M3 8h10M9 4l4 4-4 4" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </Link>
                </div>
              </div>
            </Reveal>
          ))}
        </div>
      </section>

      {/* ═══════════════════════════════════════════════════════════════
          SECTION 4 — Timeline
          ═══════════════════════════════════════════════════════════════ */}
      <section className="bg-[var(--color-bg-cream)] py-20">
        <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
          <Reveal className="mb-14 text-center">
            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
              Our Journey
            </p>
            <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
              From Village to Home
            </h2>
          </Reveal>

          <div className="relative">
            {/* Vertical line */}
            <div className="absolute left-6 top-0 bottom-0 w-px bg-[var(--color-border)] sm:left-1/2" />

            <div className="flex flex-col gap-10">
              {TIMELINE.map((item, i) => (
                <Reveal key={item.year} delay={i * 100}>
                  <div className={`relative flex items-start gap-6 sm:gap-0 ${i % 2 === 0 ? 'sm:flex-row' : 'sm:flex-row-reverse'}`}>
                    {/* Dot */}
                    <div className="absolute left-6 top-1 z-10 h-4 w-4 -translate-x-1/2 rounded-full border-2 border-[var(--color-primary)] bg-[var(--color-bg-light)] sm:left-1/2" />

                    {/* Content */}
                    <div className={`ml-12 flex-1 sm:ml-0 ${i % 2 === 0 ? 'sm:pr-12 sm:text-right' : 'sm:pl-12'}`}>
                      <span className="inline-block rounded-full bg-[var(--color-primary)] px-3 py-0.5 text-xs font-semibold text-[var(--color-bg-light)]">
                        {item.year}
                      </span>
                      <h3 className="mt-2 font-display text-lg text-[var(--color-text-primary)]">{item.title}</h3>
                      <p className="mt-1 text-sm text-[var(--color-text-secondary)]">{item.description}</p>
                    </div>

                    {/* Spacer for alternating layout */}
                    <div className="hidden flex-1 sm:block" />
                  </div>
                </Reveal>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ═══════════════════════════════════════════════════════════════
          SECTION 5 — Certifications
          ═══════════════════════════════════════════════════════════════ */}
      <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <Reveal className="mb-14 text-center">
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            Trusted & Certified
          </p>
          <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
            Government Recognised
          </h2>
          <p className="mx-auto mt-3 max-w-xl text-sm text-[var(--color-text-secondary)]">
            Our commitment to authenticity is backed by official certifications from the Government of India.
          </p>
        </Reveal>

        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
          {certifications.map((cert, i) => (
            <Reveal key={cert.id} delay={i * 100}>
              <div className={`flex flex-col gap-5 overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] shadow-[var(--shadow-card)] sm:flex-row ${cert.imageSide === 'right' ? 'sm:flex-row-reverse' : ''}`}>
                <div className="h-48 w-full flex-shrink-0 sm:h-auto sm:w-48">
                  <img
                    src={cert.image}
                    alt={cert.title}
                    className="h-full w-full object-cover"
                  />
                </div>
                <div className="flex flex-1 flex-col justify-center p-5">
                  <div className="mb-2 flex items-center gap-2">
                    <svg viewBox="0 0 24 24" className="h-5 w-5 flex-shrink-0" fill="none" stroke="var(--color-secondary)" strokeWidth="1.5">
                      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                      <path d="M9 12l2 2 4-4" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                    <span className="text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-secondary-dark)]">
                      Certified
                    </span>
                  </div>
                  <h3 className="font-display text-base text-[var(--color-text-primary)]">{cert.title}</h3>
                  <p className="mt-1 text-xs text-[var(--color-text-muted)]">{cert.issuedBy}</p>
                  <p className="mt-3 text-xs leading-relaxed text-[var(--color-text-secondary)]">{cert.description}</p>
                </div>
              </div>
            </Reveal>
          ))}
        </div>
      </section>

      {/* ═══════════════════════════════════════════════════════════════
          SECTION 6 — Testimonials
          ═══════════════════════════════════════════════════════════════ */}
      <section className="bg-[var(--color-bg-cream)] py-20">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="mb-14 text-center">
            <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
              What People Say
            </p>
            <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
              Customer Stories
            </h2>
          </Reveal>

          <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
            {SITE_CONFIG.testimonials.map((t, i) => (
              <Reveal key={t.name} delay={i * 120}>
                <div className="flex h-full flex-col rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6 shadow-[var(--shadow-card)]">
                  {/* Stars */}
                  <div className="mb-3 flex gap-0.5">
                    {Array.from({ length: 5 }).map((_, j) => (
                      <svg key={j} viewBox="0 0 16 16" className={`h-4 w-4 ${j < t.rating ? 'text-[var(--color-secondary)]' : 'text-[var(--color-border)]'}`} fill="currentColor">
                        <path d="M8 0l2.2 4.5L15 5.3l-3.5 3.4.8 4.8L8 11.3 3.7 13.5l.8-4.8L1 5.3l4.8-.8L8 0z" />
                      </svg>
                    ))}
                  </div>
                  <p className="flex-1 text-sm leading-relaxed text-[var(--color-text-secondary)]">"{t.text}"</p>
                  <p className="mt-4 text-xs font-semibold text-[var(--color-text-primary)]">{t.name}</p>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* ═══════════════════════════════════════════════════════════════
          SECTION 7 — CTA
          ═══════════════════════════════════════════════════════════════ */}
      <section className="relative overflow-hidden bg-[var(--color-dark)] py-20">
        <div className="absolute inset-0 opacity-10">
          <img
            src="https://picsum.photos/seed/about-cta-bg/1800/600"
            alt=""
            className="h-full w-full object-cover"
          />
        </div>
        <div className="relative mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
          <Reveal>
            <p className="mb-3 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--color-secondary)]">
              Join the Movement
            </p>
            <h2 className="font-display text-3xl text-[var(--color-bg-light)] sm:text-4xl">
              Every Purchase Preserves a Tradition
            </h2>
            <p className="mx-auto mt-4 max-w-lg text-sm text-[var(--color-bg-light)]/70">
              When you buy from Royal Heritage Hub, you're not just getting a beautiful handcrafted piece — you're keeping a centuries-old tradition alive for the next generation.
            </p>
            <div className="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
              <Link to={ROUTES.shop}>
                <Button variant="secondary" size="lg">Shop the Collection</Button>
              </Link>
              <Link to={ROUTES.artisans}>
                <Button variant="outline" size="lg" className="border-[var(--color-bg-light)]/30 text-[var(--color-bg-light)] hover:bg-[var(--color-bg-light)] hover:text-[var(--color-dark)]">
                  Meet Our Artisans
                </Button>
              </Link>
            </div>
          </Reveal>
        </div>
      </section>
    </div>
  );
}
