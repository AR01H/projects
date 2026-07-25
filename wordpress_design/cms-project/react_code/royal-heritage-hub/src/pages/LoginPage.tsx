import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '@/store/useAuthStore';
import { Button } from '@/components/common/Button';
import { SEO } from '@/components/common/SEO';
import { ROUTES } from '@/config/routes';
import { texts } from '@/config/texts';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const { login, isLoading, error } = useAuthStore();
  const navigate = useNavigate();

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    await login(email, password);
    navigate(ROUTES.home);
  }

  return (
    <div className="mx-auto max-w-md px-4 py-20">
      <SEO title="Login" description={`Sign in to your ${texts.common.search} account`} />

      <h1 className="text-center font-display text-3xl text-[var(--color-text-primary)]">Welcome Back</h1>
      <p className="mt-2 text-center text-sm text-[var(--color-text-secondary)]">
        Sign in to your account
      </p>

      <form onSubmit={handleSubmit} className="mt-8 flex flex-col gap-4">
        <input
          type="email"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="Email address"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />
        <input
          type="password"
          required
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          placeholder="Password"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none focus:border-[var(--color-primary)]"
        />

        {error && <p className="text-xs text-[var(--color-danger)]">{error}</p>}

        <Button type="submit" variant="primary" fullWidth size="lg" isLoading={isLoading}>
          Sign In
        </Button>
      </form>

      <p className="mt-6 text-center text-sm text-[var(--color-text-secondary)]">
        Don't have an account?{' '}
        <Link to="/register" className="font-medium text-[var(--color-primary)] hover:underline">
          Create one
        </Link>
      </p>
    </div>
  );
}
