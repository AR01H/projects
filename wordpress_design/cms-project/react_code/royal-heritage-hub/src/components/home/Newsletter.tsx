import { useState, useEffect } from 'react';
import { Button } from '@/components/common/Button';
import { SITE_CONFIG } from '@/config/site';

const STORAGE_KEY = 'rhh_newsletter_subscribed';

export function Newsletter() {
  const [email, setEmail] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (localStorage.getItem(STORAGE_KEY)) setSubmitted(true);
  }, []);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');

    const trimmed = email.trim();
    if (!trimmed) {
      setError('Please enter your email');
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
      setError('Please enter a valid email address');
      return;
    }

    setLoading(true);
    // Mock subscription — in production this would POST to an API
    setTimeout(() => {
      localStorage.setItem(STORAGE_KEY, '1');
      setSubmitted(true);
      setLoading(false);
    }, 800);
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
          <div className="mt-6 flex flex-col items-center gap-2">
            <svg viewBox="0 0 24 24" className="h-8 w-8" fill="none" stroke="var(--color-secondary)" strokeWidth="2">
              <path d="M20 6L9 17l-5-5" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            <p className="font-display text-[var(--color-secondary)]">
              You're all set! Check your inbox for your welcome code.
            </p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="mx-auto mt-7 max-w-md">
            <div className="flex gap-3">
              <input
                type="email"
                value={email}
                onChange={(e) => { setEmail(e.target.value); setError(''); }}
                placeholder="Enter your email"
                className="w-full rounded-[var(--radius-btn)] border border-[var(--color-bg-light)]/20 bg-[var(--color-bg-light)]/10 px-4 py-3 text-sm text-[var(--color-bg-light)] outline-none placeholder:text-[var(--color-bg-light)]/50 focus:border-[var(--color-secondary)]"
              />
              <Button type="submit" variant="secondary" isLoading={loading}>
                Subscribe
              </Button>
            </div>
            {error && <p className="mt-2 text-xs text-[var(--color-secondary)]">{error}</p>}
          </form>
        )}
      </div>
    </section>
  );
}
