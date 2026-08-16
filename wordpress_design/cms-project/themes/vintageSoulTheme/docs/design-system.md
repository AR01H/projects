# Design System Conventions

## Current state

`assets/css/variables.css` defines **token categories** (color, typography,
spacing, radius, shadow, border, transitions, z-index, interaction). Real
design work is proceeding **element by element**, traditional_template as
the reference, one visual piece at a time - each step replaces only the
values it needs, keeps every token *name*, and does not touch elements not
yet reached.

Done so far:
- **Page background/shade** (`--color-brand-*`, `--texture-paper`,
  `body` in `base.css`) - colors sampled directly from the priority
  reference image (a quantized color-histogram pass over
  `some_styles/canehouse/priority/sugarcane/home_desktop.jpeg`'s actual
  pixels, not reused from traditional_template's CSS), plus a two-layer
  paper-grain texture (soft mottling + fine grain, warm-tinted).
- **Brown window/frame treatment** (`assets/css/frame.css`) - a new,
  standalone `.frame`/`.frame--ornate` primitive matching the bordered-box
  styling in the priority reference design. Not yet adopted into any
  existing component (cards, etc.) - that happens when that component's
  own step is reached.
- **Handcrafted shape treatments** (`assets/css/shape.css`) - eight
  generic, unprefixed classes, not scoped to any one component:
  `.cut-1/-2/-3` (clip-path corner cuts), `.roughness-a/-b/-c`
  (displaced-SVG-mask torn edge, increasing intensity), `.edge-soft`
  (organic asymmetric rounding) and `.edge-irregular` (angular hand-torn
  zigzag clip-path). One shared sheen gradient ties all eight together as
  one family. Apply to a button, card, label, section, or anything else;
  pick at most one per element.

Still placeholder, pending their own step: typography choices, cards,
forms, and every other component's visual treatment.

### Color has two tiers - never merge them

- **Brand palette** (`--color-brand-1` … `--color-brand-5`) - client-
  controlled. The *only* place a real hex value is allowed. Swapping a
  project's identity is editing these 5 lines.
- **Semantic colors** (`--color-primary`, `--color-secondary`,
  `--color-accent`, `--color-background`, `--color-surface`) - theme-
  controlled. Every component/page uses these (or the general aliases
  built on top: `--color-bg`, `--color-text`, `--color-border`, etc.),
  never a `--color-brand-*` variable directly and never a raw hex value.

This is what makes "swap the brand" and "restyle a component" two
independent operations - restyling `--color-accent`'s *role* (which brand
color it points at) never requires touching the brand palette, and picking
a new brand color never requires touching component CSS.

## The one rule

**No raw values in component/page CSS.** No hex colors, no arbitrary px
spacing, no magic numbers - if `assets/css/variables.css` doesn't have a
token for it yet, add the token there first, then use it. This is what
makes a full re-theme (or reusing this base for an unrelated project)
a token-file edit instead of a find-and-replace across every CSS file.

## File load order (see `config/assets.php`)

```
variables.css   - tokens, nothing else
base.css        - raw element styles (h1, a, button...), no component classes
layout.css      - container/structural primitives only
frame.css       - the brown window/frame border-box primitive (also a
                    structural primitive, kept in its own file since it's
                    a distinct visual treatment, not a layout concern)
shape.css       - generic handcrafted edge/corner classes (.cut-*,
                    .roughness-*, .edge-*) - not scoped to any component,
                    apply to a button, card, label, section, etc.
components/*.css - one file per component: base, every size/variant, every
                    interaction state (hover/focus/active/disabled),
                    responsive behavior - all in the same file. No separate
                    "hover" or "responsive" file per component.
pages/*.css     - page-specific overrides only, loaded only on that page
utilities.css   - single-property helper classes only
```

## Breakpoints

Defined once, used consistently (CSS custom properties can't be read
inside `@media`, so these are documented here as the single source of
truth rather than tokenized):

| Name | Value |
|---|---|
| sm | 480px |
| md | 768px |
| lg | 1024px |
| xl | 1280px |

Mobile-first: base styles are the smallest viewport; `@media (min-width: ...)`
adds complexity upward, never the reverse.

## JavaScript

```
core/utils.js    - pure functions, no DOM access
core/dom.js      - DOM query/manipulation helpers
core/events.js   - a small pub/sub bus for cross-component communication
core/app.js      - the boot sequence; components register() here
components/*.js  - one file per interactive behavior, registered via
                   VintageSoul.app.register('name', initFn)
pages/*.js       - page-specific behavior only
```

Not every component needs a JS file - most are pure CSS/markup. Only add
one when real interactivity (open/close, fetch, animation beyond CSS)
is genuinely required.

## Accessibility (built in, not bolted on)

- Every interactive element gets a real `:focus-visible` state (see
  `base.css`) - never `outline: none` without a replacement.
- `prefers-reduced-motion: reduce` disables/shortens transitions (see
  `variables.css`).
- Semantic HTML first; ARIA only to fill a genuine gap semantic HTML can't cover.
- Color choices, once real values are picked, must meet WCAG AA contrast -
  check this before finalizing `--color-primary` and text colors.
