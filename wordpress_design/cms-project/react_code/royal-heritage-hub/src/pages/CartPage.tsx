import { Link } from 'react-router-dom';
import { useState } from 'react';
import { useCartStore } from '@/store/useCartStore';
import { useCouponStore } from '@/store/useCouponStore';
import { useFormatCurrency } from '@/utils/formatCurrency';
import { Button } from '@/components/common/Button';
import { EmptyState } from '@/components/common/EmptyState';
import { PageHero } from '@/components/common/PageHero';
import { SITE_CONFIG } from '@/config/site';
import { ROUTES } from '@/config/routes';
import { SHIPPING } from '@/config/constants';
import { SEO } from '@/components/common/SEO';

export default function CartPage() {
  const items = useCartStore((s) => s.items);
  const updateQuantity = useCartStore((s) => s.updateQuantity);
  const removeItem = useCartStore((s) => s.removeItem);
  const subtotal = useCartStore((s) => s.subtotal());
  const formatCurrency = useFormatCurrency();

  const appliedCoupon = useCouponStore((s) => s.appliedCoupon);
  const couponError = useCouponStore((s) => s.error);
  const applyCoupon = useCouponStore((s) => s.apply);
  const [couponInput, setCouponInput] = useState('');

  function handleApplyCoupon() {
    applyCoupon(couponInput);
    setCouponInput('');
  }

  const discountAmount = appliedCoupon ? subtotal * appliedCoupon.discount : 0;
  const shipping = subtotal >= SHIPPING.freeShippingThreshold || subtotal === 0 ? 0 : SHIPPING.defaultShippingCharge;
  const total = subtotal - discountAmount + shipping;

  if (items.length === 0) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-24">
        <SEO title="Shopping Bag" description="Review the items in your Royal Heritage Hub shopping bag." />
        <EmptyState
          title="Your bag is empty"
          description={SITE_CONFIG.microcopy.emptyCartDescription}
          action={
            <Link to={ROUTES.shop}>
              <Button variant="primary">Continue Shopping</Button>
            </Link>
          }
        />
      </div>
    );
  }

  return (
    <div>
      <SEO title="Shopping Bag" description="Review the items in your Royal Heritage Hub shopping bag." />
      <PageHero pageKey="cart" fallbackTitle="Your Bag" />
      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">Shopping Bag</h1>

      <div className="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-[1fr_360px]">
        <div className="flex flex-col divide-y divide-[var(--color-border)]">
          {items.map((item) => (
            <div key={item.id} className="flex gap-4 py-5">
              <img
                src={item.product.thumbnail}
                alt={item.product.name}
                className="h-28 w-24 flex-shrink-0 rounded-[var(--radius-card)] object-cover"
              />
              <div className="flex flex-1 flex-col justify-between">
                <div className="flex justify-between gap-3">
                  <div>
                    <Link to={`/product/${item.product.slug}`} className="font-display text-base text-[var(--color-text-primary)]">
                      {item.product.name}
                    </Link>
                    <p className="mt-1 text-xs text-[var(--color-text-muted)]">{item.product.specs[0]?.value ?? ''}</p>
                  </div>
                  <p className="whitespace-nowrap font-semibold text-[var(--color-primary)]">
                    {formatCurrency(item.product.price * item.quantity)}
                  </p>
                </div>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3 rounded-full border border-[var(--color-border)] px-3 py-1.5">
                    <button onClick={() => updateQuantity(item.id, item.quantity - 1)} className="text-[var(--color-text-secondary)]">−</button>
                    <span className="text-sm">{item.quantity}</span>
                    <button onClick={() => updateQuantity(item.id, item.quantity + 1)} className="text-[var(--color-text-secondary)]">+</button>
                  </div>
                  <button
                    onClick={() => removeItem(item.id)}
                    className="text-xs text-[var(--color-text-muted)] underline hover:text-[var(--color-danger)]"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        <div className="h-fit rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6">
          <h2 className="font-display text-lg text-[var(--color-text-primary)]">Order Summary</h2>

          <div className="mt-5 flex gap-2">
            <input
              value={couponInput}
              onChange={(e) => setCouponInput(e.target.value)}
              placeholder="Coupon code"
              className="w-full rounded-[var(--radius-btn)] border border-[var(--color-border)] bg-[var(--color-bg-cream)] px-3 py-2.5 text-sm outline-none"
            />
            <Button variant="outline" size="sm" onClick={handleApplyCoupon}>
              Apply
            </Button>
          </div>
          {couponError && <p className="mt-1.5 text-xs text-[var(--color-danger)]">{couponError}</p>}
          {appliedCoupon && (
            <p className="mt-1.5 text-xs text-[var(--color-success)]">
              "{appliedCoupon.code}" applied — {appliedCoupon.discount * 100}% off
            </p>
          )}

          <div className="mt-6 flex flex-col gap-3 border-t border-[var(--color-border)] pt-5 text-sm">
            <div className="flex justify-between text-[var(--color-text-secondary)]">
              <span>Subtotal</span>
              <span>{formatCurrency(subtotal)}</span>
            </div>
            {appliedCoupon && (
              <div className="flex justify-between text-[var(--color-success)]">
                <span>Discount</span>
                <span>−{formatCurrency(discountAmount)}</span>
              </div>
            )}
            <div className="flex justify-between text-[var(--color-text-secondary)]">
              <span>Shipping</span>
              <span>{shipping === 0 ? 'Free' : formatCurrency(shipping)}</span>
            </div>
            <div className="flex justify-between border-t border-[var(--color-border)] pt-3 font-display text-base text-[var(--color-text-primary)]">
              <span>Total</span>
              <span>{formatCurrency(total)}</span>
            </div>
          </div>

          <Link to={ROUTES.checkout}>
            <Button variant="primary" fullWidth size="lg" className="mt-6">
              Proceed to Checkout
            </Button>
          </Link>
        </div>
      </div>
      </div>
    </div>
  );
}
