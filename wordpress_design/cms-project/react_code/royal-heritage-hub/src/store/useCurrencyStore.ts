import { create } from 'zustand';
import { STORAGE_KEYS } from '@/config/storage';
import { DEFAULT_CURRENCY_CODE, getCurrencyDefinition, SUPPORTED_CURRENCIES } from '@/config/currency';

interface CurrencyState {
  currencyCode: string;
  setCurrency: (code: string) => void;
}

function readInitialCurrency(): string {
  try {
    const stored = localStorage.getItem(STORAGE_KEYS.currency);
    if (stored && SUPPORTED_CURRENCIES.some((c) => c.code === stored)) return stored;
  } catch {
    /* ignore */
  }
  return DEFAULT_CURRENCY_CODE;
}

export const useCurrencyStore = create<CurrencyState>((set) => ({
  currencyCode: readInitialCurrency(),
  setCurrency: (code) => {
    localStorage.setItem(STORAGE_KEYS.currency, code);
    set({ currencyCode: code });
  },
}));

export function useActiveCurrency() {
  const code = useCurrencyStore((s) => s.currencyCode);
  return getCurrencyDefinition(code);
}
