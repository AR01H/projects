import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '@/store/useAuthStore';
import { useOrderStore } from '@/store/useOrderStore';
import { Button } from '@/components/common/Button';
import { SEO } from '@/components/common/SEO';
import { ROUTES } from '@/config/routes';
import { texts } from '@/config/texts';
import { formatCurrency } from '@/utils/formatCurrency';

export default function ProfilePage() {
  const { user, isAuthenticated, updateProfile, logout, isLoading } = useAuthStore();
  const { orders, loadOrders } = useOrderStore();
  const navigate = useNavigate();
  const [editing, setEditing] = useState(false);
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');

  useEffect(() => {
    if (!isAuthenticated) { navigate('/login'); return; }
    if (user) { setName(user.name); setPhone(user.phone || ''); }
    loadOrders();
  }, [isAuthenticated]);

  if (!user) return null;

  async function handleSave() {
    await updateProfile({ name, phone });
    setEditing(false);
  }

  async function handleLogout() {
    await logout();
    navigate(ROUTES.home);
  }

  const statusColors: Record<string, string> = {
    placed: 'text-[var(--color-text-muted)]',
    confirmed: 'text-[var(--color-primary)]',
    processing: 'text-[var(--color-primary)]',
    shipped: 'text-[var(--color-secondary-dark)]',
    out_for_delivery: 'text-[var(--color-secondary-dark)]',
    delivered: 'text-[var(--color-success)]',
    cancelled: 'text-[var(--color-danger)]',
  };

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
      <SEO title="My Account" description="Manage your account and view order history" />

      <h1 className="font-display text-3xl text-[var(--color-text-primary)]">My Account</h1>

      {/* Profile Card */}
      <div className="mt-8 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6">
        <div className="flex items-center justify-between">
          <h2 className="font-display text-lg text-[var(--color-text-primary)]">Profile</h2>
          {!editing && (
            <Button variant="outline" size="sm" onClick={() => setEditing(true)}>Edit</Button>
          )}
        </div>

        {editing ? (
          <div className="mt-4 flex flex-col gap-3">
            <input value={name} onChange={(e) => setName(e.target.value)} placeholder="Name"
              className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-2.5 text-sm outline-none focus:border-[var(--color-primary)]" />
            <input value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="Phone"
              className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-2.5 text-sm outline-none focus:border-[var(--color-primary)]" />
            <div className="flex gap-2">
              <Button variant="primary" size="sm" onClick={handleSave} isLoading={isLoading}>Save</Button>
              <Button variant="outline" size="sm" onClick={() => setEditing(false)}>Cancel</Button>
            </div>
          </div>
        ) : (
          <div className="mt-4 flex flex-col gap-2 text-sm">
            <p><span className="text-[var(--color-text-muted)]">Name:</span> {user.name}</p>
            <p><span className="text-[var(--color-text-muted)]">Email:</span> {user.email}</p>
            <p><span className="text-[var(--color-text-muted)]">Phone:</span> {user.phone || '—'}</p>
          </div>
        )}
      </div>

      {/* Order History */}
      <div className="mt-8">
        <h2 className="font-display text-lg text-[var(--color-text-primary)]">Order History</h2>

        {orders.length === 0 ? (
          <div className="mt-4 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-8 text-center">
            <p className="text-sm text-[var(--color-text-muted)]">No orders yet</p>
            <Link to={ROUTES.shop}>
              <Button variant="primary" size="sm" className="mt-4">{texts.common.shopNow}</Button>
            </Link>
          </div>
        ) : (
          <div className="mt-4 flex flex-col gap-4">
            {orders.map((order) => (
              <Link
                key={order.id}
                to={`/orders/${order.id}`}
                className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-5 transition-shadow hover:shadow-[var(--shadow-card)]"
              >
                <div className="flex items-center justify-between">
                  <div>
                    <p className="font-display text-sm font-semibold text-[var(--color-text-primary)]">{order.id}</p>
                    <p className="mt-0.5 text-xs text-[var(--color-text-muted)]">
                      {new Date(order.createdAt).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="font-semibold text-[var(--color-primary)]">{formatCurrency(order.total)}</p>
                    <p className={`mt-0.5 text-xs font-medium capitalize ${statusColors[order.status] || ''}`}>
                      {order.status.replace('_', ' ')}
                    </p>
                  </div>
                </div>
                <div className="mt-3 flex gap-2">
                  {order.items.slice(0, 3).map((item, i) => (
                    <img key={i} src={item.product.thumbnail} alt="" className="h-10 w-10 rounded object-cover" />
                  ))}
                  {order.items.length > 3 && (
                    <span className="flex h-10 w-10 items-center justify-center rounded bg-[var(--color-bg-cream)] text-xs text-[var(--color-text-muted)]">
                      +{order.items.length - 3}
                    </span>
                  )}
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* Logout */}
      <div className="mt-8">
        <Button variant="outline" onClick={handleLogout}>Sign Out</Button>
      </div>
    </div>
  );
}
