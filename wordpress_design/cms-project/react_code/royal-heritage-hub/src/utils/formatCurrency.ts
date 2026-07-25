import { CURRENCY } from '@/config/constants';
import { getCurrencyDefinition, DEFAULT_CURRENCY_CODE, type CurrencyDefinition } from '@/config/currency';
import { useCurrencyStore } from '@/store/useCurrencyStore';

/**
 * Base-currency formatter — used where no currency context is available
 * (e.g. outside React, in plain utility code). Prefer useFormatCurrency()
 * inside components so prices react to the user's chosen display currency.
 */
export function formatCurrency(amount: number): string {
  return new Intl.NumberFormat(CURRENCY.locale, {
    style: 'currency',
    currency: CURRENCY.code,
    maximumFractionDigits: 0,
  }).format(amount);
}

/** Converts a base-currency amount into the given currency and formats it. */
export function formatCurrencyAs(amount: number, currency: CurrencyDefinition): string {
  const converted = amount * currency.rateFromBase;
  return new Intl.NumberFormat(currency.locale, {
    style: 'currency',
    currency: currency.code,
    maximumFractionDigits: currency.maximumFractionDigits,
  }).format(converted);
}

/**
 * Hook: returns a formatter bound to the user's currently selected display
 * currency (persisted via useCurrencyStore). All product prices in data
 * files are stored in the base currency (INR); this converts + formats.
 */
export function useFormatCurrency() {
  const currencyCode = useCurrencyStore((s) => s.currencyCode);
  const currency = getCurrencyDefinition(currencyCode || DEFAULT_CURRENCY_CODE);
  return (amount: number) => formatCurrencyAs(amount, currency);
}

export function formatDiscount(price: number, compareAt?: number): number | null {
  if (!compareAt || compareAt <= price) return null;
  return Math.round(((compareAt - price) / compareAt) * 100);
}
