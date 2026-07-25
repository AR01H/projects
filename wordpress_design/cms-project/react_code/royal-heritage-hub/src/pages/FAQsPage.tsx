import { useState, useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { Reveal } from '@/components/common/Reveal';
import { SITE_CONFIG } from '@/config/site';
import { ROUTES } from '@/config/routes';
import { SEO } from '@/components/common/SEO';

const FAQ_CATEGORIES = [
  {
    id: 'orders',
    label: 'Orders & Shipping',
    icon: (
      <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5">
        <rect x="1" y="3" width="15" height="13" />
        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
        <circle cx="5.5" cy="18.5" r="2.5" />
        <circle cx="18.5" cy="18.5" r="2.5" />
      </svg>
    ),
    faqs: [
      { q: 'What are your shipping charges?', a: `Shipping is free on all orders above ${SITE_CONFIG.currency.symbol}${SITE_CONFIG.shipping.freeShippingThreshold}. Below that, charges are calculated at checkout based on your location. Estimated delivery is ${SITE_CONFIG.shipping.estimatedDeliveryMin}–${SITE_CONFIG.shipping.estimatedDeliveryMax} business days.` },
      { q: 'How long does delivery take?', a: `Most orders arrive within ${SITE_CONFIG.shipping.estimatedDeliveryMin}–${SITE_CONFIG.shipping.estimatedDeliveryMax} business days depending on your location. Metro cities typically receive orders in 2–3 days, while remote areas may take up to ${SITE_CONFIG.shipping.estimatedDeliveryMax} days.` },
      { q: 'Do you offer Cash on Delivery?', a: `Yes, COD is available across India with an additional charge of ${SITE_CONFIG.currency.symbol}${SITE_CONFIG.shipping.codCharge}, shown clearly during checkout.` },
      { q: 'Can I track my order?', a: 'Yes, once your order is shipped, you\'ll receive a tracking link via SMS and email. You can also track your order from your account page.' },
    ],
  },
  {
    id: 'returns',
    label: 'Returns & Exchanges',
    icon: (
      <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M1 4v6h6" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M3.51 15a9 9 0 102.13-9.36L1 10" />
      </svg>
    ),
    faqs: [
      { q: 'Can I return or exchange a product?', a: 'Yes, most items are eligible for return within our return window. Please see our Return Policy page for full details and exceptions for customised items.' },
      { q: 'How do I initiate a return?', a: 'Contact us via WhatsApp or email within 7 days of delivery with your order number and reason for return. We\'ll guide you through the process.' },
      { q: 'When will I get my refund?', a: 'Refunds are processed within 5–7 business days after we receive the returned item. The amount is credited to your original payment method.' },
    ],
  },
  {
    id: 'products',
    label: 'Products & Craft',
    icon: (
      <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M12 2l2.5 6.5L21 11l-6.5 2.5L12 20l-2.5-6.5L3 11l6.5-2.5L12 2z" />
      </svg>
    ),
    faqs: [
      { q: 'Are your products genuinely handmade?', a: 'Yes. Every product is hand-carved, hand-painted, or hand-cast by artisans we work with directly across India. Minor variations in color and finish are part of the handmade character, not a defect.' },
      { q: 'What materials do you use?', a: 'We use traditional materials like Tella Poniki softwood, mango wood, sheesham wood, ivory wood, and solid brass. All paints and finishes are non-toxic and safe.' },
      { q: 'Are the toys safe for children?', a: 'Absolutely. Our Channapatna and Kondapalli toys use vegetable-dye lacquer that is completely non-toxic and safe for toddlers. They meet Indian safety standards.' },
      { q: 'Do products come with certificates?', a: 'GI-tagged products come with authentication. All products include artisan origin details and care instructions.' },
    ],
  },
  {
    id: 'gifting',
    label: 'Gifting & Corporate',
    icon: (
      <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5">
        <path d="M20 12v10H4V12" />
        <path d="M2 7h20v5H2z" />
        <path d="M12 22V7" />
        <path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z" />
        <path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z" />
      </svg>
    ),
    faqs: [
      { q: 'Do you offer bulk or corporate gifting?', a: 'Absolutely — we handle bulk corporate and wedding gifting with custom packaging and personalised gift messages. Reach out via our Contact page for a quote.' },
      { q: 'Can I get gift wrapping?', a: 'Yes! We offer complimentary festive gift wrapping on all orders during the festive season. Year-round, gift wrapping is available for ₹49 per item.' },
      { q: 'Do you send gift messages?', a: 'Yes, you can add a personalised gift message during checkout. We\'ll include a handwritten note card with your message.' },
    ],
  },
  {
    id: 'payment',
    label: 'Payments',
    icon: (
      <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5">
        <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
        <line x1="1" y1="10" x2="23" y2="10" />
      </svg>
    ),
    faqs: [
      { q: 'What payment methods do you accept?', a: 'We accept UPI (Google Pay, PhonePe, Paytm), credit/debit cards (Visa, Mastercard, RuPay), net banking, and Cash on Delivery.' },
      { q: 'Is online payment secure?', a: 'Yes, all online payments are processed through secure, PCI-compliant payment gateways. We never store your card details.' },
      { q: 'Can I pay in instalments?', a: 'Currently we don\'t offer EMI options, but we do have COD available for your convenience.' },
    ],
  },
];

function WaveDivider() {
  return (
    <div className="relative -mt-1 overflow-hidden">
      <svg
        viewBox="0 0 1440 120"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        className="h-16 w-full sm:h-24"
        preserveAspectRatio="none"
      >
        <path
          d="M0,60 C240,120 480,0 720,60 C960,120 1200,0 1440,60 L1440,120 L0,120 Z"
          fill="var(--color-bg)"
        />
        <path
          d="M0,80 C240,40 480,100 720,80 C960,40 1200,100 1440,80 L1440,120 L0,120 Z"
          fill="var(--color-bg)"
          opacity="0.5"
        />
      </svg>
    </div>
  );
}

function MovingImages() {
  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none">
      {/* Floating image 1 */}
      <div className="animate-float absolute -left-20 top-1/4 h-40 w-40 rounded-2xl opacity-20 sm:left-10 sm:h-56 sm:w-56">
        <img src="https://picsum.photos/seed/faq-float-1/400/400" alt="" className="h-full w-full rounded-2xl object-cover" />
      </div>
      {/* Floating image 2 */}
      <div className="animate-float absolute -right-16 top-1/3 h-32 w-32 rounded-2xl opacity-15 sm:right-20 sm:h-44 sm:w-44" style={{ animationDelay: '1s' }}>
        <img src="https://picsum.photos/seed/faq-float-2/400/400" alt="" className="h-full w-full rounded-2xl object-cover" />
      </div>
      {/* Floating image 3 */}
      <div className="animate-float absolute bottom-10 left-1/4 h-24 w-24 rounded-2xl opacity-10 sm:h-36 sm:w-36" style={{ animationDelay: '2s' }}>
        <img src="https://picsum.photos/seed/faq-float-3/400/400" alt="" className="h-full w-full rounded-2xl object-cover" />
      </div>
    </div>
  );
}

