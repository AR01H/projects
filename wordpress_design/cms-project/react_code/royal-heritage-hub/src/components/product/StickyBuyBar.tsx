import { formatCurrency } from '@/utils/formatCurrency';
import type { Product } from '@/types/product';

interface StickyBuyBarProps {
  product: Product;
  quantity: number;
  onAddToBag: () => void;
  visible: boolean;
  effectivePrice?: number;
}

export function StickyBuyBar({ product, quantity, onAddToBag, visible, effectivePrice }: StickyBuyBarProps) {
  if (!visible) return null;

  const displayPrice = effectivePrice ?? product.price;

  return (
    <div className="glass-surface fixed inset-x-0 bottom-0 z-40 border-t border-[var(--color-border)] px-4 py-3 lg:hidden">
      <div className="flex items-center gap-3">
        <div className="flex-1 min-w-0">
          <p className="truncate text-sm font-medium text-[var(--color-text-primary)]">{product.name}</p>
          <p className="text-xs text-[var(--color-text-muted)]">
            {formatCurrency(displayPrice)} × {quantity}
          </p>
        </div>
        <button
          onClick={onAddToBag}
          disabled={product.stock === 0}
          className="flex-shrink-0 rounded-[var(--radius-btn)] bg-[var(--color-primary)] px-5 py-2.5 text-sm font-semibold text-[var(--color-bg-light)] transition-colors hover:bg-[var(--color-primary-dark)] disabled:bg-[var(--color-text-muted)]"
        >
          {product.stock === 0 ? 'Out of Stock' : 'Add to Bag'}
        </button>
      </div>
    </div>
  );
}
