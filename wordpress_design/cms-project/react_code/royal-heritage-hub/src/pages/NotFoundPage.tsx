import { Link } from 'react-router-dom';
import { Button } from '@/components/common/Button';
import { ROUTES } from '@/config/routes';

export default function NotFoundPage() {
  return (
    <div className="mx-auto flex max-w-lg flex-col items-center px-4 py-32 text-center">
      <p className="font-display text-6xl text-[var(--color-secondary)]">404</p>
      <h1 className="mt-4 font-display text-2xl text-[var(--color-text-primary)]">Page Not Found</h1>
      <p className="mt-2 text-sm text-[var(--color-text-secondary)]">
        The page you're looking for may have been moved or doesn't exist.
      </p>
      <Link to={ROUTES.home}>
        <Button variant="primary" className="mt-8">
          Back to Home
        </Button>
      </Link>
    </div>
  );
}
