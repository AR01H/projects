import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useCartStore } from '@/store/useCartStore';
import { useFormatCurrency } from '@/utils/formatCurrency';
import { Button } from '@/components/common/Button';
import { EmptyState } from '@/components/common/EmptyState';
import { ROUTES } from '@/config/routes';
import { SHIPPING } from '@/config/constants';
import { SITE_CONFIG } from '@/config/site';

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

  const [payment, setPayment] = useState<PaymentMethod>('upi');
  const [placing, setPlacing] = useState(false);
  const [placed, setPlaced] = useState(false);

  const shipping = subtotal >= SHIPPING.freeShippingThreshold ? 0 : SHIPPING.defaultShippingCharge;
  const codCharge = payment === 'cod' ? SHIPPING.codCharge : 0;
  const total = subtotal + shipping + codCharge;

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

        <div className="h-fit rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6">
          <h2 className="font-display text-lg text-[var(--color-text-primary)]">Order Summary</h2>
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

          <div className="mt-5 flex flex-col gap-2 border-t border-[var(--color-border)] pt-4 text-sm">
            <div className="flex justify-between text-[var(--color-text-secondary)]">
              <span>Subtotal</span>
              <span>{formatCurrency(subtotal)}</span>
            </div>
            <div className="flex justify-between text-[var(--color-text-secondary)]">
              <span>Shipping</span>
              <span>{shipping === 0 ? 'Free' : formatCurrency(shipping)}</span>
            </div>
            {codCharge > 0 && (
              <div className="flex justify-between text-[var(--color-text-secondary)]">
                <span>COD Charge</span>
                <span>{formatCurrency(codCharge)}</span>
              </div>
            )}
            <div className="flex justify-between border-t border-[var(--color-border)] pt-3 font-display text-base text-[var(--color-text-primary)]">
              <span>Total</span>
              <span>{formatCurrency(total)}</span>
            </div>
          </div>

          <Button type="submit" variant="primary" fullWidth size="lg" isLoading={placing} className="mt-6">
            Place Order
          </Button>
        </div>
      </form>
    </div>
  );
}
