import { cn } from '@/utils/cn';
import type { ProductVariant } from '@/types/product';

interface VariantSelectorProps {
  variants: ProductVariant[];
  selectedVariantId: string | null;
  onSelect: (variantId: string | null) => void;
}

export function VariantSelector({ variants, selectedVariantId, onSelect }: VariantSelectorProps) {
  if (variants.length === 0) return null;

  // Group variants by type (e.g., "size", "finish", "set")
  const grouped = variants.reduce<Record<string, ProductVariant[]>>((acc, v) => {
    (acc[v.type] ??= []).push(v);
    return acc;
  }, {});

  return (
    <div className="flex flex-col gap-4">
      {Object.entries(grouped).map(([type, typeVariants]) => {
        const selected = typeVariants.find((v) => v.id === selectedVariantId);
        return (
          <div key={type}>
            <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
              {type}
              {selected && (
                <span className="ml-2 normal-case tracking-normal text-[var(--color-text-secondary)]">
                  — {selected.label}
                </span>
              )}
            </p>
            <div className="flex flex-wrap gap-2">
              {typeVariants.map((variant) => {
                const isSelected = variant.id === selectedVariantId;
                return (
                  <button
                    key={variant.id}
                    onClick={() => onSelect(isSelected ? null : variant.id)}
                    disabled={!variant.inStock}
                    className={cn(
                      'rounded-[var(--radius-btn)] border px-4 py-2.5 text-sm font-medium transition-all duration-200',
                      !variant.inStock
                        ? 'cursor-not-allowed border-[var(--color-border)] bg-[var(--color-bg-cream)]/50 text-[var(--color-text-muted)] line-through'
                        : isSelected
                          ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-bg-light)] shadow-sm'
                          : 'border-[var(--color-border)] bg-[var(--color-bg-light)] text-[var(--color-text-secondary)] hover:border-[var(--color-primary)] hover:text-[var(--color-primary)]'
                    )}
                  >
                    {variant.label}
                  </button>
                );
              })}
            </div>
          </div>
        );
      })}
    </div>
  );
}
