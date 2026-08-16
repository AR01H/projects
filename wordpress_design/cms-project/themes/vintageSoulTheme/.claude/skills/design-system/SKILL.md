---
name: design-system
description: Use when adding CSS, choosing a color/spacing/typography value, or deciding whether something needs a new design token, in this theme.
---

# Design System (VintageSoulTheme)

Full conventions: `../../../docs/design-system.md`.

## The one rule

No raw values in component/page CSS - no hex colors, no arbitrary px, no
magic numbers. If `assets/css/variables.css` doesn't have a token for a
value yet, add the token there first (in the right category section),
then use it.

## Color - check which tier before adding a value

- A **client-branding** color -> `--color-brand-1` … `--color-brand-5`
  in `variables.css` ONLY. Never add a 6th brand variable without
  reconsidering whether it's really a new semantic role instead.
- Everything else -> a **semantic** color (`--color-primary`,
  `--color-secondary`, `--color-accent`, `--color-background`,
  `--color-surface`) or a general alias (`--color-text`, `--color-border`,
  etc.) that points AT a brand or neutral token. Components reference
  semantic/alias tokens, never `--color-brand-*` or `--color-neutral-*`
  directly.

## Before writing a new component's CSS

1. Confirm the file location: `assets/css/components/<name>.css`.
2. Base styles, then every size/variant, then every interaction state
   (`:hover`, `:focus-visible`, `:active`, `:disabled`) in the SAME file -
   never a separate hover/mobile stylesheet.
3. Responsive behavior at the bottom of the same file, using the
   documented breakpoints (`docs/design-system.md`) - mobile-first
   (`min-width` media queries adding complexity upward).
4. `:focus-visible` is mandatory for anything interactive - copy the
   pattern from `assets/css/base.css`'s global rule if the component
   doesn't need a custom focus style.
5. Wrap any animation/transition change in a
   `@media (prefers-reduced-motion: reduce)` check, or rely on the global
   token override in `variables.css` if a simple duration-zeroing is enough.

## Registering the file

Add the component's CSS to `config/assets.php` only if it's used on every
page. Otherwise enqueue it from the specific Controller/template that
needs it (see `docs/wordpress.md` "Assets").
