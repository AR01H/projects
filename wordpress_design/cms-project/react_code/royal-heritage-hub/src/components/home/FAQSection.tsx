import { useState } from 'react';
import { SectionHeading } from '@/components/common/SectionHeading';
import { SITE_CONFIG } from '@/config/site';

export function FAQSection() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);
  const faqs = SITE_CONFIG.faqs;

  return (
    <section className="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Have Questions?" title="Frequently Asked Questions" align="center" />
      <div className="flex flex-col divide-y divide-[var(--color-border)] rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)]">
        {faqs.map((faq, i) => (
          <div key={faq.q}>
            <button
              onClick={() => setOpenIndex(openIndex === i ? null : i)}
              className="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
            >
              <span className="font-display text-base text-[var(--color-text-primary)]">{faq.q}</span>
              <svg
                viewBox="0 0 24 24"
                className={`h-4 w-4 flex-shrink-0 transition-transform duration-300 ${openIndex === i ? 'rotate-45' : ''}`}
                fill="none"
                stroke="var(--color-primary)"
                strokeWidth="2"
              >
                <path d="M12 5v14M5 12h14" strokeLinecap="round" />
              </svg>
            </button>
            {openIndex === i && (
              <div className="animate-fade-in-up px-6 pb-5 text-sm leading-relaxed text-[var(--color-text-secondary)]">
                {faq.a}
              </div>
            )}
          </div>
        ))}
      </div>
    </section>
  );
}
