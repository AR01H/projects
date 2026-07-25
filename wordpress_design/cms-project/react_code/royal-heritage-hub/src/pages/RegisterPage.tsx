import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '@/store/useAuthStore';
import { Button } from '@/components/common/Button';
import { SEO } from '@/components/common/SEO';
import { ROUTES } from '@/config/routes';
import { texts } from '@/config/texts';

export default function RegisterPage() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [localError, setLocalError] = useState('');
  const { register, isLoading, error } = useAuthStore();
  const navigate = useNavigate();

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLocalError('');
    if (password !== confirm) {
      setLocalError('Passwords do not match');
      return;
    }
    if (password.length < 6) {
      setLocalError('Password must be at least 6 characters');
      return;
    }
    await register(name, email, password, phone || undefined);
    navigate(ROUTES.home);
  }

  return (
    <div className="mx-auto max-w-md px-4 py-20">
      <SEO title="Register" description={`Create your ${texts.common.search} account`} />

      <h1 className="text-center font-display text-3xl text-[var(--color-text-primary)]">Create Account</h1>
      <p className="mt-2 text-center text-sm text-[var(--color-text-secondary)]">
        Join us and start shopping
      </p>

      <form onSubmit={handleSubmit} className="mt-8 flex flex-col gap-4">
        <input
          required
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Full Name"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />
        <input
          required
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="Email address"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />
        <input
          type="tel"
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
          placeholder="Phone number (optional)"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />
        <input
          required
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          placeholder="Password (min 6 characters)"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />
        <input
          required
          type="password"
          value={confirm}
          onChange={(e) => setConfirm(e.target.value)}
          placeholder="Confirm password"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />

        {(localError || error) && (
          <p className="text-xs text-[var(--color-danger)]">{localError || error}</p>
        )}

        <Button type="submit" variant="primary" fullWidth size="lg" isLoading={isLoading}>
          Create Account
        </Button>
      </form>

      <p className="mt-6 text-center text-sm text-[var(--color-text-secondary)]">
        Already have an account?{' '}
        <Link to="/login" className="font-medium text-[var(--color-primary)] hover:underline">
          Sign in
        </Link>
      </p>
    </div>
  );
}
