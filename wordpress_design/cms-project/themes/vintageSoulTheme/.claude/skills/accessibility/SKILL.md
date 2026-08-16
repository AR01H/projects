---
name: accessibility
description: Use when building or reviewing a component/page for accessibility - focus states, keyboard navigation, semantic HTML, ARIA, contrast, reduced motion.
---

# Accessibility (VintageSoulTheme)

Built in from `base.css`/`variables.css`, not bolted on later - see
`docs/design-system.md` "Accessibility".

## Checklist for any new component

- [ ] Semantic HTML element chosen before reaching for a `<div>` + ARIA
      role (`<button>` for actions, `<nav>` for navigation, `<details>`
      for disclosure, etc.)
- [ ] Every interactive element has a visible `:focus-visible` state -
      inherits from `base.css`'s global rule unless the component needs a
      custom one (never `outline: none` without a replacement)
- [ ] Keyboard-operable: anything clickable with JS is also reachable and
      operable via Tab + Enter/Space, without a mouse
- [ ] Images have meaningful `alt` text (or `alt=""` if purely decorative)
      - use `ImageHelper` rather than hand-writing `<img>` so this isn't
        forgotten per-instance
- [ ] Heading levels are not skipped for a visual-size reason (see the
      `seo` skill - same rule, different motivation)
- [ ] Any animation/transition respects `prefers-reduced-motion` (CSS:
      the global token override in `variables.css` already zeroes
      durations; JS: check `window.matchMedia('(prefers-reduced-motion: reduce)')`
      before triggering a non-essential animation)
- [ ] Color is never the *only* way information is conveyed (e.g. an error
      state also has an icon/text, not just a red border)
- [ ] Once real brand colors replace the `--color-brand-*` placeholders,
      re-verify text/background combinations meet WCAG AA contrast

## Auditing

Use `/accessibility-check` for a repeatable pass over the checklist above.
