import { create } from 'zustand';
import { STORAGE_KEYS } from '@/config/storage';

const MAX_ITEMS = 10;

function read(): string[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEYS.recentlyViewed);
    return raw ? (JSON.parse(raw) as string[]) : [];
  } catch {
    return [];
  }
}

function write(ids: string[]) {
  localStorage.setItem(STORAGE_KEYS.recentlyViewed, JSON.stringify(ids));
}

interface RecentlyViewedState {
  ids: string[];
  init: () => void;
  track: (productId: string) => void;
}

export const useRecentlyViewedStore = create<RecentlyViewedState>((set, get) => ({
  ids: [],
  init: () => set({ ids: read() }),
  track: (productId) => {
    const current = get().ids.filter((id) => id !== productId);
    const updated = [productId, ...current].slice(0, MAX_ITEMS);
    write(updated);
    set({ ids: updated });
  },
}));
