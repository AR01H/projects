import { useState } from 'react';
import { Button } from './Button';

interface NotifyWhenAvailableProps {
  productName: string;
}

export function NotifyWhenAvailable({ productName }: NotifyWhenAvailableProps) {
  const [email, setEmail] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    const trimmed = email.trim();
    if (!trimmed || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
      setError('Please enter a valid email');
      return;
    }
    setLoading(true);
    // Mock — in production POST to /api/notify
    setTimeout(() => {
      setSubmitted(true);
      setLoading(false);
    }, 800);
  }

  if (submitted) {
    return (
      <div className="mt-4 rounded-[var(--radius-card)] border border-[var(--color-success)]/30 bg-[var(--color-success)]/5 px-4 py-3 text-center">
        <p className="text-sm text-[var(--color-success)]">
          We'll notify you when <strong>{productName}</strong> is back in stock!
        </p>
      </div>
    );
  }

  return (
    <div className="mt-4 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-cream)] px-4 py-4">
      <p className="text-sm font-medium text-[var(--color-text-primary)]">Interested in this product?</p>
      <p className="mt-1 text-xs text-[var(--color-text-muted)]">
        Get notified when <strong>{productName}</strong> is back in stock.
      </p>
      <form onSubmit={handleSubmit} className="mt-3 flex gap-2">
        <input
          type="email"
          value={email}
          onChange={(e) => { setEmail(e.target.value); setError(''); }}
          placeholder="Your email address"
          className="w-full rounded-[var(--radius-btn)] border border-[var(--color-border)] bg-[var(--color-bg-light)] px-3 py-2 text-sm outline-none focus:border-[var(--color-primary)]"
        />
        <Button type="submit" variant="primary" size="sm" isLoading={loading}>
          Notify Me
        </Button>
      </form>
      {error && <p className="mt-1 text-xs text-[var(--color-danger)]">{error}</p>}
    </div>
  );
}
