/**
 * Payment API — Stripe / Razorpay Integration
 * Supports both gateways via config.
 */

import { apiClient } from './client';

export type PaymentGateway = 'stripe' | 'razorpay';

export interface PaymentConfig {
  gateway: PaymentGateway;
  key: string;               // publishable key
  currency: string;
  name: string;
  description: string;
  prefill?: { name?: string; email?: string; contact?: string };
  theme?: { color?: string };
}

export interface PaymentResult {
  success: boolean;
  paymentId?: string;
  orderId?: string;
  error?: string;
}

// ── Razorpay ──

declare global {
  interface Window {
    Razorpay: any;
  }
}

function loadRazorpayScript(): Promise<boolean> {
  return new Promise((resolve) => {
    if (document.querySelector('script[src="https://checkout.razorpay.com/v1/checkout.js"]')) {
      resolve(true);
      return;
    }
    const script = document.createElement('script');
    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
    script.onload = () => resolve(true);
    script.onerror = () => resolve(false);
    document.body.appendChild(script);
  });
}

async function payWithRazorpay(config: PaymentConfig, amount: number): Promise<PaymentResult> {
  const loaded = await loadRazorpayScript();
  if (!loaded) return { success: false, error: 'Failed to load Razorpay SDK' };

  return new Promise((resolve) => {
    const rzp = new window.Razorpay({
      key: config.key,
      amount: amount * 100, // paise
      currency: config.currency,
      name: config.name,
      description: config.description,
      prefill: config.prefill || {},
      theme: config.theme || { color: '#8B4513' },
      handler: (response: any) => {
        resolve({
          success: true,
          paymentId: response.razorpay_payment_id,
          orderId: response.razorpay_order_id,
        });
      },
      modal: {
        ondismiss: () => {
          resolve({ success: false, error: 'Payment cancelled by user' });
        },
      },
    });
    rzp.open();
  });
}

// ── Stripe ──

async function payWithStripe(config: PaymentConfig, amount: number): Promise<PaymentResult> {
  try {
    const response = await fetch(`${apiClient.useMock ? '/mock' : ''}/api/payment/create-intent`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount, currency: config.currency }),
    });
    // In real implementation, use Stripe.js to confirm payment
    // For mock, simulate success
    if (apiClient.useMock) {
      return { success: true, paymentId: `stripe_pay_${Date.now()}` };
    }
    return { success: false, error: 'Stripe integration requires Stripe.js' };
  } catch (err: any) {
    return { success: false, error: err.message };
  }
}

// ── Mock payment ──

function payWithMock(_amount: number): Promise<PaymentResult> {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve({ success: true, paymentId: `mock_pay_${Date.now()}` });
    }, 1500);
  });
}

// ── Main payment function ──

export const paymentApi = {
  // ── Get payment config from store config ──
  getConfig: (): PaymentConfig => {
    const key = import.meta.env.VITE_RAZORPAY_KEY || import.meta.env.VITE_STRIPE_KEY || 'rzp_test_demo';
    const gateway: PaymentGateway = import.meta.env.VITE_PAYMENT_GATEWAY || 'razorpay';
    return {
      gateway,
      key,
      currency: 'INR',
      name: import.meta.env.VITE_STORE_NAME || 'Royal Heritage Hub',
      description: 'Order payment',
    };
  },

  // ── Process payment ──
  pay: async (amount: number, method: string): Promise<PaymentResult> => {
    if (method === 'cod') {
      return { success: true, paymentId: 'cod' };
    }

    if (apiClient.useMock) {
      return payWithMock(amount);
    }

    const config = paymentApi.getConfig();

    if (config.gateway === 'razorpay') {
      return payWithRazorpay(config, amount);
    }

    if (config.gateway === 'stripe') {
      return payWithStripe(config, amount);
    }

    return { success: false, error: 'Unknown payment gateway' };
  },

  // ── Verify payment (for backend) ──
  verify: async (paymentId: string, orderId: string): Promise<boolean> => {
    if (apiClient.useMock) return true;
    const res = await apiClient.post<{ verified: boolean }>('/api/payment/verify', { paymentId, orderId });
    return res.verified;
  },
};
