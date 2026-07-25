import { useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useOrderStore } from '@/store/useOrderStore';
import { Button } from '@/components/common/Button';
import { SEO } from '@/components/common/SEO';
import { ROUTES } from '@/config/routes';
import { texts } from '@/config/texts';
import { formatCurrency } from '@/utils/formatCurrency';

export default function OrderDetailPage() {
  const { orderId } = useParams();
  const { currentOrder: order, getOrder, isLoading, cancelOrder } = useOrderStore();

  useEffect(() => {
    if (orderId) getOrder(orderId);
  }, [orderId]);

  if (isLoading) return <div className="py-24 text-center"><div className="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-[var(--color-border)] border-t-[var(--color-primary)]" /></div>;
  if (!order) return <div className="py-24 text-center text-sm text-[var(--color-text-muted)]">Order not found</div>;

  const statusColors: Record<string, string> = {
    placed: 'bg-[var(--color-text-muted)]/10 text-[var(--color-text-muted)]',
    confirmed: 'bg-[var(--color-primary)]/10 text-[var(--color-primary)]',
    processing: 'bg-[var(--color-primary)]/10 text-[var(--color-primary)]',
    shipped: 'bg-[var(--color-secondary)]/10 text-[var(--color-secondary-dark)]',
    out_for_delivery: 'bg-[var(--color-secondary)]/10 text-[var(--color-secondary-dark)]',
    delivered: 'bg-[var(--color-success)]/10 text-[var(--color-success)]',
    cancelled: 'bg-[var(--color-danger)]/10 text-[var(--color-danger)]',
  };

  return (
    <div className="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
      <SEO title={`Order ${order.id}`} description={`Order details for ${order.id}`} />

      <div className="flex items-center justify-between">
        <div>
          <h1 className="font-display text-2xl text-[var(--color-text-primary)]">Order {order.id}</h1>
          <p className="mt-1 text-xs text-[var(--color-text-muted)]">
            Placed on {new Date(order.createdAt).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })}
          </p>
        </div>
        <span className={`rounded-full px-3 py-1 text-xs font-semibold capitalize ${statusColors[order.status] || ''}`}>
          {order.status.replace('_', ' ')}
        </span>
      </div>

      {/* Tracking Steps */}
      <div className="mt-8 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6">
        <h2 className="mb-4 font-display text-sm font-semibold text-[var(--color-text-primary)]">Order Tracking</h2>
        <div className="flex flex-col gap-0">
          {order.tracking.map((step, i) => (
            <div key={step.status} className="flex gap-4">
              <div className="flex flex-col items-center">
                <div className={cn(
                  'flex h-6 w-6 items-center justify-center rounded-full border-2',
                  step.completed ? 'border-[var(--color-primary)] bg-[var(--color-primary)]' : 'border-[var(--color-border)]'
                )}>
                  {step.completed && (
                    <svg viewBox="0 0 12 12" className="h-3 w-3" fill="none" stroke="white" strokeWidth="2"><path d="M10 3L4.5 8.5 2 6" strokeLinecap="round" strokeLinejoin="round" /></svg>
                  )}
                </div>
                {i < order.tracking.length - 1 && (
                  <div className={cn('w-0.5 flex-1 min-h-[24px]', step.completed ? 'bg-[var(--color-primary)]' : 'bg-[var(--color-border)]')} />
                )}
              </div>
              <div className="pb-6">
                <p className={`text-sm font-medium ${step.completed ? 'text-[var(--color-text-primary)]' : 'text-[var(--color-text-muted)]'}`}>
                  {step.label}
                </p>
                {step.date && (
                  <p className="mt-0.5 text-xs text-[var(--color-text-muted)]">
                    {new Date(step.date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}
                  </p>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Order Items */}
      <div className="mt-6 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6">
        <h2 className="mb-4 font-display text-sm font-semibold text-[var(--color-text-primary)]">Items</h2>
        <div className="flex flex-col gap-3">
          {order.items.map((item, i) => (
            <div key={i} className="flex items-center gap-4">
              <img src={item.product.thumbnail} alt={item.product.name} className="h-14 w-14 rounded object-cover" />
              <div className="flex-1">
                <p className="text-sm font-medium text-[var(--color-text-primary)]">{item.product.name}</p>
                <p className="text-xs text-[var(--color-text-muted)]">Qty: {item.quantity}</p>
              </div>
              <p className="text-sm font-semibold text-[var(--color-primary)]">{formatCurrency(item.product.price * item.quantity)}</p>
            </div>
          ))}
        </div>

        <div className="mt-4 flex flex-col gap-2 border-t border-[var(--color-border)] pt-4 text-sm">
          <div className="flex justify-between text-[var(--color-text-secondary)]"><span>Subtotal</span><span>{formatCurrency(order.subtotal)}</span></div>
          {order.discount > 0 && <div className="flex justify-between text-[var(--color-success)]"><span>Discount</span><span>−{formatCurrency(order.discount)}</span></div>}
          <div className="flex justify-between text-[var(--color-text-secondary)]"><span>Shipping</span><span>{order.shipping === 0 ? texts.common.free : formatCurrency(order.shipping)}</span></div>
          {order.codCharge > 0 && <div className="flex justify-between text-[var(--color-text-secondary)]"><span>COD Charge</span><span>{formatCurrency(order.codCharge)}</span></div>}
          <div className="flex justify-between border-t border-[var(--color-border)] pt-3 font-semibold text-[var(--color-text-primary)]"><span>Total</span><span>{formatCurrency(order.total)}</span></div>
        </div>
      </div>

      {/* Address */}
      <div className="mt-6 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6">
        <h2 className="mb-3 font-display text-sm font-semibold text-[var(--color-text-primary)]">Shipping Address</h2>
        <p className="text-sm text-[var(--color-text-secondary)]">
          {order.address.name}<br />
          {order.address.line1}<br />
          {order.address.line2 && <>{order.address.line2}<br /></>}
          {order.address.city}, {order.address.state} - {order.address.pincode}<br />
          Phone: {order.address.phone}
        </p>
      </div>

      {/* Actions */}
      <div className="mt-8 flex gap-3">
        <Link to={ROUTES.shop}><Button variant="primary">{texts.common.continueShopping}</Button></Link>
        {order.status === 'placed' && (
          <Button variant="outline" onClick={() => cancelOrder(order.id)}>Cancel Order</Button>
        )}
      </div>
    </div>
  );
}

function cn(...classes: (string | boolean | undefined)[]) {
  return classes.filter(Boolean).join(' ');
}
