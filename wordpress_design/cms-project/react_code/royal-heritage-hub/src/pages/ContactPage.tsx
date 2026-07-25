import { useState } from 'react';
import { CONTACT } from '@/config/constants';
import { Button } from '@/components/common/Button';
import { PageHero } from '@/components/common/PageHero';

export default function ContactPage() {
  const [sent, setSent] = useState(false);

  return (
    <div>
      <PageHero pageKey="contact" fallbackTitle="Get in Touch" />
      <div className="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
      <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">Get in Touch</h1>
      <p className="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)]">
        Questions about an order, bulk gifting, or a custom piece? We'd love to hear from you.
      </p>

      <div className="mt-10 grid grid-cols-1 gap-10 lg:grid-cols-2">
        <div className="flex flex-col gap-4">
          <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-5">
            <p className="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Phone</p>
            <a href={CONTACT.phoneHref} className="mt-1 block font-display text-lg text-[var(--color-primary)]">
              {CONTACT.phone}
            </a>
          </div>
          <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-5">
            <p className="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Email</p>
            <a href={CONTACT.emailHref} className="mt-1 block font-display text-lg text-[var(--color-primary)]">
              {CONTACT.email}
            </a>
          </div>
        </div>

        {sent ? (
          <div className="flex items-center justify-center rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-cream)] p-10 text-center">
            <p className="font-display text-lg text-[var(--color-text-primary)]">
              Thank you — we'll get back to you within 24 hours.
            </p>
          </div>
        ) : (
          <form
            onSubmit={(e) => {
              e.preventDefault();
              setSent(true);
            }}
            className="flex flex-col gap-4"
          >
            <input required placeholder="Your Name" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
            <input required type="email" placeholder="Your Email" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
            <textarea required rows={5} placeholder="Your Message" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
            <Button type="submit" variant="primary" size="lg">
              Send Message
            </Button>
          </form>
        )}
      </div>
      </div>
    </div>
  );
}
