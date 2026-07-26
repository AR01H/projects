import { productsApi } from './products';
import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_TAGS } from '@/data/mockData';
import type { Product } from '@/types/product';

export interface TagMeta {
  tag: string;
  label: string;
  parentTag: string | null;
}

export interface TagSummary {
  tag: string;
  label: string;
  count: number;
  parentTag: string | null;
}

interface ApiResponse<T> { data: T; }

async function getTagMeta(): Promise<TagMeta[]> {
  if (apiClient.useMock) return MOCK_TAGS;
  try {
    const res = await apiClient.get<ApiResponse<TagMeta[]>>(ENDPOINTS.tags.list);
    const items = res.data ?? res as unknown as TagMeta[];
    return items.map((t: any) => ({ tag: t.tag || t.slug, label: t.label || t.name, parentTag: t.parentTag || null }));
  } catch { return []; }
}

function fallbackLabel(tag: string): string {
  return tag.split('-').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

export const tagsApi = {
  getAll: async (): Promise<TagSummary[]> => {
    const products = await productsApi.getAll();
    const meta = await getTagMeta();
    const metaByTag = new Map(meta.map((m) => [m.tag, m]));

    const counts = new Map<string, number>();
    products.forEach((p) => {
      p.tags.forEach((t) => counts.set(t, (counts.get(t) ?? 0) + 1));
    });

    return Array.from(counts.entries())
      .map(([tag, count]) => {
        const m = metaByTag.get(tag);
        return { tag, label: m?.label ?? fallbackLabel(tag), count, parentTag: m?.parentTag ?? null };
      })
      .sort((a, b) => b.count - a.count);
  },

  getByTag: async (tag: string): Promise<Product[]> => {
    const products = await productsApi.getAll();
    return products.filter((p) => p.tags.includes(tag));
  },

  getPopular: async (limit = 12): Promise<TagSummary[]> => (await tagsApi.getAll()).slice(0, limit),

  getTopLevel: async (): Promise<TagSummary[]> => (await tagsApi.getAll()).filter((t) => !t.parentTag),

  getChildren: async (parentTag: string): Promise<TagSummary[]> =>
    (await tagsApi.getAll()).filter((t) => t.parentTag === parentTag),

  getParent: async (tag: string): Promise<TagSummary | undefined> => {
    const all = await tagsApi.getAll();
    const current = all.find((t) => t.tag === tag);
    if (!current?.parentTag) return undefined;
    return all.find((t) => t.tag === current.parentTag);
  },

  getLabel: async (tag: string): Promise<string> => {
    const all = await tagsApi.getAll();
    return all.find((t) => t.tag === tag)?.label ?? fallbackLabel(tag);
  },
};
