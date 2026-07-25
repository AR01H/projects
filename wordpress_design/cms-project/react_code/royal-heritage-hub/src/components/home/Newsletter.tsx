import { useState } from 'react';
import { Button } from '@/components/common/Button';
import { SITE_CONFIG } from '@/config/site';

export function Newsletter() {
  const [email, setEmail] = useState('');
  const [submitted, setSubmitted] = useState(false);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (email.trim()) setSubmitted(true);
  }

  return (
    <section className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
      <div className="rounded-[var(--radius-card)] bg-[var(--color-dark)] px-8 py-14 text-center sm:px-16">
        <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary)]">
          Stay Connected
        </p>
        <h2 className="font-display text-2xl text-[var(--color-bg-light)] sm:text-3xl">
          Get 10% Off Your First Order
        </h2>
        <p className="mx-auto mt-3 max-w-md text-sm text-[var(--color-bg-light)]/75">
          {SITE_CONFIG.microcopy.newsletterDescription}
        </p>
        {submitted ? (
          <p className="mt-6 font-display text-[var(--color-secondary)]">
            Thank you — check your inbox for your welcome code!
          </p>
        ) : (
          <form onSubmit={handleSubmit} className="mx-auto mt-7 flex max-w-md gap-3">
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="Enter your email"
              className="w-full rounded-[var(--radius-btn)] border border-[var(--color-bg-light)]/20 bg-[var(--color-bg-light)]/10 px-4 py-3 text-sm text-[var(--color-bg-light)] outline-none placeholder:text-[var(--color-bg-light)]/50 focus:border-[var(--color-secondary)]"
            />
            <Button type="submit" variant="secondary">
              Subscribe
            </Button>
          </form>
        )}
      </div>
    </section>
  );
}
