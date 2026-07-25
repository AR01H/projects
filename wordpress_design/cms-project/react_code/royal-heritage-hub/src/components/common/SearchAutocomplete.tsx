import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { productsApi } from '@/api/products';
import { buildRoute, ROUTES } from '@/config/routes';
import { useFormatCurrency } from '@/utils/formatCurrency';
import { Rating } from './Rating';
import type { Product } from '@/types/product';

interface SearchAutocompleteProps {
  query: string;
  onSelect: () => void;
}

export function SearchAutocomplete({ query, onSelect }: SearchAutocompleteProps) {
  const [suggestions, setSuggestions] = useState<Product[]>([]);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const formatCurrency = useFormatCurrency();
  const debounceRef = useRef<ReturnType<typeof setTimeout>>();

  useEffect(() => {
    if (query.trim().length < 2) {
      setSuggestions([]);
      return;
    }

    clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(async () => {
      setLoading(true);
      try {
        const results = await productsApi.getFiltered({
          search: query.trim(),
          sortBy: 'best-selling',
        });
        setSuggestions(results.slice(0, 5));
      } catch {
        setSuggestions([]);
      } finally {
        setLoading(false);
      }
    }, 250);

    return () => clearTimeout(debounceRef.current);
  }, [query]);

  if (query.trim().length < 2 || suggestions.length === 0) return null;

  return (
    <div className="absolute inset-x-0 top-full z-50 mt-1 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] shadow-[var(--shadow-hover)]">
      {loading && (
        <div className="px-4 py-3 text-xs text-[var(--color-text-muted)]">Searching...</div>
      )}
      {!loading && suggestions.length > 0 && (
        <ul className="py-2">
          {suggestions.map((product) => (
            <li key={product.id}>
              <button
                onClick={() => {
                  navigate(buildRoute(ROUTES.product, { productSlug: product.slug }));
                  onSelect();
                }}
                className="flex w-full items-center gap-3 px-4 py-2.5 text-left transition-colors hover:bg-[var(--color-bg-cream)]"
              >
                <img
                  src={product.thumbnail}
                  alt={product.name}
                  className="h-12 w-12 flex-shrink-0 rounded-[var(--radius-card)] object-cover"
                />
                <div className="flex-1 min-w-0">
                  <p className="truncate text-sm font-medium text-[var(--color-text-primary)]">
                    {product.name}
                  </p>
                  <div className="mt-0.5 flex items-center gap-2">
                    <span className="text-sm font-semibold text-[var(--color-primary)]">
                      {formatCurrency(product.price)}
                    </span>
                    <Rating value={product.rating} reviewCount={product.reviewCount} size="sm" />
                  </div>
                </div>
              </button>
            </li>
          ))}
          <li className="border-t border-[var(--color-border)]">
            <button
              onClick={() => {
                navigate(`${ROUTES.shop}?search=${encodeURIComponent(query.trim())}`);
                onSelect();
              }}
              className="w-full px-4 py-2.5 text-center text-sm font-medium text-[var(--color-primary)] transition-colors hover:bg-[var(--color-bg-cream)]"
            >
              View all results for "{query.trim()}"
            </button>
          </li>
        </ul>
      )}
    </div>
  );
}
