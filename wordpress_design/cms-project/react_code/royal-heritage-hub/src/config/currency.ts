/**
 * MULTI-CURRENCY CONFIG
 * ---------------------------------------------------------------------------
 * All product prices in data files are stored in the BASE currency (see
 * SITE_CONFIG.currency in site.ts). Rates below convert FROM that base
 * currency TO each supported display currency. Update rates here — no
 * component or data file needs to change.
 */

export interface CurrencyDefinition {
  code: string;
  symbol: string;
  locale: string;
  /** Multiplier applied to a base-currency price to get this currency's price */
  rateFromBase: number;
  /** Decimal places to show (0 for JPY-style currencies, 2 for most, 0 for INR by convention here) */
  maximumFractionDigits: number;
}

export const SUPPORTED_CURRENCIES: CurrencyDefinition[] = [
  { code: 'INR', symbol: '₹', locale: 'en-IN', rateFromBase: 1, maximumFractionDigits: 0 },
  { code: 'USD', symbol: '$', locale: 'en-US', rateFromBase: 0.012, maximumFractionDigits: 2 },
  { code: 'EUR', symbol: '€', locale: 'de-DE', rateFromBase: 0.011, maximumFractionDigits: 2 },
  { code: 'GBP', symbol: '£', locale: 'en-GB', rateFromBase: 0.0095, maximumFractionDigits: 2 },
  { code: 'AED', symbol: 'د.إ', locale: 'ar-AE', rateFromBase: 0.044, maximumFractionDigits: 2 },
];

export const DEFAULT_CURRENCY_CODE = 'INR';

export function getCurrencyDefinition(code: string): CurrencyDefinition {
  return SUPPORTED_CURRENCIES.find((c) => c.code === code) ?? SUPPORTED_CURRENCIES[0];
}
