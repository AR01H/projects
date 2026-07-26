import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useCartStore } from '@/store/useCartStore';
import { useCouponStore } from '@/store/useCouponStore';
import { useFormatCurrency } from '@/utils/formatCurrency';
import { Button } from '@/components/common/Button';
import { EmptyState } from '@/components/common/EmptyState';
import { ROUTES } from '@/config/routes';
import { SHIPPING } from '@/config/constants';
import { SITE_CONFIG } from '@/config/site';

export function CartDrawer() {
  const isOpen = useCartStore((s) => s.isOpen);
  const toggleCart = useCartStore((s) => s.toggleCart);
  const items = useCartStore((s) => s.items);
  const updateQuantity = useCartStore((s) => s.updateQuantity);
  const removeItem = useCartStore((s) => s.removeItem);
  const subtotal = useCartStore((s) => s.subtotal());
  const formatCurrency = useFormatCurrency();

  const appliedCoupon = useCouponStore((s) => s.appliedCoupon);
  const couponError = useCouponStore((s) => s.error);
  const applyCoupon = useCouponStore((s) => s.validate);
  const [couponInput, setCouponInput] = useState('');

  if (!isOpen) return null;

  const discountAmount = appliedCoupon ? subtotal * appliedCoupon.discount : 0;
  const remainingForFreeShipping = Math.max(0, SHIPPING.freeShippingThreshold - subtotal);

  return (
    <div className="fixed inset-0 z-50">
      <div
        className="absolute inset-0 bg-[var(--color-dark)]/50 backdrop-blur-sm"
        onClick={() => toggleCart(false)}
      />
      <div className="animate-slide-in-right absolute right-0 top-0 flex h-full w-full max-w-md flex-col bg-[var(--color-bg-light)] shadow-2xl">
        <div className="flex items-center justify-between border-b border-[var(--color-border)] px-6 py-5">
          <h2 className="font-display text-xl text-[var(--color-text-primary)]">
            Your Bag ({items.length})
          </h2>
          <button onClick={() => toggleCart(false)} aria-label="Close cart">
            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-dark)" strokeWidth="1.8">
              <path d="M6 6l12 12M18 6L6 18" strokeLinecap="round" />
            </svg>
          </button>
        </div>

        {remainingForFreeShipping > 0 && items.length > 0 && (
          <div className="bg-[var(--color-bg-cream)] px-6 py-3 text-xs text-[var(--color-text-secondary)]">
            Add <strong className="text-[var(--color-primary)]">{formatCurrency(remainingForFreeShipping)}</strong> more for free shipping
          </div>
        )}

        <div className="flex-1 overflow-y-auto px-6 py-4">
          {items.length === 0 ? (
            <EmptyState
              title="Your bag is empty"
              description={SITE_CONFIG.microcopy.emptyCartDescription}
              action={
                <Button variant="primary" onClick={() => toggleCart(false)}>
                  <Link to={ROUTES.shop}>Continue Shopping</Link>
                </Button>
              }
            />
          ) : (
            <div className="flex flex-col gap-5">
              {items.map((item) => (
                <div key={item.id} className="flex gap-4">
                  <img
                    src={item.product.thumbnail}
                    alt={item.product.name}
                    className="h-24 w-20 flex-shrink-0 rounded-[var(--radius-card)] object-cover"
                  />
                  <div className="flex flex-1 flex-col justify-between">
                    <div>
                      <p className="font-display text-sm leading-snug text-[var(--color-text-primary)]">
                        {item.product.name}
                      </p>
                      <p className="mt-1 text-sm font-semibold text-[var(--color-primary)]">
                        {formatCurrency(item.product.price)}
                      </p>
                    </div>
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3 rounded-full border border-[var(--color-border)] px-2 py-1">
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity - 1)}
                          className="h-5 w-5 text-sm text-[var(--color-text-secondary)]"
                        >
                          −
                        </button>
                        <span className="text-sm">{item.quantity}</span>
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity + 1)}
                          className="h-5 w-5 text-sm text-[var(--color-text-secondary)]"
                        >
                          +
                        </button>
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
          )}
        </div>

        {items.length > 0 && (
          <div className="border-t border-[var(--color-border)] px-6 py-5">
            {/* Coupon input */}
            <div className="mb-4 flex gap-2">
              <input
                value={couponInput}
                onChange={(e) => setCouponInput(e.target.value)}
                placeholder="Coupon code"
                className="w-full rounded-[var(--radius-btn)] border border-[var(--color-border)] bg-[var(--color-bg-cream)] px-3 py-2 text-xs outline-none"
              />
              <Button variant="outline" size="sm" onClick={() => { applyCoupon(couponInput, subtotal, items.length); setCouponInput(''); }}>
                Apply
              </Button>
            </div>
            {couponError && <p className="mb-2 text-xs text-[var(--color-danger)]">{couponError}</p>}
            {appliedCoupon && (
              <p className="mb-2 text-xs text-[var(--color-success)]">
                "{appliedCoupon.code}" — {appliedCoupon.discount * 100}% off applied
              </p>
            )}

            <div className="flex items-center justify-between">
              <span className="text-sm text-[var(--color-text-secondary)]">Subtotal</span>
              <span className="font-display text-lg text-[var(--color-text-primary)]">
                {formatCurrency(subtotal)}
              </span>
            </div>
            {appliedCoupon && (
              <div className="flex items-center justify-between">
                <span className="text-sm text-[var(--color-success)]">Discount</span>
                <span className="text-sm text-[var(--color-success)]">−{formatCurrency(discountAmount)}</span>
              </div>
            )}
            <Link to={ROUTES.checkout} onClick={() => toggleCart(false)}>
              <Button variant="primary" fullWidth size="lg" className="mt-4">
                Proceed to Checkout
              </Button>
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
