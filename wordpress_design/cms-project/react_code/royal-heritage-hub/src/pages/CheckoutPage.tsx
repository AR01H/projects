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
import { SEO } from '@/components/common/SEO';

type PaymentMethod = 'upi' | 'card' | 'netbanking' | 'cod';

function getPaymentMethods(formatCurrency: (n: number) => string): { value: PaymentMethod; label: string }[] {
  return [
    { value: 'upi', label: 'UPI (GPay / PhonePe / Paytm)' },
    { value: 'card', label: 'Credit / Debit Card' },
    { value: 'netbanking', label: 'Net Banking' },
    { value: 'cod', label: `Cash on Delivery (+${formatCurrency(SHIPPING.codCharge)})` },
  ];
}

export default function CheckoutPage() {
  const items = useCartStore((s) => s.items);
  const subtotal = useCartStore((s) => s.subtotal());
  const formatCurrency = useFormatCurrency();
  const PAYMENT_METHODS = getPaymentMethods(formatCurrency);

  // Coupon state from store
  const appliedCoupon = useCouponStore((s) => s.appliedCoupon);
  const couponDiscount = useCouponStore((s) => s.discountAmount);
  const couponFreeShipping = useCouponStore((s) => s.freeShipping);
  const couponFreeGift = useCouponStore((s) => s.freeGift);
  const couponTierLabel = useCouponStore((s) => s.tierLabel);
  const couponError = useCouponStore((s) => s.error);
  const couponLoading = useCouponStore((s) => s.loading);
  const validateCoupon = useCouponStore((s) => s.validate);
  const removeCoupon = useCouponStore((s) => s.remove);

  const [couponInput, setCouponInput] = useState('');
  const [payment, setPayment] = useState<PaymentMethod>('upi');
  const [placing, setPlacing] = useState(false);
  const [placed, setPlaced] = useState(false);

  const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);
  const shipping = couponFreeShipping ? 0 : (subtotal >= SHIPPING.freeShippingThreshold ? 0 : SHIPPING.defaultShippingCharge);
  const codCharge = payment === 'cod' ? SHIPPING.codCharge : 0;
  const total = Math.max(0, subtotal - couponDiscount + shipping + codCharge);

  function handleApplyCoupon() {
    validateCoupon(couponInput, subtotal, itemCount);
    setCouponInput('');
  }

  function handleRemoveCoupon() {
    removeCoupon();
  }

  function handleCouponKeyDown(e: React.KeyboardEvent) {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleApplyCoupon();
    }
  }

  function placeOrder(e: React.FormEvent) {
    e.preventDefault();
    setPlacing(true);
    setTimeout(() => {
      setPlacing(false);
      setPlaced(true);
    }, 1200);
  }

  if (items.length === 0 && !placed) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-24">
        <SEO title="Checkout" description="Complete your purchase securely at Royal Heritage Hub." />
        <EmptyState
          title="Your bag is empty"
          description={SITE_CONFIG.microcopy.emptyCheckoutDescription}
          action={
            <Link to={ROUTES.shop}>
              <Button variant="primary">Continue Shopping</Button>
            </Link>
          }
        />
      </div>
    );
  }

  if (placed) {
    return (
      <div className="mx-auto max-w-xl px-4 py-24 text-center">
        <SEO title="Checkout" description="Complete your purchase securely at Royal Heritage Hub." />
        <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-success)]/15">
          <svg viewBox="0 0 24 24" className="h-8 w-8" fill="none" stroke="var(--color-success)" strokeWidth="2">
            <path d="M20 6L9 17l-5-5" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </div>
        <h1 className="font-display text-3xl text-[var(--color-text-primary)]">Order Placed!</h1>
        <p className="mt-3 text-sm text-[var(--color-text-secondary)]">
          {SITE_CONFIG.microcopy.orderConfirmationMessage} A confirmation has been sent to your email,
          and your order will arrive in {SHIPPING.estimatedDeliveryMin}–{SHIPPING.estimatedDeliveryMax} business days.
        </p>
        <Link to={ROUTES.shop}>
          <Button variant="primary" className="mt-8">
            Continue Shopping
          </Button>
        </Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SEO title="Checkout" description="Complete your purchase securely at Royal Heritage Hub." />
      <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">Checkout</h1>

      <form onSubmit={placeOrder} className="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-[1fr_380px]">
        <div className="flex flex-col gap-8">
          <section>
            <h2 className="mb-4 font-display text-lg text-[var(--color-text-primary)]">Shipping Address</h2>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <input required placeholder="Full Name" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
              <input required type="tel" placeholder="Phone Number" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
              <input required placeholder="Address Line 1" className="sm:col-span-2 rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
              <input placeholder="Address Line 2 (Optional)" className="sm:col-span-2 rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
              <input required placeholder="City" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
              <input required placeholder="State" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
              <input required placeholder="PIN Code" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
              <input required type="email" placeholder="Email" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none" />
            </div>
          </section>

          <section>
            <h2 className="mb-4 font-display text-lg text-[var(--color-text-primary)]">Payment Method</h2>
            <div className="flex flex-col gap-3">
              {PAYMENT_METHODS.map((m) => (
                <label
                  key={m.value}
                  className={`flex cursor-pointer items-center gap-3 rounded-[var(--radius-btn)] border px-4 py-3 text-sm transition-colors ${
                    payment === m.value
                      ? 'border-[var(--color-primary)] bg-[var(--color-bg-cream)]'
                      : 'border-[var(--color-border)]'
                  }`}
                >
                  <input
                    type="radio"
                    name="payment"
                    checked={payment === m.value}
                    onChange={() => setPayment(m.value)}
                    className="accent-[var(--color-primary)]"
                  />
                  {m.label}
                </label>
              ))}
            </div>
          </section>

          <section>
            <h2 className="mb-4 font-display text-lg text-[var(--color-text-primary)]">Order Notes (Optional)</h2>
            <textarea
              rows={3}
              placeholder="Gift message, delivery instructions..."
              className="w-full rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-3 text-sm outline-none"
            />
          </section>
        </div>

        {/* Order Summary Sidebar */}
        <div className="h-fit rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6">
          <h2 className="font-display text-lg text-[var(--color-text-primary)]">Order Summary</h2>

          {/* Items */}
          <div className="mt-4 flex flex-col gap-3 divide-y divide-[var(--color-border)]">
            {items.map((item) => (
              <div key={item.id} className="flex justify-between gap-3 pt-3 first:pt-0 text-sm">
                <span className="text-[var(--color-text-secondary)]">
                  {item.product.name} × {item.quantity}
                </span>
                <span className="whitespace-nowrap font-medium text-[var(--color-text-primary)]">
                  {formatCurrency(item.product.price * item.quantity)}
                </span>
              </div>
            ))}
          </div>

          {/* Coupon Input */}
          <div className="mt-4">
            {appliedCoupon ? (
              <div className="rounded-lg border border-[var(--color-success)]/30 bg-[var(--color-success)]/5 p-3">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-semibold text-[var(--color-success)]">
                      {appliedCoupon.badge && <span className="mr-1 inline-block rounded px-1.5 py-0.5 text-[0.6rem] font-bold" style={{ backgroundColor: appliedCoupon.bgColor, color: appliedCoupon.textColor }}>{appliedCoupon.badge}</span>}
                      {appliedCoupon.code}
                    </p>
                    <p className="mt-0.5 text-xs text-[var(--color-text-secondary)]">{appliedCoupon.description}</p>
                    {couponTierLabel && <p className="mt-0.5 text-xs text-[var(--color-success)]">{couponTierLabel}</p>}
                  </div>
                  <button type="button" onClick={handleRemoveCoupon} className="text-xs text-[var(--color-danger)] hover:underline">Remove</button>
                </div>
              </div>
            ) : (
              <div className="flex gap-2">
                <input
                  value={couponInput}
                  onChange={(e) => setCouponInput(e.target.value.toUpperCase())}
                  onKeyDown={handleCouponKeyDown}
                  placeholder="Enter coupon code"
                  className="w-full rounded-[var(--radius-btn)] border border-[var(--color-border)] bg-[var(--color-bg-cream)] px-3 py-2 text-sm outline-none uppercase"
                />
                <Button variant="outline" size="sm" onClick={handleApplyCoupon} isLoading={couponLoading}>
                  Apply
                </Button>
              </div>
            )}
            {couponError && <p className="mt-1 text-xs text-[var(--color-danger)]">{couponError}</p>}
          </div>

          {/* Price Breakdown */}
          <div className="mt-5 flex flex-col gap-2 border-t border-[var(--color-border)] pt-4 text-sm">
            <div className="flex justify-between text-[var(--color-text-secondary)]">
              <span>Subtotal ({itemCount} items)</span>
              <span>{formatCurrency(subtotal)}</span>
            </div>

            {/* Discount */}
            {couponDiscount > 0 && (
              <div className="flex justify-between text-[var(--color-success)]">
                <span>Discount</span>
                <span>−{formatCurrency(couponDiscount)}</span>
              </div>
            )}

            {/* Free Shipping */}
            {couponFreeShipping && shipping === 0 && (
              <div className="flex justify-between text-[var(--color-success)]">
                <span>Shipping</span>
                <span className="font-semibold">FREE (coupon)</span>
              </div>
            )}
            {!couponFreeShipping && (
              <div className="flex justify-between text-[var(--color-text-secondary)]">
                <span>Shipping</span>
                <span>{shipping === 0 ? 'Free' : formatCurrency(shipping)}</span>
              </div>
            )}

            {/* COD Charge */}
            {codCharge > 0 && (
              <div className="flex justify-between text-[var(--color-text-secondary)]">
                <span>COD Charge</span>
                <span>{formatCurrency(codCharge)}</span>
              </div>
            )}

            {/* Free Gift */}
            {couponFreeGift && (
              <div className="flex items-center gap-2 rounded-lg bg-[var(--color-success)]/10 px-3 py-2 text-xs text-[var(--color-success)]">
                <span>🎁</span>
                <span>Free gift: {couponFreeGift.productName} (×{couponFreeGift.quantity})</span>
              </div>
            )}

            {/* Total */}
            <div className="flex justify-between border-t border-[var(--color-border)] pt-3 font-display text-base text-[var(--color-text-primary)]">
              <span>Total</span>
              <span>{formatCurrency(total)}</span>
            </div>

            {/* Savings callout */}
            {couponDiscount > 0 && (
              <p className="text-center text-xs font-medium text-[var(--color-success)]">
                You're saving {formatCurrency(couponDiscount)} on this order!
              </p>
            )}
          </div>

          <Button type="submit" variant="primary" fullWidth size="lg" isLoading={placing} className="mt-6">
            Place Order · {formatCurrency(total)}
          </Button>

          {/* Trust signals */}
          <div className="mt-4 flex justify-center gap-4 text-[0.65rem] text-[var(--color-text-muted)]">
            <span>🔒 Secure</span>
            <span>📦 Easy Returns</span>
            <span>✅ Genuine Products</span>
          </div>
        </div>
      </form>
    </div>
  );
}