export default function FAQsPage() {
  const [activeCategory, setActiveCategory] = useState(FAQ_CATEGORIES[0].id);
  const [openIndex, setOpenIndex] = useState<number | null>(0);
  const [searchQuery, setSearchQuery] = useState('');

  const activeFaqs = FAQ_CATEGORIES.find((c) => c.id === activeCategory)?.faqs ?? [];
  const filteredFaqs = searchQuery
    ? FAQ_CATEGORIES.flatMap((c) => c.faqs).filter(
        (f) =>
          f.q.toLowerCase().includes(searchQuery.toLowerCase()) ||
          f.a.toLowerCase().includes(searchQuery.toLowerCase())
      )
    : activeFaqs;

  return (
    <div className="pb-12">
      <SEO title="FAQs" description="Find answers to frequently asked questions about shopping, shipping, returns, and payments at Royal Heritage Hub." />
      {/* ═══ HERO with wave ═══ */}
      <section className="relative overflow-hidden bg-[var(--color-dark)]">
        <MovingImages />
        <div className="relative z-10 mx-auto max-w-3xl px-4 py-20 text-center sm:py-28">
          <Reveal>
            <p className="mb-3 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--color-secondary)]">
              Help Centre
            </p>
            <h1 className="font-display text-3xl text-[var(--color-bg-light)] sm:text-4xl lg:text-5xl">
              Frequently Asked Questions
            </h1>
            <p className="mx-auto mt-4 max-w-lg text-sm text-[var(--color-bg-light)]/70">
              Everything you need to know about shopping with Royal Heritage Hub.
            </p>

            {/* Search bar */}
            <div className="mx-auto mt-8 max-w-md">
              <div className="flex items-center gap-3 rounded-full border border-white/20 bg-white/10 px-5 py-3 backdrop-blur-sm">
                <svg viewBox="0 0 24 24" className="h-5 w-5 flex-shrink-0 text-white/50" fill="none" stroke="currentColor" strokeWidth="2">
                  <circle cx="11" cy="11" r="8" />
                  <path d="M21 21l-4.3-4.3" strokeLinecap="round" />
                </svg>
                <input
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Search questions..."
                  className="w-full bg-transparent text-sm text-white outline-none placeholder:text-white/40"
                />
                {searchQuery && (
                  <button onClick={() => setSearchQuery('')} className="text-white/50 hover:text-white">
                    <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2">
                      <path d="M6 6l12 12M18 6L6 18" strokeLinecap="round" />
                    </svg>
                  </button>
                )}
              </div>
            </div>
          </Reveal>
        </div>
        <WaveDivider />
      </section>

      {/* ═══ FAQ CATEGORIES + ACCORDION ═══ */}
      <section className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[220px_1fr]">
          {/* Category sidebar */}
          <aside className="flex flex-col gap-2 lg:sticky lg:top-28 lg:self-start">
            {FAQ_CATEGORIES.map((cat) => (
              <button
                key={cat.id}
                onClick={() => {
                  setActiveCategory(cat.id);
                  setOpenIndex(0);
                  setSearchQuery('');
                }}
                className={`flex items-center gap-3 rounded-[var(--radius-btn)] px-4 py-3 text-left text-sm transition-all ${
                  activeCategory === cat.id && !searchQuery
                    ? 'bg-[var(--color-primary)] text-[var(--color-bg-light)] shadow-[var(--shadow-card)]'
                    : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-cream)]'
                }`}
              >
                <span className={activeCategory === cat.id && !searchQuery ? 'text-[var(--color-bg-light)]' : 'text-[var(--color-primary)]'}>
                  {cat.icon}
                </span>
                <span className="font-medium">{cat.label}</span>
              </button>
            ))}
          </aside>

          {/* FAQ accordion */}
          <div>
            <div className="mb-4">
              <h2 className="font-display text-xl text-[var(--color-text-primary)]">
                {searchQuery ? `Search Results` : FAQ_CATEGORIES.find((c) => c.id === activeCategory)?.label}
              </h2>
              <p className="text-xs text-[var(--color-text-muted)]">
                {filteredFaqs.length} {filteredFaqs.length === 1 ? 'question' : 'questions'}
              </p>
            </div>

            {filteredFaqs.length === 0 ? (
              <div className="py-12 text-center">
                <svg viewBox="0 0 24 24" className="mx-auto h-10 w-10 text-[var(--color-text-muted)]" fill="none" stroke="currentColor" strokeWidth="1.5">
                  <circle cx="11" cy="11" r="8" />
                  <path d="M21 21l-4.3-4.3" strokeLinecap="round" />
                </svg>
                <p className="mt-3 text-sm text-[var(--color-text-muted)]">No matching questions found.</p>
                <button onClick={() => setSearchQuery('')} className="mt-2 text-sm font-medium text-[var(--color-primary)] underline">
                  Clear search
                </button>
              </div>
            ) : (
              <div className="flex flex-col gap-3">
                {filteredFaqs.map((faq, i) => (
                  <Reveal key={faq.q} delay={i * 60}>
                    <div className="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] transition-shadow hover:shadow-[var(--shadow-card)]">
                      <button
                        onClick={() => setOpenIndex(openIndex === i ? null : i)}
                        className="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                      >
                        <span className="font-display text-sm font-medium text-[var(--color-text-primary)] sm:text-base">
                          {faq.q}
                        </span>
                        <div
                          className={`flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full transition-all duration-300 ${
                            openIndex === i
                              ? 'rotate-180 bg-[var(--color-primary)] text-[var(--color-bg-light)]'
                              : 'bg-[var(--color-bg-cream)] text-[var(--color-text-muted)]'
                          }`}
                        >
                          <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2.5">
                            <path d="M6 9l6 6 6-6" strokeLinecap="round" strokeLinejoin="round" />
                          </svg>
                        </div>
                      </button>
                      {openIndex === i && (
                        <div className="animate-fade-in-up border-t border-[var(--color-border-soft)] px-6 py-4 text-sm leading-relaxed text-[var(--color-text-secondary)]">
                          {faq.a}
                        </div>
                      )}
                    </div>
                  </Reveal>
                ))}
              </div>
            )}
          </div>
        </div>
      </section>

      {/* ═══ WAVE CTA ═══ */}
      <section className="relative bg-[var(--color-bg-cream)]">
        <svg
          viewBox="0 0 1440 80"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          className="h-12 w-full sm:h-16"
          preserveAspectRatio="none"
        >
          <path
            d="M0,40 C360,80 720,0 1080,40 C1260,60 1380,20 1440,40 L1440,0 L0,0 Z"
            fill="var(--color-bg)"
          />
        </svg>
        <div className="mx-auto max-w-3xl px-4 py-12 text-center sm:py-16">
          <Reveal>
            <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">
              Still Have Questions?
            </h2>
            <p className="mx-auto mt-3 max-w-md text-sm text-[var(--color-text-secondary)]">
              Our team typically responds within 2 hours during business hours.
            </p>
            <div className="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
              <a
                href={SITE_CONFIG.contact.phoneHref}
                className="rounded-[var(--radius-btn)] bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-[var(--color-bg-light)] transition-colors hover:bg-[var(--color-primary-dark)]"
              >
                Call Us
              </a>
              <a
                href="https://wa.me/917887699208"
                target="_blank"
                rel="noreferrer"
                className="rounded-[var(--radius-btn)] border border-[#25D366] bg-[#25D366]/10 px-6 py-3 text-sm font-medium text-[#25D366] transition-colors hover:bg-[#25D366]/20"
              >
                WhatsApp Us
              </a>
              <Link
                to={ROUTES.contact}
                className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-6 py-3 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-bg-light)]"
              >
                Contact Form
              </Link>
            </div>
          </Reveal>
        </div>
        <svg
          viewBox="0 0 1440 80"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          className="h-12 w-full sm:h-16"
          preserveAspectRatio="none"
        >
          <path
            d="M0,40 C360,0 720,80 1080,40 C1260,20 1380,60 1440,40 L1440,80 L0,80 Z"
            fill="var(--color-bg)"
          />
        </svg>
      </section>
    </div>
  );
}
